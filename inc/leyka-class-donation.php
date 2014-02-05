<?php
/**
 * Leyka Donation History
 **/

class Leyka_Donation_Management {
	
	private static $_instance = null;

	protected $_post_type = 'leyka_donation';

	private function __construct() {

        add_action('admin_head', array($this, 'remove_new_donation_button'));

        add_filter('post_row_actions', function($actions, $donation){
            global $current_screen;

            if($current_screen->post_type != 'leyka_donation')
                return $actions;

            $actions['edit'] = '<a href="'.get_edit_post_link($donation->ID, 1).'">'
                               .__('Details', 'leyka').'</a>';
            unset($actions['view']);
//            unset( $actions['trash'] );

            unset($actions['inline hide-if-no-js']);

            //$actions['inline hide-if-no-js'] .= __( 'Quick&nbsp;Edit' );

            return $actions;
        }, 10, 2);

        /** @todo Maybe, add donation's custom fields to quick edit box */
//        add_action('quick_edit_custom_box', array($this, 'display_custom_quickedit_donation'), 10, 2);

        add_action('leyka_donation_metaboxes', array($this, 'set_metaboxes'));	
        add_action('save_post', array($this, 'save_donation_data'));

		add_filter('manage_'.$this->_post_type.'_posts_columns', array($this, 'manage_columns_names'));
		add_action('manage_'.$this->_post_type.'_posts_custom_column', array($this, 'manage_columns_content'), 2, 2);

        /** Donation status transitions */
        add_action('new_to_submitted', array($this, 'new_donation_added'));

        // Leyka_donation status changed to "funded":
        add_action('funded_'.$this->_post_type, array($this, 'donation_funded'), 10, 2);
        
        add_action('wp_ajax_leyka_send_donor_email', array($this, 'ajax_send_donor_email'));
	}

    function remove_new_donation_button(){
        global $current_screen;

        if($current_screen->post_type == $this->_post_type)
            echo '<style>.add-new-h2{display: none;}</style>';
    }

    public function new_donation_added(WP_Post $donation) {
        
    }

    public function donation_funded($donation_id, WP_Post $donation) {

        set_transient('leyka_test_donation_funded', print_r($donation_id, true), 60*60*60);

        Leyka_Donation_Management::send_donor_thanking_email($donation);

        if(
            leyka_options()->opt('notify_donations_managers')
         && leyka_options()->opt('donations_managers_emails')
        )
            $this->send_managers_notifications($donation);
    }

    /** Ajax handler method */
    public function ajax_send_donor_email() {

        if( !wp_verify_nonce($_POST['nonce'], 'leyka_donor_email') )
            return;

        if( Leyka_Donation_Management::send_donor_thanking_email(get_post($_POST['donation_id'])) )
            die(__('Grateful email has been sent to the donor', 'leyka'));
        else
            die(__("For some reason, we can't send this email right now :( Please, try again later.", 'leyka'));
    }

    public static function send_donor_thanking_email(WP_Post $donation) {

        $donation = new Leyka_Donation($donation);

        $donor_email = $donation->donor_email;
        if( !$donor_email )
            $donor_email = leyka_pf_get_donor_email_value();

        if( !$donor_email )
            return false;
        if($donation->donor_email_date)
            return false;

        if( !function_exists('set_html_content_type') ) {
            function set_html_content_type(){ return 'text/html'; }
        }
        add_filter('wp_mail_content_type', 'set_html_content_type');

        $campaign = new Leyka_Campaign($donation->campaign_id);
        $res = wp_mail(
            $donor_email,
            leyka_options()->opt('email_thanks_title'),
            wpautop(str_replace(
                array(
                    '#SITE_NAME#',
                    '#ORG_NAME#',
                    '#DONATION_ID#',
                    '#DONOR_NAME#',
                    '#PAYMENT_METHOD_NAME#',
                    '#CAMPAIGN_NAME#',
                    '#PURPOSE#',
                    '#SUM#',
                    '#DATE#',
                ),
                array(
                    get_bloginfo('name'),
                    leyka_options()->opt('org_full_name'),
                    $donation->id,
                    $donation->donor_name,
                    $donation->payment_method_label,
                    $campaign->title,
                    $campaign->payment_title,
                    $donation->amount.' '.$donation->currency_label,
                    $donation->date,
                ),
                leyka_options()->opt('email_thanks_text')
            )),
            array('From: '.leyka_options()->opt_safe('email_from_name').' <'.leyka_options()->opt_safe('email_from').'>',)
        );

        // Reset content-type to avoid conflicts (http://core.trac.wordpress.org/ticket/23578):
        remove_filter('wp_mail_content_type', 'set_html_content_type');

        if($res) {
            update_post_meta($donation->id, '_leyka_donor_email_date', time());

            return true;
        } else
            return false;
    }

    public function send_managers_notifications(WP_Post $donation) {

        if( !leyka_options()->opt('donations_managers_emails') )
            return;
        $donation = new Leyka_Donation($donation);
        if($donation->managers_emails_date) {
//            echo '<pre>' . print_r($donation->ID, TRUE) . '</pre>';die();
            return;
        }

        if( !function_exists('set_html_content_type') ) {
            function set_html_content_type(){ return 'text/html'; }
        }
        add_filter('wp_mail_content_type', 'set_html_content_type');

        $res = true;
        foreach(explode(',', leyka_options()->opt('leyka_donations_managers_emails')) as $email) {
            $email = trim($email);

            if( !$email )
                continue;

            $campaign = new Leyka_Campaign($donation->campaign_id);
            if( !wp_mail(
                $email,
                leyka_options()->opt('email_notification_title'),
                wpautop(str_replace(
                    array(
                        '#SITE_NAME#',
                        '#ORG_NAME#',
                        '#DONATION_ID#',
                        '#DONOR_NAME#',
                        '#PAYMENT_METHOD_NAME#',
                        '#CAMPAIGN_NAME#',
                        '#PURPOSE#',
                        '#SUM#',
                        '#DATE#',
                    ),
                    array(
                        get_bloginfo('name'),
                        leyka_options()->opt('org_full_name'),
                        $donation->id,
                        $donation->donor_name,
                        $donation->payment_method_label,
                        $campaign->title,
                        $campaign->payment_title,
                        $donation->amount.' '.$donation->currency_label,
                        $donation->date,
                    ),
                    leyka_options()->opt('email_notification_text')
                )),
                array('From: '.leyka_options()->opt_safe('email_from_name').' <'.leyka_options()->opt_safe('email_from').'>',)
            ) )
                $res &= false;
        }

        if($res)
            update_post_meta($donation->id, '_leyka_managers_emails_date', time());

        // Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
        remove_filter('wp_mail_content_type', 'set_html_content_type');
    }

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if(null == self::$_instance) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/** Donation metaboxes */
    public function set_metaboxes(){
		remove_meta_box('submitdiv', $this->_post_type, 'side'); // Remove default status/publish metabox

		add_meta_box($this->_post_type.'_data', __('Donation data', 'leyka'), array($this, 'donation_data_metabox'), $this->_post_type, 'normal', 'high');
		add_meta_box($this->_post_type.'_status', __('Donation status', 'leyka'), array($this, 'donation_status_metabox'), $this->_post_type, 'side', 'high');
		add_meta_box($this->_post_type.'_emails_status', __('Emails status', 'leyka'), array($this, 'emails_status_metabox'), $this->_post_type, 'normal', 'high');
		add_meta_box($this->_post_type.'_gateway_response', __('Gateway responses text', 'leyka'), array($this, 'gateway_response_metabox'), $this->_post_type, 'normal', 'low');
	}

    public function donation_data_metabox($donation) {
        $campaign = new Leyka_Campaign(get_post_meta($donation->ID, 'leyka_campaign_id', true));

        $donation = new Leyka_Donation($donation);?>

        <div class="leyka-ddata-string"><span class="label"><?php echo __('Campaign:', 'leyka');?></span> 
            <?php if($campaign->id && $campaign->status == 'publish') {?>
            <a href="<?php echo $campaign->url;?>">
                <?php echo mb_strtolower($campaign->title);?>
            </a>
            <?php } else { echo __('the campaign has been removed or drafted', 'leyka'); }?>
        </div>
        <div class="leyka-ddata-string"><span class="label"><?php echo __('Donation purpose:', 'leyka');?></span> <?php echo $campaign->id ? $campaign->payment_title : $donation->title;?>
        </div>
        <div class="leyka-ddata-string">
            <span class="label"><?php echo __('Donor:', 'leyka');?></span> 
			<?php echo $donation->donor_name ? $donation->donor_name.' ('.$donation->donor_email.')' : '';?>
        </div>
        <div class="leyka-ddata-string">
            <span class="label"><?php echo __('Donation amount:', 'leyka');?></span> 
			<?php echo $donation->amount ? $donation->amount.' '.$donation->currency_label : '';?>
        </div>
        <div class="leyka-ddata-string">
            <span class="label"><?php echo __('Payment method:', 'leyka');?></span> 
            <?php
            $pm = leyka_get_pm_by_id($donation->payment_method);
            $gateway = leyka_get_gateway_by_id($donation->gateway_id);

            echo ($pm ? $pm->label : __('Unknown payment method', 'leyka'))
                .' ('.($gateway ? mb_strtolower($gateway->label) : __('unknown gateway', 'leyka')).')';
            ?>
        </div>
        <!--<div class="leyka-ddata-string">
            <span class="label"><?php echo __('Gateway response:', 'leyka');?></span>
			<?php /*echo ' '.
                (empty($donation_data['leyka_gateway_response'][0]) ?
                    __('No gateway response has been received', 'leyka') :
                    mb_strtolower($donation_data['leyka_gateway_response'][0]));*/?>
        </div>-->
	<?php }

    public function donation_status_metabox($donation) {
        wp_nonce_field('donation_status_metabox', '_donation_edit_nonce');?>
        <div class="leyka-status-section select">
            <label><?php echo __('Status', 'leyka');?></label>
            <select name="donation_status">
                <?php foreach(leyka_get_donation_status_list() as $status => $label) {
                    if($status == 'trash')
                        continue;?>
                <option value="<?php echo $status;?>" <?php echo $donation->post_status == $status ? 'selected' : '';?>><?php echo $label;?></option>
                <?php }?>
            </select>
		</div>

        <div class="leyka-status-section actions">
            <?php if(current_user_can('delete_post', $donation->ID)) {?>
				<div class="delete-action">
                <a class="submitdelete deletion" href="<?php echo get_delete_post_link($donation->ID); ?>"><?php echo !EMPTY_TRASH_DAYS ? __('Delete Permanently') : __('Move to Trash');?></a>
				</div>
            <?php }?>

            <?php
            if(current_user_can(get_post_type_object($this->_post_type)->cap->publish_posts)) {?>
				<div class="save-action">
			    <input name="original_funded" type="hidden" id="original_funded" value="<?php esc_attr_e(__('Update status', 'leyka'));?>" />
                <?php submit_button(__('Update status', 'leyka'), 'primary button-large', 'funded', false, array('accesskey' => 'p'));?>
				</div>
            <?php
			}
			?>
        </div>

        <div class="leyka-status-section log">
            <?php
            $status_log = get_post_meta($donation->ID, '_status_log', true);
            if($status_log) {?>
                <?php $last_status = end($status_log);
                echo str_replace(
                    array('%status', '%date'),
                    array('<i>'.$last_status['status'].'</i>', '<time>'.date('d.m.Y, H:i', $last_status['date']).'</time>'),
                    '<div class="leyka-ddata-string last-log">'.__('Last status change: to&nbsp;%status (at&nbsp;%date)', 'leyka').'</div>'
                );?>
                <div id="donation-status-log-toggle"><?php echo __('Show/hide full status history', 'leyka');?></div>
                <ul id="donation-status-log" style="display: none;">
                    <?php for($i=0; $i<count($status_log); $i++) {?>
                    <li><?php echo str_replace(
                        array('%status', '%date'),
                        array('<i>'.$status_log[$i]['status'].'</i>','<time>'.date('d.m.Y, H:i', $status_log[$i]['date']).'</time>'),
                        __('%date - %status', 'leyka')
                    );}?></li>
                </ul>
            <?php } else
                echo '<div class="leyka-ddata-string last-log">'.__('Last status change: none logged', 'leyka').'</div>';
            ?>
        </div>
	<?php }

    public function emails_status_metabox($donation) {

        $donor_thanks_date = get_post_meta($donation->ID, '_leyka_donor_email_date', true);
        $manager_notification_date = get_post_meta($donation->ID, '_leyka_managers_emails_date', true);
		
		if($donor_thanks_date){
			$txt = str_replace(
                '%s',
                '<time>'.date('d.m.Y, H:i</time>', $donor_thanks_date).'</time>',
                __('Grateful email to the donor has been sent (at %s)', 'leyka')
            );
		?>
			<div class="leyka-ddata-string donor has-thanks"><?php echo $txt;?></div>
		<?php
		}
		else {
			$send = "<div class='send-donor-thanks'>".__('(send it now)', 'leyka')."</div>";
			$txt = sprintf(__("Grateful email hasn't been sent %s", 'leyka'), $send);
		?>
			<div class="leyka-ddata-string donor no-thanks" data-donation-id="<?php echo $donation->ID;?>">
				<?php echo $txt;?>
				<?php wp_nonce_field('leyka_donor_email', '_leyka_donor_email_nonce', false, true); ?>
			</div>
		<?php
		}
				
    /*  foralien: I've refactored this above - hope that's OK 
		echo $donor_thanks_date ?
            str_replace(
                '%s',
                date('d.m.Y, H:i', $donor_thanks_date),
                __('Grateful email to the donor has been sent (at %s)', 'leyka')
            ) :
            '<div class="leyka-no-donor-thanks" data-donation-id="'.$donation->ID.'">'.
                __("Grateful email hasn't been sent <div class='send-donor-thanks'>(send it now)</div>", 'leyka').wp_nonce_field('leyka_donor_email', '_leyka_donor_email_nonce', false, true).'</div>';*/

        echo $manager_notification_date ?
            str_replace(
                '%s',
                '<time>'.date('d.m.Y, H:i', $manager_notification_date).'</time>',
                __('Donation managers notifications has been sended (at %s)', 'leyka')
            ) :
            '<div class="leyka-ddata-string manager no-thanks">'.__("Donation managers' notification emails hasn't been sent", 'leyka').'</div>';
    }

    /**
     * @param $donation WP_Post
     */
    public function gateway_response_metabox($donation) { $donation = new Leyka_Donation($donation);?>
        
        <div>
            <?php
            if( !$donation->gateway_response_formatted )
                echo __('No gateway response has been received', 'leyka');
            else {

                foreach($donation->gateway_response_formatted as $name => $value) {?>

                <div class="leyka-ddata-string"><span class="label"><?php echo $name;?></span> <?php echo mb_strtolower($value);?></div>

                <?php }
            }
            ?>
        </div>
    <?php }

	/** Donations table columns */
	function manage_columns_names($columns) {

		$unsort = $columns;
		$columns = array();

		if(isset($unsort['cb'])){
			$columns['cb'] = $unsort['cb'];
			unset($unsort['cb']);
		}

		$columns['ID'] = 'ID';

		if(isset($unsort['title'])){
			$columns['title'] = __('Campaign', 'leyka');
			unset($unsort['title']);
//			unset($unsort['campaign']);
		}

        unset($unsort['date']);

//		$columns['campaign'] = __('Campaign', 'leyka');
		$columns['donor'] = __('Donor', 'leyka');
		$columns['amount'] = __('Amount', 'leyka');
		$columns['method'] = __('Method', 'leyka');	
		$columns['status'] = __('Status', 'leyka');
		$columns['emails'] = __('Grateful email', 'leyka');

		if($unsort)
			$columns = array_merge($columns, $unsort);

		return $columns;
	}

	function manage_columns_content($column_name, $donation_id) {
        
//		$donation = new Leyka_Donation(get_post($donation_id));
		$donation = get_post($donation_id);
        switch($column_name) {
            case 'ID': echo $donation_id; break;
            case 'amount':
                $currency = leyka_options()->opt(
                    'leyka_currency_'.get_post_meta($donation_id, 'leyka_donation_currency', true).'_label'
                );
                echo get_post_meta($donation_id, 'leyka_donation_amount', true).' '.$currency;
                break;
            case 'donor': echo get_post_meta($donation_id, 'leyka_donor_name', true); break;
            case 'method':
                $pm = leyka_get_pm_by_id(get_post_meta($donation_id, 'leyka_payment_method', true));
                $gateway = leyka_get_gateway_by_id(get_post_meta($donation_id, 'leyka_gateway', true));

                echo ($pm ? $pm->label : __('Unknown payment method', 'leyka'))
                    .' ('.($gateway ? mb_strtolower($gateway->label) : __('unknown gateway', 'leyka')).')';
                break;
//            case 'title':
//                $campaign = get_post(get_post_meta($donation_id, 'leyka_campaign_id', true));
//                if( !$campaign )
//                    echo __('Campaign was deleted', 'leyka');
//                break;
            case 'status':
                $status_log = get_post_meta($donation_id, '_status_log', true);
                if( !$status_log || !is_array($status_log) ) {
                    $status_log = array(array(
                        'date' => strtotime($donation->post_modified),
                        'status' => $donation->post_status
                    ));
                    update_post_meta($donation_id, '_status_log', $status_log);
                }
                $status_log = end($status_log);

                echo '<i>'.$this->get_status_labels($donation->post_status).'</i>'
                    .' ('.date('d.m.Y, H:i:s', $status_log['date']).')';
                break;
            case 'emails':
                $donor_thanks_date = get_post_meta($donation_id, '_leyka_donor_email_date', true);

                /*echo $donor_thanks_date ?
                    str_replace(
                        '%s',
                        date('d.m.Y, H:i', $donor_thanks_date),
                        __('Sent at %s', 'leyka')
                    ) :
                    '<div class="leyka-no-donor-thanks" data-donation-id="'.$donation_id.'">'.
                        __("Not sent <div class='send-donor-thanks'>(send it now)</div>", 'leyka').
                        wp_nonce_field('leyka_donor_email', '_leyka_donor_email_nonce', false, true).'</div>';*/
				
				if($donor_thanks_date){
					echo str_replace(
						'%s',
						'<time>'.date('d.m.Y, H:i</time>', $donor_thanks_date).'</time>',
						__('Sent at %s', 'leyka')
					);				
				}
				else {
					$send = "<div class='send-donor-thanks'>".__('(send it now)', 'leyka')."</div>";
					$txt = sprintf(__("Not sent %s", 'leyka'), $send);
				?>
					<div class="leyka-no-donor-thanks" data-donation-id="<?php echo $donation_id;?>">
						<?php echo $txt;?>
						<?php wp_nonce_field('leyka_donor_email', '_leyka_donor_email_nonce', false, true); ?>
					</div>
				<?php
				}
				
                break;
//            case '':
//                break;
            default:
        }
	}

    /** Save donation data metabox */
    public function save_donation_data($donation_id) {

        // Maybe, donation inserting trough API:
        if(empty($_POST['post_type']) || $_POST['post_type'] != 'leyka_donation')
            return false;

        // Verify that the nonce is valid.
        if(empty($_POST['_donation_edit_nonce'])
        || !wp_verify_nonce($_POST['_donation_edit_nonce'], 'donation_status_metabox')) {
            return $donation_id;
        }

        if(defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE)
            return $donation_id;

//        die('<pre>' . print_r('HERE', 1) . '</pre>');

        /** @todo Сделать после добавления capabilities Лейки */
//        if( !current_user_can('edit_donation', $donation_id) )
//            return $donation_id;

        // Unhook & hook again this function later so it doesn't loop infinitely:
        remove_action('save_post', array($this, 'save_donation_data'));

        wp_update_post(array('ID' => $donation_id, 'post_status' => $_POST['donation_status']));

        add_action('save_post', array($this, 'save_donation_data'));

        $status_log = get_post_meta($donation_id, '_status_log', true);
        $status_log[] = array('date' => time(), 'status' => $_POST['donation_status']);
        update_post_meta($donation_id, '_status_log', $status_log);
    }

	/** Helpers **/
	static function get_status_labels($status = false) {
        $labels = leyka()->get_donation_statuses();

        if(empty($status))
		    return $labels;
        elseif($status == 'publish')
            return $labels['funded'];
        else
		    return !empty($labels[$status]) ? $labels[$status] : false;
	}

    /** @todo Maybe, method doesn't needed. Check after release */
	static function get_default_meta() {

		return array(
			'leyka_donation_amount' => 0,
			'leyka_donation_currency' => '',
			'leyka_donor_name' => '',
			'leyka_donor_email' => '',
			'leyka_payment_method' => '',
			'leyka_gateway' => '',
			'leyka_campaign_id' => 0,
			'leyka_donation_status' => '',			
			'leyka_gateway_response' => '',
			'_leyka_donor_email_date' => 0,
			'_leyka_manager_emails_date' => 0,
            '_status_log' => array(),
		);
	}
} // class end

function leyka_donation_management() {
    return Leyka_Donation_Management::get_instance();
}


class Leyka_Donation {

	private $_id;

    /** @var WP_Post */
	private $_post_object;

    private $_donation_meta = array();
	
	function __construct($donation) {

        if(is_object($donation)) {
            $this->_id = $donation->ID;
            $this->_post_object = $donation;
        } elseif((int)$donation > 0) {
            $this->_id = (int)$donation;
            $this->_post_object = get_post($this->_id);
        }

        if( !$this->_donation_meta ) {
            $meta = get_post_meta($this->_id, '', true);
            $this->_donation_meta = array(
                'payment_method' => $meta['leyka_payment_method'][0],
                'gateway' => $meta['leyka_gateway'][0],
                'currency' => $meta['leyka_donation_currency'][0],
                'amount' => (float)$meta['leyka_donation_amount'][0],
                'donor_name' => $meta['leyka_donor_name'][0],
                'donor_email' => $meta['leyka_donor_email'][0],
                'donor_email_date' => $meta['_leyka_donor_email_date'][0],
                'managers_emails_date' => $meta['_leyka_managers_emails_date'][0],
                'campaign_id' => (int)$meta['leyka_campaign_id'][0],
                'status_log' => $meta['_status_log'][0],
                'gateway_response' => empty($meta['leyka_gateway_response']) ?
                    '' : $meta['leyka_gateway_response'][0],
            );
        }
	}

    public function __get($field) {

        switch($field) {
            case 'id':
            case 'ID': return $this->_id;
            case 'title':
            case 'name': return $this->_post_object->post_title;
            case 'status': return $this->_post_object->post_status;
            case 'status_label':
                $stati = leyka_get_donation_status_list();
                return $stati[$this->_post_object->post_status];
            case 'date': return date(get_option('date_format'), strtotime($this->_post_object->post_date));
            case 'payment_method':
            case 'payment_method_id':
            case 'pm':
            case 'pm_id':
                return $this->_donation_meta['payment_method'];
            case 'gateway':
            case 'gateway_id':
                return $this->_donation_meta['gateway'];
            case 'payment_method_label':
                $pm = leyka_get_pm_by_id($this->_donation_meta['payment_method']);
                return ($pm ? $pm->label : __('Unknown payment method', 'leyka'));
            case 'currency':
                return $this->_donation_meta['currency'];
            case 'currency_label':
                return leyka_options()->opt('leyka_currency_'.$this->_donation_meta['currency'].'_label');
            case 'sum':
            case 'amount':
                return $this->_donation_meta['amount'];
            case 'donor_name':
                return $this->_donation_meta['donor_name'];
            case 'donor_email':
                return $this->_donation_meta['donor_email'];
            case 'donor_email_date':
                return $this->_donation_meta['donor_email_date'];
            case 'managers_emails_date':
                return $this->_donation_meta['managers_emails_date'];
            case 'campaign_id':
                return $this->_donation_meta['campaign_id'];
            case 'status_log':
                return $this->_donation_meta['status_log'];
            case 'gateway_response':
                return $this->_donation_meta['gateway_response'];
            case 'gateway_response_formatted':
                return $this->gateway ? leyka_get_gateway_by_id($this->gateway)->get_gateway_response_formatted($this) : array();
//            case '': return ''; break;
            default:
                return null;
        }
    }

    public function add_gateway_response($resp_text) {

        $this->_donation_meta['gateway_response'] = $resp_text;

        update_post_meta($this->_id, 'leyka_gateway_response', $this->_donation_meta['gateway_response']);
    }
    
    public function set_status($status) {

        if( !array_key_exists($status, leyka_get_donation_status_list()) )
            return false;

        $res = wp_update_post(array('ID' => $this->_id, 'post_status' => $status));

        $status_log = get_post_meta($this->_id, '_status_log', true);
        $status_log[] = array('date' => time(), 'status' => $status);
        update_post_meta($this->_id, '_status_log', $status_log);
        
        return $res;
    }

	public function save() {

		/*$meta = $this->get_default_meta();
		if(isset($_REQUEST['campaign_target']) && !empty($_REQUEST['campaign_target']))
			$meta['campaign_target'] = intval($_REQUEST['campaign_target']);
			
		foreach($meta as $key => $value){
			update_post_meta($this->ID, $key, $value);
		}*/
	}

//	function get_meta($key) {
//
//		return get_post_meta($this->_id, $key, true);
//	}
//
	/** Helpers */
//	function get_status_label($status){
//
//		$labels = leyka()->get_donation_statuses();
//
//		return !empty($labels[$status]) ? $labels[$status] : false;
//	}
}