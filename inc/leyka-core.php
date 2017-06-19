<?php if( !defined('WPINC') ) die;

/** Core class. */
class Leyka {

    /**
     * Unique identifier for your plugin.
     *
     * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
     * match the Text Domain file header in the main plugin file.
     * @var string
     */
    protected $_plugin_slug = 'leyka';

    /**
     * Instance of this class.
     * @var object
     */
    protected static $_instance = null;

    /**
     * Gateways list.
     * @var array
     */
    protected $_gateways = array();

    /** @var array Of WP_Error instances. */
    protected $_form_errors = array();

    /** @var string Gateway URL to process payment data. */
    protected $_payment_url = '';

    /** @var string Gateway URL to process payment data. */
    protected $_auto_redirect = true;

    /** @var integer Currently submitted donation ID. */
    protected $_submitted_donation_id = 0;

    /** @var array Of key => value pairs of payment form vars to send to the Gateway URL. */
    protected $_payment_vars = array();

    /**
     * Template list.
     * @var array
     */
    protected $templates = null;

    /** @var bool|null */
    protected $_form_is_screening = false;

    /** Return a single instance of this class */
    public static function get_instance() {

        if( !self::$_instance ) {
            self::$_instance = new self;
        }

        return self::$_instance;

    }

    /** Initialize the plugin by setting up localization, filters, administration functions etc. */
    private function __construct() {
        
        if( !get_option('leyka_permalinks_flushed') ) {

            function leyka_rewrite_rules() {

                flush_rewrite_rules(false);
                update_option('leyka_permalinks_flushed', 1);

            }
            add_action('init', 'leyka_rewrite_rules');

        }

        // By default, we'll assume some errors in the payment form, so redirect will get us back to it:
        $this->_payment_form_redirect_url = wp_get_referer();
        
        $this->load_public_cssjs();

        add_action('init', array($this, 'register_post_types'), 1);

        add_action('init', array($this, 'register_user_capabilities'), 1);

        // Add/modify the rewrite rules:
        add_filter('rewrite_rules_array', array($this, 'insert_rewrite_rules'));
        add_filter('query_vars', array($this, 'insert_rewrite_query_vars'));

        function leyka_session_start() {
            if( !session_id() ) {
                session_start();
            }
        }
        add_action('init', 'leyka_session_start', -2);

        if(is_admin()) {

            require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-options-allocator.php');
            require_once(LEYKA_PLUGIN_DIR.'inc/leyka-render-settings.php');
            require_once(LEYKA_PLUGIN_DIR.'inc/leyka-admin.php');
            require_once(LEYKA_PLUGIN_DIR.'inc/leyka-donations-export.php');

            Leyka_Admin_Setup::get_instance();

        }

        add_action('admin_bar_menu', array($this, 'add_toolbar_menu'), 999);

        /** Service URLs handler: */
        add_action('parse_request', array($this, 'parse_request'));

        function leyka_get_posts(WP_Query $query) {

            if(is_admin() || !$query->is_main_query()) {
                return;
            }

            if($query->is_post_type_archive(Leyka_Donation_Management::$post_type)) {

                $query->set('post_status', 'funded');

                if(get_query_var('leyka_campaign_filter')) {

                    $campaign = get_posts(array(
                        'post_type' => Leyka_Campaign_Management::$post_type,
                        'name' => get_query_var('leyka_campaign_filter'))
                    );
                    if( !$campaign ) {
                        return;
                    }
                    $campaign = reset($campaign);

                    $query->set('meta_query', array(array(
                        'key'     => 'leyka_campaign_id',
                        'value'   => $campaign->ID,
                    ),));
                }

            }

        }
        add_action('pre_get_posts', 'leyka_get_posts', 1);

        function leyka_success_page_widget_template($content) {

            if(is_page(leyka_options()->opt('success_page')) && leyka_options()->opt('show_success_widget_on_success')) {

                ob_start();
                include(LEYKA_PLUGIN_DIR.'templates/service/leyka-template-success-widget.php');
                $content = ob_get_clean();

            }

            return $content;

        }
        add_filter('the_content', 'leyka_success_page_widget_template', 1);

        function leyka_failure_page_widget_template($content) {

            if(is_page(leyka_options()->opt('failure_page')) && leyka_options()->opt('show_failure_widget_on_failure')) {

                ob_start();
                include(LEYKA_PLUGIN_DIR.'templates/service/leyka-template-failure-widget.php');
                $content = ob_get_clean();

            }

            return $content;

        }
        add_filter('the_content', 'leyka_failure_page_widget_template', 1);
        
        function reinstall_cssjs_in_giger() {
            $theme = wp_get_theme();
            if($theme && $theme->template == 'giger' && !is_singular('leyka_campaign')) {
        
                if(get_the_ID() == leyka_options()->opt('failure_page') || get_the_ID() == leyka_options()->opt('success_page')) {
                    
                    $leyla_template_data = leyka_get_current_template_data();
                    
                    if($leyla_template_data['id'] == 'revo') {
                        $leyka = leyka();
                        $leyka->load_public_cssjs(); // force add leyka cssjs in giger for revo leyka theme
                    }
                    
                }
        
            }
        }
        add_action('template_redirect', 'reinstall_cssjs_in_giger', 90); # is important, in giger problem code run with priority 80
        
        if( !is_admin() ) {

            add_action('wp_head', 'leyka_inline_scripts');
            function leyka_inline_scripts(){

//                $colors = array('#07C7FD', '#05A6D3', '#8CE4FD'); // Leyka blue
                $colors = array('#1db318', '#1aa316', '#acebaa'); // Leyka green

                // detect if we have JS at all... ?>

                <script>
                    document.documentElement.classList.add("leyka-js");
                </script>
                <style>
                    :root {
                        --color-main: 		<?php echo $colors[0];?>;
                        --color-main-dark: 	<?php echo $colors[1];?>;
                        --color-main-light: <?php echo $colors[2];?>;
                    }
                </style>
                <?php
            }

            function leyka_template_init_include() {
                if(is_main_query() && is_singular(Leyka_Campaign_Management::$post_type)) { // Include template init script

                    $campaign = new Leyka_Campaign(get_queried_object_id());
                    $template = leyka_get_current_template_data($campaign);

                    if($template && isset($template['file'])) {

                        $init_file = LEYKA_PLUGIN_DIR.'templates/leyka-'.$template['id'].'/leyka-'.$template['id'].'-init.php';
                        if(file_exists($init_file)) {
                            require_once($init_file);
                        }

                    }

                }
            }
            add_action('wp_head', 'leyka_template_init_include');

        }

        /** Embed campaign URL handler: */
        function leyka_template_include($template) {

            if(is_main_query() && is_singular(Leyka_Campaign_Management::$post_type) && !empty($_GET['embed_object'])) {

                $new_template = leyka_get_current_template_data(false, 'embed_'.$_GET['embed_object'], true);
                if($new_template && !empty($new_template['file'])) {
                    $template = $new_template['file'];
                }

            }

            return $template;

        }
        add_filter('template_include', 'leyka_template_include', 100);

        add_action('template_redirect', array($this, 'gateway_redirect_page'), 1, 1);

        $this->apply_formatting_filters(); // Internal formatting filters

        /** Currency rates auto refreshment: */
        if(Leyka_Options_Controller::get_option_value('leyka_auto_refresh_currency_rates')) {

            if( !wp_next_scheduled('refresh_currencies_rates') ) {
                wp_schedule_event(time(), 'daily', 'refresh_currencies_rates');
            }

            add_action('refresh_currencies_rates', array($this, 'do_currencies_rates_refresh'));

            if( // Just in case..
                !Leyka_Options_Controller::get_option_value('leyka_currency_rur2usd')
                || !Leyka_Options_Controller::get_option_value('leyka_currency_rur2eur')
            ) {
                $this->_do_currency_rates_refresh();
            }

        } else {
            wp_clear_scheduled_hook('refresh_currencies_rates');
        }

        do_action('leyka_initiated');

    }
    
    public function load_public_cssjs() {
        // Load public-facing style sheet and JavaScript:
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles')); // wp_footer
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts')); // wp_footer
        add_action('wp_enqueue_scripts', array($this, 'localize_scripts')); // wp_footer
    }

    public function parse_request() {

        if(stristr($_SERVER['REQUEST_URI'], 'leyka/service') !== FALSE) { // Leyka service URL

            $request = explode('leyka/service', $_SERVER['REQUEST_URI']);
            $request = explode('/', trim($request[1], '/'));

            if($request[0] == 'do_recurring') { // Recurrents processing URL
                $this->_do_active_recurrents_rebilling();
            } else { // Gateway callback URL

                // Callback URLs are: some-website.org/leyka/service/{gateway_id}/{action_name}/
                // For ex., some-website.org/leyka/service/yandex/check_order/

                // $request[0] - Gateway ID, $request[1] - service action:
                do_action('leyka_service_call-'.$request[0], empty($request[1]) ? '' : $request[1]);

            }

            exit();

        }

    }

    public function add_toolbar_menu(WP_Admin_Bar $wp_admin_bar) {

        if( !current_user_can('leyka_manage_donations') ) {
            return;
        }

        $wp_admin_bar->add_node(array(
            'id' => 'leyka-toolbar-menu',
            'title' => __('Leyka', 'leyka'),
            'href' => admin_url('admin.php?page=leyka'),
        ));

        $wp_admin_bar->add_node(array(
            'id'     => 'leyka-toolbar-desktop',
            'title'  => __('Desktop', 'leyka'),
            'parent' => 'leyka-toolbar-menu',
            'href' => admin_url('admin.php?page=leyka'),
        ));
        $wp_admin_bar->add_node(array(
            'id'     => 'leyka-toolbar-donations',
            'title'  => __('Donations', 'leyka'),
            'parent' => 'leyka-toolbar-menu',
            'href' => admin_url('edit.php?post_type='.Leyka_Donation_Management::$post_type),
        ));
        $wp_admin_bar->add_node(array(
            'id'     => 'leyka-toolbar-campaigns',
            'title'  => __('Campaigns', 'leyka'),
            'parent' => 'leyka-toolbar-menu',
            'href' => admin_url('edit.php?post_type='.Leyka_Campaign_Management::$post_type),
        ));

        if(current_user_can('leyka_manage_options')) {
            $wp_admin_bar->add_node(array(
                'id'     => 'leyka-toolbar-settings',
                'title'  => __('Settings', 'leyka'),
                'parent' => 'leyka-toolbar-menu',
                'href' => admin_url('admin.php?page=leyka_settings'),
            ));
        }

    }

    protected function _do_currency_rates_refresh() {
        foreach(leyka_get_actual_currency_rates() as $currency => $rate) {
            update_option('leyka_currency_rur2'.mb_strtolower($currency), $rate);
        }
    }

    /** Proceed the rebill requests for all recurring subsriptions. */
    protected function _do_active_recurrents_rebilling() {

        ini_set('max_execution_time', 0);
        set_time_limit(0);
        ini_set('memory_limit', 268435456); // 256 Mb, just in case

        // Get all active initial donations for the recurring subscriptions:
        $params = array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'nopaging' => true,
            'post_status' => 'funded',
            'post_parent' => 0,
            'day' => (int)date('j'),
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'leyka_payment_type',
                    'value' => 'rebill',
                    'compare' => '=',
                ),
                array(
                    'key' => '_rebilling_is_active',
                    'value' => '1',
                    'compare' => '=',
                ),
            ),
        );

        foreach(get_posts($params) as $donation) {

            $donation = new Leyka_Donation($donation);

            $gateway = leyka_get_gateway_by_id($donation->gateway_id);
            if($gateway) {

                $new_recurring_donation = $gateway->do_recurring_donation($donation);
                if($new_recurring_donation && is_a($new_recurring_donation, 'Leyka_Donation')) {
                    Leyka_Donation_Management::send_all_recurring_emails($new_recurring_donation);
                }

            }

        }

    }

    public function __get($param) {
        switch($param) {
            case 'version': return LEYKA_VERSION;
            case 'plugin_slug': return $this->_plugin_slug;
            case 'payment_url': return $this->_payment_url;
            case 'payment_vars': return $this->_payment_vars;
            case 'submitted_donation_id':
            case 'donation_id': return $this->_submitted_donation_id;
            case 'auto_redirect': return !!$this->_auto_redirect;
            case 'form_is_screening': return !!$this->_form_is_screening;
            default:
                return '';
        }
    }

    public function __set($name, $value) {
        switch($name) {
            case 'form_is_screening':
                $value = !!$value;
                if( !$this->_form_is_screening && $value ) {
                    $this->_form_is_screening = $value;
                }
        }
    }

    public function add_payment_form_error(WP_Error $error) {
        $this->_form_errors[] = $error;
    }

    /** @return bool */
    public function payment_form_has_errors() {
        return count($this->_form_errors) > 0;
    }

    /** @return array Of WP_Error instances, if any. */
    public function get_payment_form_errors() {
        return $this->_form_errors;
    }

    /**
     * Wrapper to work with leyka_errors session var.
     * @param bool $anew
     */
    private function _add_session_errors($anew = false) {
        if(empty($_SESSION['leyka_errors']) || $anew)
            $_SESSION['leyka_errors'] = $this->get_payment_form_errors();
        else
            $_SESSION['leyka_errors'] = array_merge($_SESSION['leyka_errors'], $this->get_payment_form_errors());
    }

    /** @return bool */
    public function has_session_errors() {
        return !empty($_SESSION['leyka_errors']) && count($_SESSION['leyka_errors']);
    }

    /** @return array */
    public function get_session_errors() {
        return empty($_SESSION['leyka_errors']) ? array() : $_SESSION['leyka_errors'];
    }

    public function clear_session_errors() {
        $_SESSION['leyka_errors'] = array();
    }

    /**
     * Retrieve all available payment/donation statuses.
     *
     * @return array of status_id => status label pairs
     */
    public function get_donation_statuses() {
        return apply_filters('leyka_donation_statuses', array(
            'submitted' => _x('Submitted', '«Submitted» donation status', 'leyka'),
            'funded'    => _x('Funded', '«Completed» donation status', 'leyka'),
            'refunded'  => _x('Refunded', '«Refunded» donation status', 'leyka'),
            'failed'    => _x('Failed', '«Failed» donation status', 'leyka'),
            'trash'     => _x('Trash', '«Deleted» donation status', 'leyka'),
        ));
    }
    /**
     * Retrieve all available payment/donation statuses' descriptions.
     *
     * @return array of status_id => status_description pairs
     */
    public function get_donation_statuses_descriptions() {
        return apply_filters('leyka_donation_statuses_descriptions', array(
            'submitted' => _x("Donation attempt was made, but the payment itself wasn't sent.", '«Submitted» donation status description', 'leyka'),
            'funded' => _x('Donation was finished, the funds were made to your account.', '«Completed» donation status description', 'leyka'),
            'refunded' => _x('Donation funds were returned to the donor.', '«Refunded» donation status description', 'leyka'),
            'failed' => _x("Donation payment was finished with an error. The funds weren't sent.", '«Failed» donation status description', 'leyka'),
            'trash' => _x('Donation information was deleted.', '«Trash» donation status description', 'leyka'),
        ));
    }

    /**
     * Retrieve all available campaign target states.
     *
     * @return array of state_id => state label pairs
     */
    public function get_campaign_target_states() {
        return apply_filters('leyka_campaign_target_states', array(
            'no_target'   => _x('No target', 'Campaign state when target is not set', 'leyka'),
            'is_reached'  => _x('Reached', 'Campaign state when target is reached', 'leyka'),
            'in_progress' => _x('In progress', 'Campaign state when target is not reached yet', 'leyka'),
        ));
    }

    /**
     * @return array Of Leyka_Gateway objects.
     */
    public function get_gateways() {
        return $this->_gateways;
    }

    /**
     * @param Leyka_Gateway $gateway
     * @return bool
     */
    public function add_gateway(Leyka_Gateway $gateway) {

        if(empty($this->_gateways[$gateway->id])) {

            $this->_gateways[$gateway->id] = $gateway;
            return true;

        } else {
            return false;
        }

    }

    /** Just in case */
    public function remove_gateway($gateway_id) {
        if( !empty($this->_gateways[$gateway_id]) ) {
            unset($this->_gateways[$gateway_id]);
        }
    }

    /**
     * Fired when the plugin is activated or when an update is needed.
     */
    public static function activate() {

        $leyka_last_ver = get_option('leyka_last_ver');

        if($leyka_last_ver && $leyka_last_ver == LEYKA_VERSION) { // Already at last version
            return;
        }

        if( !$leyka_last_ver || $leyka_last_ver < '2.1' ) {

            /** Upgrade options structure in the DB */
            if(get_option('leyka_modules')) {
                delete_option('leyka_modules');
            }

            if(get_option('leyka_options_installed')) {
                delete_option('leyka_options_installed');
            }

            foreach(leyka_options()->get_options_names() as $name) {

                $option = get_option("leyka_$name");
                if(is_array($option) && isset($option['type']) && isset($option['title'])) { // Update option data
                    update_option("leyka_$name", $option['value']);
                }

            }

            /** Upgrade gateway and PM options structure in the DB */
            foreach(leyka_get_gateways() as $gateway) {

                /** @var $gateway Leyka_Gateway */
                delete_option("leyka_{$gateway->id}_payment_methods");

                foreach($gateway->get_options_names() as $name) {

                    $option = get_option("leyka_$name");

                    if(is_array($option) && isset($option['type']) && isset($option['title'])) { // Update option data
                        update_option("leyka_$name", $option['value']);
                    }

                }

                foreach($gateway->get_payment_methods() as $pm) {

                    /** @var $pm Leyka_Payment_Method */
                    foreach($pm->get_pm_options_names() as $name) {

                        $option = get_option("leyka_$name");
                        if(is_array($option) && isset($option['type']) && isset($option['title'])) // Update option data
                            update_option("leyka_$name", $option['value']);
                    }

                }

            }

        }

        if( !$leyka_last_ver || $leyka_last_ver <= '2.2.5' ) {

            // Initialize pm_order option if needed:
            if( !get_option('leyka_pm_order') ) {

                $pm_order = array();
                foreach((array)get_option('leyka_pm_available') as $pm_full_id) {
                    if($pm_full_id) {
                        $pm_order[] = "pm_order[]={$pm_full_id}";
                    }
                }

                update_option('leyka_pm_order', implode('&', $pm_order));

            }

            // Remove an unneeded scripts for settings pages:
            $settings_pages_dir = dir(LEYKA_PLUGIN_DIR.'inc/settings-pages/');
            while(false !== ($script = $settings_pages_dir->read())) {

                if(
                    $script != '.' && $script != '..' &&
                    !in_array($script, array('leyka-settings-common.php', 'leyka-settings-payment.php',))
                ) {
                    unlink(LEYKA_PLUGIN_DIR.'inc/settings-pages/'.$script);
                }
            }
            $settings_pages_dir->close();

            // Remove an obsolete plugin options:
            $options = array(
                array('old' => 'chronopay_card_description', 'new' => 'chronopay-chronopay_card_description'),
                array('old' => 'chronopay_card_rebill_description', 'new' => 'chronopay-chronopay_card_rebill_description'),
                array('old' => 'bank_order_description', 'new' => 'quittance-bank_order_description'),
                array('old' => 'bankcard_description', 'new' => 'rbk-bankcard_description'),
                array('old' => 'rbkmoney_description', 'new' => 'rbk-rbkmoney_description'),
                array('old' => 'rbk_all_description', 'new' => 'rbk-rbk_all_description'),
                array('old' => 'robokassa_card_description', 'new' => 'robokassa-BANKOCEAN2_description'),
                array('old' => 'robokassa_yandex_money_description', 'new' => 'robokassa-YandexMerchantOcean_description'),
                array('old' => 'robokassa_webmoney_description', 'new' => 'robokassa-WMR_description'),
                array('old' => 'robokassa_qiwi_description', 'new' => 'robokassa-Qiwi30Ocean_description'),
                array('old' => 'robokassa_all_description', 'new' => 'robokassa-Other_description'),
                array('old' => 'text_box_description', 'new' => 'text-text_box_description'),
                array('old' => 'yandex_card_description', 'new' => 'yandex-yandex_card_description'),
                array('old' => 'yandex_money_description', 'new' => 'yandex-yandex_money_description'),
                array('old' => 'yandex_wm_description', 'new' => 'yandex-yandex_wm_description'),
                array('old' => 'yandex_phyz_card_description', 'new' => 'yandex_phyz-yandex_phyz_card_description'),
                array('old' => 'yandex_phyz_money_description', 'new' => 'yandex_phyz-yandex_phyz_money_description'),
            );
            foreach($options as $option) {

                $old_value = get_option("leyka_{$option['old']}");
                $new_value = get_option("leyka_{$option['new']}");

                if($old_value && $old_value != $new_value) {
                    update_option("leyka_{$option['new']}", $old_value);
                }

                delete_option("leyka_{$option['old']}");
            }

        }

        /**
         * Fix the bug when total_funded amount of campaign was calculated incorrectly
         * if there were correctional donations for that campaign.
         */
        if($leyka_last_ver && $leyka_last_ver >= '2.2.5' && $leyka_last_ver <= '2.2.7.2') {

            function leyka_update_campaigns_total_funded() {

                set_time_limit(3600);
                wp_suspend_cache_addition(true);

                $campaigns = get_posts(array(
                    'post_type' => Leyka_Campaign_Management::$post_type,
                    'nopaging' => true,
                    'post_status' => 'any'
                ));
                foreach($campaigns as $campaign) {

                    $campaign = new Leyka_Campaign($campaign);
                    $campaign->update_total_funded_amount();
                }

                wp_suspend_cache_addition(false);

            }
            add_action('init', 'leyka_update_campaigns_total_funded', 100);

        }

        /** Fix the typo in one option's name */
        if($leyka_last_ver && $leyka_last_ver <= '2.2.7.2') {

            update_option('leyka_agree_to_terms_needed', get_option('leyka_argee_to_terms_needed'));
            delete_option('leyka_argee_to_terms_needed');

        }

        /** Fix the CloudPayments callbacks' IPs */
        if($leyka_last_ver && $leyka_last_ver <= '2.2.10') {
            update_option('leyka_cp_ip', '130.193.70.192,185.98.85.109');
        }

        if($leyka_last_ver && $leyka_last_ver <= '2.2.12.2') {
            delete_option('agree_to_terms_text'); // From now on, "agree to Terms" text field is separated in two new settings
        }

        if($leyka_last_ver && $leyka_last_ver <= '2.2.14') {

            if(in_array('chronopay-chronopay_card_rebill', (array)get_option('leyka_pm_available'))) {

                $pm_order_parts = explode('&', get_option('leyka_pm_order'));
                $key = array_search('chronopay-chronopay_card_rebill', $pm_order_parts);

                if($key !== false) {

                    unset($pm_order_parts[$key]);
                    update_option('leyka_pm_order', implode('&', $pm_order_parts));

                }

            }

        }

        /** Set a flag to flush permalinks (needs to be done a bit later, than this activation itself): */
        update_option('leyka_permalinks_flushed', 0);

        update_option('leyka_last_ver', LEYKA_VERSION);

    }

    /**
     * Fired when the plugin is deactivated.
     * @param boolean $network_wide True if WPMU superadmin uses "Network Deactivate" action,
     * false if WPMU is disabled or plugin is deactivated on an individual blog.
     */
    public static function deactivate() {
        delete_option('leyka_permalinks_flushed');
    }

    public function apply_formatting_filters() {

        add_filter('leyka_the_content', 'wptexturize');
        add_filter('leyka_the_content', 'convert_smilies');
        add_filter('leyka_the_content', 'convert_chars');
        add_filter('leyka_the_content', 'wpautop');

    }

    /** Register and enqueue public-facing style sheet. */
    public function enqueue_styles() {

        if(stristr($_SERVER['REQUEST_URI'], 'leyka-process-donation') !== FALSE) { // Leyka service URL

            wp_enqueue_style(
                $this->_plugin_slug.'-redirect-styles',
                LEYKA_PLUGIN_BASE_URL.'css/gateway-redirect-page.css',
                array(),
                LEYKA_VERSION
            );
            return;

        }
        
        // Revo template or success/failure widgets styles:
        if(leyka_revo_template_displayed() || leyka_success_widget_displayed() || leyka_failure_widget_displayed()) {
            wp_enqueue_style(
                $this->_plugin_slug.'-revo-plugin-styles',
                LEYKA_PLUGIN_BASE_URL.'assets/css/public.css',
                array(),
                LEYKA_VERSION
            );
        }

        if( !leyka_form_is_screening() ) {
            return;
        }
        
        // Enqueue the normal Leyka CSS just in case some other plugin elements exist on page:
        wp_enqueue_style(
            $this->_plugin_slug.'-plugin-styles',
            LEYKA_PLUGIN_BASE_URL.'css/public.css',
            array(),
            LEYKA_VERSION
        );

    }

    /** Register and enqueues public-facing JavaScript files. */
    public function enqueue_scripts() {

        // Revo template or success/failure widgets JS:
        if(leyka_revo_template_displayed() || leyka_success_widget_displayed() || leyka_failure_widget_displayed()) {
            wp_enqueue_script(
                $this->_plugin_slug.'-revo-public',
                LEYKA_PLUGIN_BASE_URL.'assets/js/public.js',
                array('jquery',),
                LEYKA_VERSION,
                true
            );
        }
        
        if( !leyka_form_is_screening() ) {
            return;
        }
        
        // Enqueue the normal Leyka scripts just in case some other plugin elements exist on page:
        wp_enqueue_script(
            $this->_plugin_slug.'-modal',
            LEYKA_PLUGIN_BASE_URL.'js/jquery.easyModal.min.js',
            array('jquery'),
            LEYKA_VERSION,
            true
        );

        wp_enqueue_script(
            $this->_plugin_slug.'-public',
            LEYKA_PLUGIN_BASE_URL.'js/public.js',
            array('jquery', $this->_plugin_slug.'-modal'),
            LEYKA_VERSION,
            true
        );

        do_action('leyka_enqueue_scripts'); // Allow the gateways to add their own scripts

    }

    public function localize_scripts() {

        $js_data = apply_filters('leyka_js_localized_strings', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'homeurl' => home_url('/'),
            'correct_donation_amount_required' => __('Donation amount must be specified to submit the form', 'leyka'),
            'donation_amount_too_great' => __('Donation amount you entered is too great (maximum %s allowed)', 'leyka'),
            'donation_amount_too_small' => __('Donation amount you entered is too small (minimum %s allowed)', 'leyka'),
            'amount_incorrect' => __('Set an amount from %s to %s <span class="curr-mark">%s</span>', 'leyka'),
            'donor_name_required' => __('Enter your name', 'leyka'),
            'oferta_agreement_required' => __('You have to agree with the terms of the donation service', 'leyka'),

            'checkbox_check_required' => __('This checkbox must be checked to submit the form', 'leyka'),
            'text_required' => __('This field must be filled to submit the form', 'leyka'),
            'email_required' => __('Email must be filled to submit the form', 'leyka'),
            'email_invalid' => __('Enter an email in the some@email.com format', 'leyka'),
            'must_not_be_email' => __("You shouldn't enter an email here", 'leyka'),
//            'email_regexp' => '',
        ));

        wp_localize_script(apply_filters('leyka_js_localized_script_id', $this->_plugin_slug.'-public'), 'leyka', $js_data);

    }

    /** Register leyka user roles and caps. */
    function register_user_capabilities() {

        $role = get_role('administrator'); // Just in case. There were some exotic cases..
        if( !$role ) {
            return;
        }

        /** Create all roles and capabilities: */
        $caps = array(
            'read' => true, 'edit_#base#' => true, 'read_#base#' => true, 'delete_#base#' => true,
            'edit_#base#s' => true, 'edit_others_#base#s' => true, 'publish_#base#s' => true,
            'read_private_#base#s' => true, 'delete_#base#s' => true, 'delete_private_#base#s' => true,
            'delete_published_#base#s' => true, 'delete_others_#base#s' => true,
            'edit_private_#base#s' => true, 'edit_published_#base#s' => true,
            'upload_files' => true, 'unfiltered_html' => true, 'leyka_manage_donations' => true,
        );

        if(empty($role->capabilities['leyka_manage_donations'])) {

            foreach($caps as $cap => $true) {

                $cap_donation = str_replace('#base#', 'donation', $cap);
                $role->add_cap($cap_donation, TRUE);
                $caps[$cap_donation] = TRUE;

                $cap_campaign = str_replace('#base#', 'campaign', $cap);
                $role->add_cap($cap_campaign, TRUE);
                $caps[$cap_campaign] = TRUE;

                if(stristr($cap, '#base#') !== FALSE)
                    unset($caps[$cap]);
            }
            $role->add_cap('leyka_manage_options', TRUE);
        }

        if( !get_role('donations_manager') ) {
            add_role('donations_manager', __('Donations Manager', 'leyka'), $caps);
        }
        if( !get_role('donations_administrator') ) {
            add_role('donations_administrator', __('Donations Administrator', 'leyka'), array_merge($caps, array('leyka_manage_options' => true,)));
        }

    }

    /**
     * Register leyka post types.
     */
    function register_post_types(){

        /** Donation CPT: */
        $args = array(
            'label' => __('Donations', 'leyka'),
            'labels' => array(
                'name'          => __('Donations', 'leyka'),
                'singular_name' => __('Donation', 'leyka'),
                'menu_name'     => __('Donations', 'leyka'),
                'all_items'     => __('Donations', 'leyka'),
                'add_new'       => __('Add correctional donation', 'leyka'),
                'add_new_item'  => __('Add correctional donation', 'leyka'),
                'edit_item'     => __('Donation profile', 'leyka'),
                'new_item'      => __('New donation', 'leyka'),
                'view_item'     => __('View donation', 'leyka'),
                'search_items'  => __('Search donation', 'leyka'),
                'not_found'     => __('Donations not found', 'leyka'),
                'not_found_in_trash' => __('Donations not found in Trash', 'leyka')
            ),
            'exclude_from_search' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_in_menu' => false,
            'show_in_admin_bar' => false,
            'supports' => false,
            'taxonomies' => array(),
            'has_archive' => 'donations',
            'capability_type' => array('donation', 'donations'),
            'map_meta_cap' => true,
            'rewrite' => array('slug' => 'donation', 'with_front' => false)
        );

        register_post_type(Leyka_Donation_Management::$post_type, $args);

        /** Donation editing messages */
        add_filter('post_updated_messages', array(Leyka_Donation_Management::get_instance(), 'set_admin_messages'));

        /** Campaign CPT: */
        $args = array(
            'labels' => array(
                'name'          => __('Campaigns', 'leyka'),
                'singular_name' => _x('Campaign', 'In genitive case', 'leyka'),
                'menu_name'     => __('Campaigns', 'leyka'),
                'all_items'     => __('All Campaigns', 'leyka'),
                'add_new'       => __('New campaign', 'leyka'),
                'add_new_item'  => __('Add new campaign', 'leyka'),
                'edit_item'     => __('Edit campaign', 'leyka'),
                'new_item'      => __('New campaign', 'leyka'),
                'view_item'     => __('View campaign', 'leyka'),
                'search_items'  => __('Search campaigns', 'leyka'),
                'not_found'     => __('Campaigns not found', 'leyka'),
                'not_found_in_trash' => __('Campaigns not found in Trash', 'leyka')
            ),
            'exclude_from_search' => false,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_nav_menus' => true,
            'show_in_menu' => false,
            'show_in_admin_bar' => true,
            'supports' => array('title', 'editor', 'thumbnail', 'revisions',),
            'taxonomies' => array(),
            'has_archive' => true,
            'capability_type' => array('campaign', 'campaigns'),
            'map_meta_cap' => true,
            'rewrite' => array('slug' => 'campaign', 'with_front' => false)
        );

        register_post_type(Leyka_Campaign_Management::$post_type, $args);

        /** Campaign editing messages */
        add_filter('post_updated_messages', array(Leyka_Campaign_Management::get_instance(), 'set_admin_messages'));

        register_post_status('submitted', array(
            'label'                     => _x('Submitted', '«Submitted» donation status', 'leyka'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Submitted <span class="count">(%s)</span>', 'Submitted <span class="count">(%s)</span>', 'leyka'),
        ));

        register_post_status('funded', array(
            'label'                     => _x('Funded', '«Completed» donation status', 'leyka'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Funded <span class="count">(%s)</span>', 'Funded <span class="count">(%s)</span>', 'leyka'),
        ));

        register_post_status('refunded', array(
            'label'                     => _x('Refunded', '«Refunded» donation status', 'leyka'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'leyka'),
        ));

        register_post_status('failed', array(
            'label'                     => _x('Failed', '«Failed» donation status', 'leyka'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'leyka'),
        ));

        do_action('leyka_cpt_registered');

    }

    /**
     * Add the plugin's rules themselves.
     * @var $rules array
     * @return array
     */
    public function insert_rewrite_rules(array $rules) {
        return array(
            'campaign/([^/]+)/donations/?$' => 'index.php?post_type='.Leyka_Donation_Management::$post_type.'&leyka_campaign_filter=$matches[1]',
            'campaign/([^/]+)/donations/page/([1-9]{1,})/?$' =>
                'index.php?post_type='.Leyka_Donation_Management::$post_type.'&leyka_campaign_filter=$matches[1]&paged=$matches[2]',
        ) + $rules; // The rules' order is important
    }

    /**
     * Add the special query var to indicate a donations archive filtering by particular campaign.
     * @var $vars array
     * @return array
     */
    public function insert_rewrite_query_vars(array $vars) {

        $vars[] = 'leyka_campaign_filter';
        return $vars;

    }

    /**
     * Payment form submissions.
     */
    public function gateway_redirect_page() {

        if(stristr($_SERVER['REQUEST_URI'], 'leyka-process-donation')) {

            if(empty($_POST)) {

                wp_redirect(site_url());
                exit();

            }
			
			if(is_admin_bar_showing()) { // Hide adminbar (toolbar) if needed
				add_filter('show_admin_bar', '__return_false');
			}

            add_filter('document_title_parts', 'leyka_remove_gateway_redirect_title', 10);
            function leyka_remove_gateway_redirect_title($title_parts){

                $title_parts['title'] = __('Redirecting to the gateway payment page', 'leyka');
                return $title_parts;

            }

            do_action('leyka_init_gateway_redirect_page');

            $this->_do_payment_form_submission();

            if($this->payment_form_has_errors() || !$this->_payment_url) {

                $this->_add_session_errors(); // Error handling

                $referer = wp_get_referer();
                if(strstr($referer, '#') !== false) {
                    $referer = reset(explode('#', $referer));
                }

                wp_redirect($referer.'#leyka-submit-errors');

            } else {

                header('HTTP/1.1 200 OK');

                require_once(LEYKA_PLUGIN_DIR.'templates/service/leyka-gateway-redirect-page.php');
                exit();

            }

        }

    } // template_redirect

    public function _do_payment_form_submission() {

        $this->clear_session_errors(); // Clear all previous submits errors, if there are some

        if( !wp_verify_nonce($_REQUEST['_wpnonce'], 'leyka_payment_form') ) {

            $error = new WP_Error('wrong_form_submission', __('Wrong nonce in submitted form data', 'leyka'));
            $this->add_payment_form_error($error);

        }

        $pm = explode('-', $_POST['leyka_payment_method']);
        if( !$pm || count($pm) < 2 ) {

            $error = new WP_Error('wrong_gateway_pm_data', __('Wrong gateway or/and payment method in submitted form data', 'leyka'));
            $this->add_payment_form_error($error);

        }

        $donor_name = leyka_pf_get_donor_name_value();
        if($donor_name && !leyka_validate_donor_name($donor_name)) {

            $error = new WP_Error('incorrect_donor_name', __('Incorrect donor name given while trying to add a donation', 'leyka'));
            $this->add_payment_form_error($error);

        }

        $donor_email = leyka_pf_get_donor_email_value();
        if($donor_email && !leyka_validate_email($donor_email)) {

            $error = new WP_Error('incorrect_donor_email', __('Incorrect donor email given while trying to add a donation', 'leyka'));
            $this->add_payment_form_error($error);

        }

        if($this->payment_form_has_errors()) {
            return;
        }

        $donation_id = $this->log_submission();

        if(is_wp_error($donation_id)) { /** @var WP_Error $donation_id */

            $this->add_payment_form_error($donation_id);
            return;

        } else if( !$donation_id ) {

            $error = new WP_Error('unknown_donation_submit_error', __('The donation was not created due to error.', 'leyka'));
            $this->add_payment_form_error($error);
            return;

        }

        leyka_remember_donation_data(array('donation_id' => $donation_id));

        do_action('leyka_payment_form_submission-'.$pm[0], $pm[0], implode('-', array_slice($pm, 1)), $donation_id, $_POST);

        $this->_submitted_donation_id = $donation_id;
        $this->_payment_vars = apply_filters('leyka_submission_form_data-'.$pm[0], $this->_payment_vars, $pm[1], $donation_id);

        $this->_payment_url = apply_filters('leyka_submission_redirect_url-'.$pm[0], $this->_payment_url, $pm[1]);

        $this->_auto_redirect = apply_filters('leyka_submission_auto_redirect-'.$pm[0], true, $pm[1], $donation_id);

    }

    /** Save a base submission info and return new donation ID, so gateway can add it's specific data to the logs. */
    public function log_submission() {

        if(empty($_POST['leyka_campaign_id']) || (int)$_POST['leyka_campaign_id'] <= 0) {
            return false;
        }

        $campaign = new Leyka_Campaign((int)$_POST['leyka_campaign_id']);
        $pm_data = leyka_pf_get_payment_method_value();

        $donation_id = Leyka_Donation::add(apply_filters('leyka_new_donation_data', array(
            'purpose_text' => $campaign->payment_title,
            'gateway_id' => $pm_data['gateway_id'],
        )));

        if(is_wp_error($donation_id)) {
            return $donation_id;
        } else {

            $campaign->increase_submits_counter();

            do_action('leyka_log_donation', $pm_data['gateway_id'], $pm_data['payment_method_id'], $donation_id);
            do_action('leyka_log_donation-'.$pm_data['gateway_id'], $donation_id);

            return $donation_id;

        }

    }

    /**
     * Templates manipulations.
     *
     * @param $is_service boolean True if templates is of service group, false otherwise.
     * @return array Template files.
     **/
    public function get_templates($is_service = false) {

        if( !$this->templates ) {
            $this->templates = array();
        }

        if( !!$is_service ) {
            $this->templates = glob(LEYKA_PLUGIN_DIR.'templates/service/leyka-template-*.php');
        } else {

            $custom_templates = glob(STYLESHEETPATH.'/leyka-template-*.php');
            $custom_templates = $custom_templates ? $custom_templates : array();

            $this->templates = array_merge(
                $custom_templates,
                glob(LEYKA_PLUGIN_DIR.'templates/leyka-template-*.php')
            );

        }

        if( !$this->templates ) {
            $this->templates = array();
        }

        $this->templates = array_map(array($this, 'get_template_data'), $this->templates);

        return (array)$this->templates;

    }

    public function get_template_data($file) {

        $headers = array(
            'name' => 'Leyka Template',
            'description' => 'Description',
        );

        $data = get_file_data($file, $headers);
        $data['file'] = $file;
        $data['basename'] = basename($file);
        $id = explode('-', str_replace('.php', '', $data['basename']));
        $data['id'] = end($id); // Otherwise error appears in php 5.4.x

        if(empty($data['name'])) {
            $data['name'] = $data['basename'];
        }

        return $data;

    }

    public function get_template($basename, $is_service = false) {

        $templates = $this->get_templates($is_service);
        if( !$templates ) {
            return false;
        }

        $active = '';
        foreach($templates as $template) {

            $cur_basename = explode('-', str_replace('.php', '', $template['basename']));
            $cur_basename = end($cur_basename); // Otherwise error appears in PHP 5.4.x
            if($cur_basename == $basename) {

                $active = $template;
                break;

            }
        }

        return $active;

    }

} // Leyka class end

// Shorthands for singletons instances:
/**
 * @return Leyka Core object
 */
function leyka() {
    return Leyka::get_instance();
}

/** Orphan strings to localize */
__('Radios', 'leyka');
__('Radio options for each payment method', 'leyka');
__('Toggles', 'leyka');
__('Toggled options for each payment method', 'leyka');
__('Neo', 'leyka');
__('An updated version of "Toggles" form template', 'leyka');
__('Revo', 'leyka');
__('A modern and lightweight step-by-step form template', 'leyka');
__('single', 'leyka');
__('rebill', 'leyka');
__('correction', 'leyka');
__('The donations management system for your WP site', 'leyka');
__('Teplitsa of Social Technologies', 'leyka');