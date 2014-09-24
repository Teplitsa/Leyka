<?php if( !defined('WPINC') ) die;

/**
 * Leyka Donation History
 **/

class Leyka_Donation_Management {
	
	private static $_instance = null;

	public static $post_type = 'leyka_donation';

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

		add_filter('manage_'.self::$post_type.'_posts_columns', array($this, 'manage_columns_names'));
		add_action('manage_'.self::$post_type.'_posts_custom_column', array($this, 'manage_columns_content'), 2, 2);

        /** Donation status transitions */
        add_action('new_to_submitted', array($this, 'new_donation_added'));

        // Leyka_donation status changed to "funded":
        add_action('funded_'.self::$post_type, array($this, 'donation_funded'), 10, 2);
        
        add_action('wp_ajax_leyka_send_donor_email', array($this, 'ajax_send_donor_email'));
	}

    function remove_new_donation_button(){
        global $current_screen;

        if($current_screen->post_type == self::$post_type)
            echo '<style>.add-new-h2{display: none;}</style>';
    }

    public function new_donation_added(WP_Post $donation) {

        if($donation->post_type != 'leyka_donation')
            return;

        if($donation->post_status == 'funded') // Donation was added with "funded" status, do the drill
            $this->donation_funded($donation->ID, $donation);
    }

    public function donation_funded($donation_id, WP_Post $donation) {

//        set_transient('leyka_test_donation_funded', print_r($donation_id, true), 60*60*60);

        Leyka_Donation_Management::send_donor_thanking_email($donation);

        if(leyka_options()->opt('donations_managers_emails')) {
            $donation_id = new Leyka_Donation($donation_id);
            if(
                ($donation_id->payment_type == 'single' && leyka_options()->opt('notify_donations_managers')) ||
                ($donation_id->payment_type == 'rebill' && leyka_options()->opt('notify_managers_on_recurrents'))
            )
                Leyka_Donation_Management::send_managers_notifications($donation);

        }
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

        $email_text = $donation->payment_type == 'single' ?
            leyka_options()->opt('email_thanks_text') : leyka_options()->opt('email_recurrents_thanks_text');
        $email_title = $donation->payment_type == 'single' ?
            leyka_options()->opt('email_thanks_title') : leyka_options()->opt('email_recurrents_thanks_title');

        $res = wp_mail(
            $donor_email,
            apply_filters('leyka_email_thanks_title', $email_title, $donation, $campaign),
            wpautop(str_replace(
                array(
                    '#SITE_NAME#',
                    '#SITE_EMAIL#',
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
                    get_bloginfo('admin_email'),
                    leyka_options()->opt('org_full_name'),
                    $donation->id,
                    $donation->donor_name,
                    $donation->payment_method_label,
                    $campaign->title,
                    $campaign->payment_title,
                    $donation->amount.' '.$donation->currency_label,
                    $donation->date,
                ),
                apply_filters(
                    'leyka_email_thanks_text',
                    $email_text,
                    $donation, $campaign
                )
            )),
            array('From: '.apply_filters(
                'leyka_email_from_name',
                leyka_options()->opt_safe('email_from_name'),
                $donation,
                $campaign
            ).' <'.leyka_options()->opt_safe('email_from').'>',)
        );

        // Reset content-type to avoid conflicts (http://core.trac.wordpress.org/ticket/23578):
        remove_filter('wp_mail_content_type', 'set_html_content_type');

        if($res) {
            update_post_meta($donation->id, '_leyka_donor_email_date', time());

            return true;
        } else
            return false;
    }

    public static function send_managers_notifications(WP_Post $donation) {

        if( !leyka_options()->opt('donations_managers_emails') )
            return;
        $donation_obj = $donation;
        $donation = new Leyka_Donation($donation);
        if($donation->managers_emails_date)
            return;

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
                apply_filters(
                    'leyka_email_notification_title',
                    leyka_options()->opt('email_notification_title'),
                    $donation, $campaign
                ),
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
                    apply_filters(
                        'leyka_email_notification_text',
                        leyka_options()->opt('email_notification_text'),
                        $donation, $campaign
                    )
                )),
                array('From: '.apply_filters(
                    'leyka_email_from_name',
                    leyka_options()->opt_safe('email_from_name'),
                    $donation,
                    $campaign
                ).' <'.leyka_options()->opt_safe('email_from').'>',)
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
		remove_meta_box('submitdiv', self::$post_type, 'side'); // Remove default status/publish metabox

		add_meta_box(self::$post_type.'_data', __('Donation data', 'leyka'), array($this, 'donation_data_metabox'), self::$post_type, 'normal', 'high');
		add_meta_box(self::$post_type.'_status', __('Donation status', 'leyka'), array($this, 'donation_status_metabox'), self::$post_type, 'side', 'high');
		add_meta_box(self::$post_type.'_emails_status', __('Emails status', 'leyka'), array($this, 'emails_status_metabox'), self::$post_type, 'normal', 'high');
		add_meta_box(self::$post_type.'_gateway_response', __('Gateway responses text', 'leyka'), array($this, 'gateway_response_metabox'), self::$post_type, 'normal', 'low');
//        add_meta_box(self::$post_type.'_recurrent_cancel', __('Cancel recurrent donations', 'leyka'), array($this, 'recurrent_cancel_metabox'), self::$post_type, 'normal', 'low');
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
        <div class="leyka-ddata-string">
            <span class="label"><?php echo __('Payment type:', 'leyka');?></span>
            <?php echo __($donation->payment_type, 'leyka'); // "single" or "rebill" ?>
        </div>

        <?php if($donation->chronopay_customer_id) {?>
        <div class="leyka-ddata-string">
            <span class="label"><?php echo __('Chronopay customer ID:', 'leyka');?></span>
            <?php echo $donation->chronopay_customer_id;?>
        </div>
        <?php }?>
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
            if(current_user_can(get_post_type_object(self::$post_type)->cap->publish_posts)) {?>
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
                    array('<i>'.$this->get_status_labels($last_status['status']).'</i>', '<time>'.date('d.m.Y, H:i', $last_status['date']).'</time>'),
                    '<div class="leyka-ddata-string last-log">'.__('Last status change: to&nbsp;%status (at&nbsp;%date)', 'leyka').'</div>'
                );?>
                <div id="donation-status-log-toggle"><?php echo __('Show/hide full status history', 'leyka');?></div>
                <ul id="donation-status-log" style="display: none;">
                    <?php for($i=0; $i<count($status_log); $i++) {?>
                    <li><?php echo str_replace(
                        array('%status', '%date'),
                        array('<i>'.$this->get_status_labels($status_log[$i]['status']).'</i>','<time>'.date('d.m.Y, H:i', $status_log[$i]['date']).'</time>'),
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
		
		if($donor_thanks_date) {
			$txt = str_replace(
                '%s',
                '<time>'.date('d.m.Y, H:i</time>', $donor_thanks_date).'</time>',
                __('Grateful email to the donor has been sent (at %s)', 'leyka')
            );?>

			<div class="leyka-ddata-string donor has-thanks"><?php echo $txt;?></div>

		<?php } else {

			$send = "<div class='send-donor-thanks'>".__('(send it now)', 'leyka')."</div>";
			$txt = sprintf(__("Grateful email hasn't been sent %s", 'leyka'), $send);?>

			<div class="leyka-ddata-string donor no-thanks" data-donation-id="<?php echo $donation->ID;?>">
				<?php echo $txt;?>
				<?php wp_nonce_field('leyka_donor_email', '_leyka_donor_email_nonce', false, true); ?>
			</div>
		<?php }

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

                <div class="leyka-ddata-string">
                    <span class="label"><?php echo $name;?></span> <?php echo mb_strtolower($value);?>
                </div>

            <?php }
            } ?>
        </div>
    <?php }

    public function recurrent_cancel_metabox($donation) { $donation = new Leyka_Donation($donation);

        if($donation->payment_type != 'rebill') {?>
            <div id="hide-recurrent-metabox"></div>
        <?php return; } else {
            $init_recurrent_donation = Leyka_Donation::get_init_recurrent_payment($donation->chronopay_customer_id);
            if($init_recurrent_donation->recurrents_cancelled) {?>

            <div class="">
                <?php print_r(
                    __('Recurrent donations subscription was cancelled at %s', 'leyka'),
                    date('d.m.Y, H:i:s', $init_recurrent_donation->recurrents_cancel_date)
                );?>
            </div>

            <?php }
        }?>

        <div class="recurrent-cancel" data-donation-id="<?php echo $donation->id;?>" data-nonce="<?php echo wp_create_nonce('leyka_recurrent_cancel');?>" onclick="return confirm('<?php _e("You are about to cancel all future donations on this recurrent subscribe for this donor!\\n\\nDo you really want to do it?", "leyka");?>');"><?php _e('Cancel recurrent donations of this donor', 'leyka');?></div>

        <div id="ajax-processing" style="display: none;">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL.'/img/ajax-loader-h.gif';?>" /> <?php _e('Recurrent cancelling in progress...', 'leyka');?>
        </div>
        <div id="ajax-response" style="display: none;"></div>
        <div id="recurrent-cancel-retry" style="display: none;"><?php _e('Try again', 'leyka');?></div>
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

		if(isset($unsort['title'])) {
			$columns['title'] = __('Campaign', 'leyka');
			unset($unsort['title']);
		}

        unset($unsort['date']);

//		$columns['campaign'] = __('Campaign', 'leyka');
		$columns['donor'] = __('Donor', 'leyka');
		$columns['amount'] = __('Amount', 'leyka');
		$columns['method'] = __('Method', 'leyka');	
		$columns['status'] = __('Status', 'leyka');
		$columns['emails'] = __('Letter', 'leyka');
		$columns['payment_type'] = __('Type of payment', 'leyka');

		if($unsort)
			$columns = array_merge($columns, $unsort);

		return $columns;
	}

	function manage_columns_content($column_name, $donation_id) {

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
				if($donor_thanks_date){
					echo str_replace(
						'%s',
						'<time>'.date('d.m.Y, H:i</time>', $donor_thanks_date).'</time>',
						__('Sent at %s', 'leyka')
					);				
				} else {
					$send = "<div class='send-donor-thanks'>".__('(send it now)', 'leyka')."</div>";
					$txt = sprintf(__("Not sent %s", 'leyka'), $send);?>

					<div class="leyka-no-donor-thanks" data-donation-id="<?php echo $donation_id;?>">
						<?php echo $txt;?>
						<?php wp_nonce_field('leyka_donor_email', '_leyka_donor_email_nonce', false, true); ?>
					</div>
				<?php }
				
                break;
            case 'payment_type':
                echo get_post_meta($donation_id, 'leyka_payment_type', true) == 'rebill' ?
                    __('Recurrent (rebill)', 'leyka') : __('Single', 'leyka');
                break;
//            case '':
//                break;
            default:
        }
	}

    /** Save donation data metabox */
    public function save_donation_data($donation_id) {

        // Maybe, donation is inserted trough API:
        if(empty($_POST['post_type']) || $_POST['post_type'] != 'leyka_donation')
            return false;

        // Verify that the nonce is valid.
        if(empty($_POST['_donation_edit_nonce'])
        || !wp_verify_nonce($_POST['_donation_edit_nonce'], 'donation_status_metabox')) {
            return $donation_id;
        }

        if(defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE)
            return $donation_id;

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
            'leyka_payment_type' => 'single',
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

    public static function add(array $params = array()) {

        $status = empty($params['status']) ? 'submitted' : $params['status'];
        $id = wp_insert_post(array(
            'post_type' => 'leyka_donation',
            'post_status' => $status == 'submitted' ? $status : 'submitted',
            'post_title' => empty($params['purpose_text']) ?
                leyka_options()->opt('donation_purpose_text') : $params['purpose_text'],
        ));

        if(is_wp_error($id))
            return $id;

        update_post_meta(
            $id, 'leyka_donation_amount',
            empty($params['amount']) ? leyka_pf_get_amount_value() : round((float)$params['amount'], 2)
        );
        update_post_meta(
            $id, 'leyka_donation_currency',
            empty($params['currency']) ? leyka_pf_get_currency_value() : $params['currency']
        );
        update_post_meta(
            $id, 'leyka_donor_name',
            empty($params['donor_name']) ? leyka_pf_get_donor_name_value() : $params['donor_name']
        );
        update_post_meta(
            $id, 'leyka_donor_email',
            empty($params['donor_email']) ? leyka_pf_get_donor_email_value() : $params['donor_email']
        );

        $pm_data = leyka_pf_get_payment_method_value();
        update_post_meta(
            $id, 'leyka_payment_method',
            empty($params['payment_method_id']) ? $pm_data['payment_method_id'] : $params['payment_method_id']
        );
        update_post_meta(
            $id, 'leyka_gateway',
            empty($params['gateway_id']) ? $pm_data['gateway_id'] : $params['gateway_id']
        );
        update_post_meta(
            $id, 'leyka_campaign_id',
            empty($params['campaign_id']) ? leyka_pf_get_campaign_id_value() : $params['campaign_id']
        );

        if( !get_post_meta($id, '_leyka_donor_email_date', true) )
            update_post_meta($id, '_leyka_donor_email_date', 0);
        if( !get_post_meta($id, '_leyka_managers_emails_date', true) )
            update_post_meta($id, '_leyka_managers_emails_date', 0);

        update_post_meta(
            $id,
            '_status_log',
            array(array('date' => time(), 'status' => $status))
        );

        $params['payment_type'] = empty($params['payment_type']) ? 'single' : $params['payment_type'];
        update_post_meta($id, 'leyka_payment_type', $params['payment_type'] == 'rebill' ? 'rebill' : 'single');

        if( !empty($params['chronopay_customer_id']) )
            update_post_meta($id, '_chronopay_customer_id', $params['chronopay_customer_id']);

        if( isset($params['recurrents_cancelled']) )
            update_post_meta($id, 'leyka_recurrents_cancelled', $params['recurrents_cancelled']);

        if( isset($params['recurrents_cancel_date']) )
            update_post_meta($id, 'leyka_recurrents_cancel_date', $params['recurrents_cancel_date']);
        elseif(isset($params['recurrents_cancelled']) && $params['recurrents_cancelled'])
            update_post_meta($id, 'leyka_recurrents_cancel_date', time());
        else
            update_post_meta($id, 'leyka_recurrents_cancel_date', 0);

        if($status != 'submitted')
            wp_update_post(array('ID' => $id, 'post_status' => $status));

//        if($status == 'funded') {
//            $donation = get_post($id);
//            leyka_donation_management()->donation_funded($donation->ID, $donation);
//        }

        return $id;
    }

    public static function get_init_recurrent_payment($donor_id) {

        if( !$donor_id )
            return false;

        $query = new WP_Query(array( // Get init recurrent payment with customer_id given
            'posts_per_page' => 1,
            'post_type' => 'leyka_donation',
            'post_status' => 'funded', // 'any'?
            'meta_query' => array(
                'RELATION' => 'AND',
                array(
                    'key'     => '_chronopay_customer_id',
                    'value'   => $donor_id,
                    'compare' => '=',
                ),
                array(
                    'key'     => 'leyka_payment_type',
                    'value'   => 'rebill',
                    'compare' => '=',
                ),
            ),
            'orderby' => 'date',
            'order' => 'ASC',
        ));
        $query = $query->get_posts();

        return count($query) ? (new Leyka_Donation($query[0]->ID)) : false;
    }
	
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
                'payment_type' => empty($meta['leyka_payment_type']) ? 'single' : $meta['leyka_payment_type'][0],
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
                'gateway_response' => empty($meta['leyka_gateway_response']) ? '' : $meta['leyka_gateway_response'][0],
                'recurrents_cancelled' => isset($meta['leyka_recurrents_cancelled']) ?
                    $meta['leyka_recurrents_cancelled'][0] : false,
                'recurrents_cancel_date' => isset($meta['leyka_recurrents_cancel_date']) ?
                    $meta['leyka_recurrents_cancel_date'][0] : false,

                'chronopay_customer_id' => empty($meta['_chronopay_customer_id']) ?
                    '' : $meta['_chronopay_customer_id'][0],
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
                return $this->gateway ?
                    leyka_get_gateway_by_id($this->gateway)->get_gateway_response_formatted($this) : array();

            case 'type':
            case 'payment_type': return $this->_donation_meta['payment_type']; break;
            case 'payment_type_label': return __($this->_donation_meta['payment_type'], 'leyka'); break;
            case 'recurrents_cancelled': return $this->_donation_meta['recurrents_cancelled']; break;
            case 'recurrents_cancel_date': return $this->_donation_meta['recurrents_cancel_date']; break;

            case 'chronopay_customer_id': return $this->_donation_meta['chronopay_customer_id']; break;
//            case '': return ''; break;
            default:
                return apply_filters('leyka_get_unknown_donation_field', null, $field, $this);
        }
    }

    public function __set($field, $value) {
        switch($field) {
            case 'status':
                if( !array_key_exists($value, leyka_get_donation_status_list()) || $value == $this->status )
                    return false;

                $res = wp_update_post(array('ID' => $this->_id, 'post_status' => $value));

                $status_log = get_post_meta($this->_id, '_status_log', true);
                $status_log[] = array('date' => time(), 'status' => $value);
                update_post_meta($this->_id, '_status_log', $status_log);

                break;

            case 'donor_email':
                update_post_meta($this->_id, 'leyka_donor_email', $value);
                break;

            case 'type':
            case 'payment_type':
                $value = ($value == 'rebill') ? 'rebill' : 'single';
                update_post_meta($this->_id, 'leyka_payment_type', $value);
                break;

            case 'recurrents_cancelled':
                $value = !!$value;
                update_post_meta($this->_id, 'leyka_recurrents_cancelled', $value);
                update_post_meta($this->_id, 'leyka_recurrents_cancel_date', $value ? time() : 0);
                break;

            case 'chronopay_customer_id':
                update_post_meta($this->_id, '_chronopay_customer_id', $value);
                break;
            default:
        }
    }

    public function add_gateway_response($resp_text) {

        $this->_donation_meta['gateway_response'] = $resp_text;

        update_post_meta($this->_id, 'leyka_gateway_response', $this->_donation_meta['gateway_response']);
    }

    /** @todo Maybe this method is not needed, as now we can set new status through __set(). */
    public function set_status($status) {

        if( !array_key_exists($status, leyka_get_donation_status_list()) || $status == $this->status )
            return false;

        $res = wp_update_post(array('ID' => $this->_id, 'post_status' => $status));

        $status_log = get_post_meta($this->_id, '_status_log', true);
        $status_log[] = array('date' => time(), 'status' => $status);
        update_post_meta($this->_id, '_status_log', $status_log);

        return $res;
    }
}


function leyka_cancel_recurrents_action() {

    if(
        empty($_POST['nonce'])
     || !wp_verify_nonce($_POST['nonce'], 'leyka_recurrent_cancel')
     || empty($_POST['donation_id'])
    )
        die('-1');

    $_POST['donation_id'] = (int)$_POST['donation_id'];

    $donation = new Leyka_Donation($_POST['donation_id']);
    do_action('leyka_cancel_recurrents-'.$donation->gateway_id, $donation);
}
//add_action('wp_ajax_leyka_cancel_recurrents', 'leyka_cancel_recurrents_action');
//add_action('wp_ajax_nopriv_leyka_cancel_recurrents', 'leyka_cancel_recurrents_action');