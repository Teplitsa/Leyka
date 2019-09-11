<?php if( !defined('WPINC') ) { die; }

require_once LEYKA_PLUGIN_DIR.'gateways/rbk/includes/Leyka_Rbk_Gateway_Web_Hook_Verification.php';
require_once LEYKA_PLUGIN_DIR.'gateways/rbk/includes/Leyka_Rbk_Gateway_Web_Hook.php';
require_once LEYKA_PLUGIN_DIR.'gateways/rbk/includes/Leyka_Rbk_Gateway_Helper.php';

/**
 * Leyka_Rbk_Gateway class
 */
class Leyka_Rbk_Gateway extends Leyka_Gateway {

    protected static $_instance;

    protected static $_rbk_api_path = '/v2/processing/invoices';
    protected $_rbk_response;
    protected $_rbk_log = array();

    protected function _set_attributes() {

        $this->_id = 'rbk';
        $this->_title = __('RBK Money', 'leyka');

        $this->_description = apply_filters(
            'leyka_gateway_description',
            __('RBK Money allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="https://rbkmoney.ru/">RBK Money</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
            $this->_id
        );

        $this->_docs_link = '//leyka.te-st.ru/docs/podklyuchenie-rbk/';
        $this->_registration_link = '//auth.rbk.money/auth/realms/external/login-actions/registration?client_id=koffing';

        $this->_min_commission = 2.9;
        $this->_receiver_types = array('legal');

    }

    protected function _set_options_defaults() {

        if($this->_options) {
            return;
        }

        $this->_options = array(
            'rbk_shop_id' => array(
                'type' => 'text',
                'title' => __('RBK Money shopID', 'leyka'),
                'description' => __('Please, enter your shopID value here. It can be found in your contract with RBK Money or in your control panel there.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '1234'),
            ),
            'rbk_api_key' => array(
                'type' => 'text',
                'title' => __('RBK Money apiKey', 'leyka'),
                'comment' => __('Please, enter your apiKey value here. It can be found in your RBK Money control panel.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'RU123456789'),
            ),
            'rbk_api_web_hook_key' => array(
                'type' => 'text',
                'title' => __('RBK Money webhook public key', 'leyka'),
                'comment' => __('Please, enter your webhook public key value here.', 'leyka'),
                'required' => true,
                'placeholder' => __('-----BEGIN PUBLIC KEY----- ...', 'leyka'),
            )
        );

    }

    protected function _initialize_pm_list() {
        if(empty($this->_payment_methods['bankcard'])) {
            $this->_payment_methods['bankcard'] = Leyka_Rbk_Card::get_instance();
        }
    }

    public function enqueue_gateway_scripts() {

        if(Leyka_Rbk_Card::get_instance()->active) {

            wp_enqueue_script(
                'leyka-rbk-checkout',
                'https://checkout.rbk.money/checkout.js',
                array(),
                false,
                true
            );

            wp_enqueue_script(
                'leyka-revo-rbk',
                LEYKA_PLUGIN_BASE_URL . 'gateways/' . Leyka_Rbk_Gateway::get_instance()->id.'/js/leyka.rbk.js',
                array('jquery', 'leyka-revo-public', 'leyka-rbk-checkout'),
                LEYKA_VERSION,
                true
            );

        }

    }

    public function process_form($gateway_id, $pm_id, $donation_id, $form_data) {

        $donation = new Leyka_Donation($donation_id);
        $campaign = new Leyka_Campaign($form_data['leyka_campaign_id']);

        $url = Leyka_Rbk_Gateway_Web_Hook::$rbk_host.self::$_rbk_api_path;

        $dueDate = date(
            'Y-m-d\TH:i:s\Z',
            strtotime('+2 minute', current_time('timestamp', 1))
        );

        $data = array(
            'shopID' => leyka_options()->opt('leyka_rbk_shop_id'),
            'amount' => (int)$donation->__get('amount') * 100,
            'metadata' => array(
                'order_id' => __("Donation id:", 'leyka') . " {$donation_id}"
            ),
            'dueDate' => $dueDate,
            'currency' => 'RUB',
            'product' => $form_data['leyka_ga_campaign_title'],
            'description' => $campaign->short_description,
        );

        $args = array(
            'timeout' => 30,
            'redirection' => 10,
            'blocking' => true,
            'httpversion' => '1.1',
            'headers' => array(
                'X-Request-ID' => uniqid(),
                'Authorization' => 'Bearer '.leyka_options()->opt('leyka_rbk_api_key'),
                'Content-type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json'
            ),
            'body' => json_encode($data)
        );

        $this->_rbk_log["RBK_Request"] = array(
            'url' => $url,
            'params' => $args
        );

        $response = wp_remote_post($url, $args);
        $this->_rbk_response = json_decode(wp_remote_retrieve_body($response));

        return $this->_rbk_response;

    }

    public function submission_redirect_url($current_url, $pm_id) {
        return $current_url;
    }

    public function submission_form_data($form_data_vars, $pm_id, $donation_id) {

        $donation = new Leyka_Donation($donation_id);
        $campaign = new Leyka_Campaign($donation->campaign_id);

        $name = esc_attr(get_bloginfo('name').': '.__('donation', 'leyka'));

        $invoice_template_id = $this->_rbk_response->invoice->id;
        $invoice_template_access_token = $this->_rbk_response->invoiceAccessToken->payload;
        update_post_meta($donation_id, '_leyka_donation_id_on_gateway_response', $invoice_template_id);

        $this->_rbk_log['RBK_Form'] = $_POST;
        $this->_rbk_log['RBK_Response'] = $this->_rbk_response;
        $donation->add_gateway_response($this->_rbk_log);

        $description = $campaign->payment_title ? esc_attr($campaign->payment_title) : $name;
        $success_page_url = get_permalink(leyka_options()->opt('success_page'));

        if('revo' !== $campaign->campaign_template) {?>
            <script src="https://checkout.rbk.money/checkout.js"></script>
            <script>
                window.addEventListener('load', function () {
                    checkout = RbkmoneyCheckout.configure({
                        invoiceID: '<?php echo $invoice_template_id;?>',
                        invoiceAccessToken: '<?php echo $invoice_template_access_token;?>',
                        name: '<?php  echo $name;?>',
                        description: '<?php echo $description?>',
                        email: '<?php echo $donation->donor_email;?>',
                        initialPaymentMethod: 'bankCard',
                        paymentFlowHold: true,
                        holdExpiration: 'capture',
                        opened: function () {
                            // console.log('Checkout opened');
                        },
                        closed: function () {
                            // console.log('Checkout closed');
                            return window.history.back();
                        },
                        finished: function () {
                            // console.log('Payment successful finished');
                            return window.location.href = '<?php echo $success_page_url;?>';
                        }
                    });
                    checkout.open();
                    window.addEventListener('popstate', function () {
                        checkout.close();
                    });
                }, false);
            </script><?php

            die;

        } else {

            $script = <<<JS
            checkout = RbkmoneyCheckout.configure( {
			invoiceID : '{$invoice_template_id}',
			invoiceAccessToken : '{$invoice_template_access_token}',
			name : '{$name}',
			description : '{$description}',
			email : '{$donation->donor_email}',
			initialPaymentMethod : 'bankCard',
			paymentFlowHold : true,
			holdExpiration : 'capture',
    		opened : function () {
	    		jQuery('.leyka-pf__redirect').removeClass('leyka-pf__redirect--open');
			},
			closed : function () {
			    jQuery('.leyka-pf__redirect').removeClass('leyka-pf__redirect--open');
			},
			finished : function () {
			return window.location.href = '{$success_page_url}';
			}
			} );
			checkout.open();
			    window.addEventListener( 'popstate', function () {
    			checkout.close();
			} );
JS;
            die( wp_json_encode(array('status' => 0, 'script' => trim($script))) );

        }

    }

    public function _handle_service_calls($call_type = '') {
        if('check_order' === $call_type) {
            // Callback URLs are: some-website.org/leyka/service/rbk/check_order/
            //require options:

            //InvoiceCreated
            //InvoicePaid
            //PaymentRefunded
            //PaymentProcessed
            do_action('leyka_rbk_gateway_web_hook');
        }
    }

    protected function _get_value_if_any($arr, $key, $val = false) {
        return empty($arr[$key]) ? '' : ($val ? $val : $arr[$key]);
    }

    public function get_gateway_response_formatted(Leyka_Donation $donation) {

        if( !$donation->gateway_response ) {
            return array();
        }

        $vars = maybe_unserialize($donation->gateway_response);
        if( !$vars || !is_array($vars) ) {
            return array();
        }

        return array(
            __('Operation date:', 'leyka') => date('d.m.Y, H:i:s', strtotime($vars['RBK_Response']->invoice->createdAt)),
            __('Shop Account:', 'leyka') => $vars['RBK_Response']->invoice->shopID,
            __('Full donation amount:', 'leyka') => $vars['RBK_Response']->invoice->amount / 100,
            __('Donation currency:', 'leyka') => $vars['RBK_Response']->invoice->currency,
            __('Payment method selected:', 'leyka') => $vars['RBK_Form']['leyka_payment_method'],
            __('Operation status:', 'leyka') => $vars['RBK_Response']->invoice->status,
            __('Donor name:', 'leyka') => $vars['RBK_Form']['leyka_donor_name'],
            __('Invoice ID:', 'leyka') => $vars['RBK_Response']->invoice->id,
        );

    }

}


class Leyka_Rbk_Card extends Leyka_Payment_Method {

    protected static $_instance;

    public function _set_attributes() {

        $this->_id = 'bankcard';
        $this->_gateway_id = 'rbk';
        $this->_category = 'bank_cards';

        $this->_description = apply_filters(
            'leyka_pm_description',
            __('RBK Money allows a simple and safe way to pay for goods and services with bank cards and other means through internet. You will have to fill a payment form, and then you will be redirected to the <a href="https://rbkmoney.ru/">RBK Money</a> secure payment page to enter your bank card data and to confirm your payment.', 'leyka'),
            $this->_id,
            $this->_gateway_id,
            $this->_category
        );

        $this->_label_backend = __('Bank card (RBK Money)', 'leyka');
        $this->_label = __('Bank card', 'leyka');

        $this->_icons = apply_filters('leyka_icons_'.$this->_gateway_id.'_' . $this->_id, array(
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-visa.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mastercard.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-maestro.svg',
            LEYKA_PLUGIN_BASE_URL.'img/pm-icons/card-mir.svg',
        ));

        $this->_supported_currencies[] = 'rur';
        $this->_default_currency = 'rur';

    }

}

function leyka_add_gateway_rbk() {
    leyka_add_gateway(Leyka_Rbk_Gateway::get_instance());
}

add_action('leyka_init_actions', 'leyka_add_gateway_rbk');