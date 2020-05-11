<?php if( !defined('WPINC') ) die;

/** Core class. */
class Leyka extends Leyka_Singleton {

    protected static $_instance;

    /**
     * Unique identifier for the plugin.
     *
     * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
     * match the Text Domain file header in the main plugin file.
     * @var string
     */
    protected $_plugin_slug = 'leyka';

    /**
     * Templates order.
     * @var array
     */
    protected $_templates_order = array('need-help', 'star', 'revo', 'neo', 'toggles', 'radios',);

    /**
     * Gateways list.
     * @var array
     */
    protected $_gateways = array();

    /** @var array Of WP_Error instances. */
    protected $_form_errors = array();

    /** @var string Gateway URL to process payment data. */
    protected $_payment_url = '';

    /** @var mixed Donation form submission redirect type.
     * Possible values:
     *  - 'auto' to submit via POST,
     *  - 'redirect' to submit via GET,
     *  - boolean false to turn off auto-submitting
     */
    protected $_submission_redirect_type = 'auto';

    /** @var integer Currently submitted donation ID. */
    protected $_submitted_donation_id = 0;

    /** @var array Of key => value pairs of payment form vars to send to the Gateway URL. */
    protected $_payment_vars = array();

    /**
     * Template list.
     * @var array
     */
    protected $_templates = null;

    /** * @var array */
    protected $_extensions = array();

    /** @var bool|null */
    protected $_form_is_screening = false;

    /** Initialize the plugin by setting up localization, filters, administration functions etc. */
    protected function __construct() {

        if( !get_option('leyka_permalinks_flushed') ) {
            add_action('init', function(){

                flush_rewrite_rules(false);
                update_option('leyka_permalinks_flushed', 1);

            });
        }

        // By default, we'll assume some errors in the payment form, so redirect will get us back to it:
        $this->_payment_url = wp_get_referer();

        // Add GTM & UA e-commerce dataLayer if needed:
        if( in_array(leyka_options()->opt('use_gtm_ua_integration'), array('simple', 'enchanced', 'enchanced_ua_only')) ) {
            add_action('wp_head', array($this, 'add_gtm_data_layer_ua_'.leyka_options()->opt('use_gtm_ua_integration')), -1000);
        }

        $this->load_public_cssjs();

        add_action('init', array($this, 'register_post_types'), 1);
        add_action('init', array($this, 'register_user_capabilities'), 1);
        add_action('init', array($this, 'register_taxonomies'), 1);

        // Add/modify the rewrite rules:
        add_filter('rewrite_rules_array', array($this, 'insert_rewrite_rules'));
        add_filter('query_vars', array($this, 'insert_rewrite_query_vars'));

        add_action('parse_request', array($this, 'parse_request')); // Service URLs handlers

//        function leyka_session_start() {
//            if( !session_id() ) {
//                session_start();
//            }
//        }
//        add_action('init', 'leyka_session_start', -2);

        if(get_option('leyka_plugin_stats_option_needs_sync')) {

            function leyka_sync_stats_option() {

                $stats_option_synch_result = leyka_sync_plugin_stats_option();

                if(is_wp_error($stats_option_synch_result)) {
                    return $stats_option_synch_result;
                } else {
                    return delete_option('leyka_plugin_stats_option_needs_sync')
                        && update_option('leyka_plugin_stats_option_sync_done', time());
                }

            }
            add_action('admin_init', 'leyka_sync_stats_option');

        }

        add_action('admin_bar_menu', array($this, 'add_toolbar_menu'), 999);

        // For Donors management:
        if(get_option('leyka_donor_management_available')) {

            // Disable logging in if Donor is the only user's role (and user doesn't have an Account access capability):
            add_filter('authenticate', function($user, $username, $pass) {

                if($user && is_wp_error($user)) {
                    return $user;
                }

                if( !$user ) {

                    $logged_in_user = get_user_by('login', $username);
                    if(
                        $logged_in_user
                        && leyka_user_has_role(Leyka_Donor::DONOR_USER_ROLE, true, $logged_in_user)
                        && !$logged_in_user->has_cap(Leyka_Donor::DONOR_ACCOUNT_ACCESS_CAP)
                    ) {

                        remove_filter('authenticate', 'wp_authenticate_username_password', 20);
                        return null;

                    }

                }

                return $user;

            }, 1000, 3);

            // Refuse the login for Donors without Accounts:
            add_action('wp_login', function($login, $user){

                if( !$user || !is_a($user, 'WP_User') || wp_doing_ajax() ) {
                    return;
                }

                /** @var $user WP_User */
                if(
                    leyka_user_has_role(Leyka_Donor::DONOR_USER_ROLE, true, $user)
                    && !$user->has_cap(Leyka_Donor::DONOR_ACCOUNT_ACCESS_CAP)
                ) {

                    wp_logout();
                    wp_redirect(home_url());
                    exit;

                }

            }, 1000, 2);

            // Logout Donors if needed:
            add_action('init', function(){

                $user = wp_get_current_user();
                if(
                    leyka_user_has_role(Leyka_Donor::DONOR_USER_ROLE, true, $user)
                    && !$user->has_cap(Leyka_Donor::DONOR_ACCOUNT_ACCESS_CAP)
                ) {

                    wp_logout();
                    wp_redirect(home_url());
                    exit;

                }

            });

        }

        // For Donor accounts management:
        if(get_option('leyka_donor_accounts_available')) {

            // Don't show admin bar if Donor is the only user's role:
            add_action('init', function(){ // Don't show admin bar
                if(leyka_user_has_role(Leyka_Donor::DONOR_USER_ROLE, true)) {
                    add_filter('show_admin_bar', '__return_false');
                }
            }, 9);

            // If Donor resets his password via WordPress login page, add him an Account capability:
            add_action('after_password_reset', function(WP_User $user){

                if( !$user || !is_a($user, 'WP_User') || wp_doing_ajax() ) {
                    return;
                }

                if(
                    leyka_user_has_role(Leyka_Donor::DONOR_USER_ROLE, true, $user)
                    && !$user->has_cap(Leyka_Donor::DONOR_ACCOUNT_ACCESS_CAP)
                ) {
                    Leyka_Donor::update_account_access($user, true);
                }

            });

            // If Donor is successfully logged in, redirect him/her to the Account page:
            add_filter('login_redirect', function($redirect_to, $requested_redirect_to, $user){

                if( !$user || !is_a($user, 'WP_User') || wp_doing_ajax() ) {
                    return $redirect_to;
                }

                /** @var $user WP_User */
                return leyka_user_has_role(Leyka_Donor::DONOR_USER_ROLE, true, $user)
                    && $user->has_cap(Leyka_Donor::DONOR_ACCOUNT_ACCESS_CAP) ?
                    site_url('/donor-account/') : $redirect_to;

            }, 1000, 3);

            add_action('leyka_donor_account_created', array($this, 'handle_non_init_recurring_donor_registration'), 10, 2);
            add_action('leyka_donor_account_not_created', array($this, 'handle_donor_account_creation_error'), 10, 2);

        }

        // Donors management & Donors' accounts fields logical link:
        add_action('leyka_set_donor_accounts_available_option_value', function($option_value){
            if($option_value) {
                update_option('leyka_donor_management_available', true);
            }
        });

        if(is_admin()) { // Admin area only

            require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-options-allocator.php');
            require_once(LEYKA_PLUGIN_DIR.'inc/leyka-settings-rendering-utils.php');
            require_once(LEYKA_PLUGIN_DIR.'inc/leyka-admin.php');
            require_once(LEYKA_PLUGIN_DIR.'inc/leyka-donations-export.php');
            require_once(LEYKA_PLUGIN_DIR.'inc/leyka-usage-stats-functions.php');
            require_once(LEYKA_PLUGIN_DIR.'inc/leyka-class-portlet-controller.php');

            Leyka_Admin_Setup::get_instance();

            if(get_option('leyka_init_wizard_redirect')) {

                delete_option('leyka_init_wizard_redirect');

                add_action('admin_init', function(){

                    wp_redirect(admin_url('admin.php?page=leyka_settings_new&screen=wizard-init'));
                    exit;

                });

            }

        } else { // Public (non-admin) area only

            function leyka_get_posts(WP_Query $query) {

                if(is_admin() || !$query->is_main_query()) {
                    return;
                }

                if($query->is_post_type_archive(Leyka_Donation_Management::$post_type)) {

                    $query->set('post_status', 'funded');

                    if(get_query_var('leyka_campaign_filter')) {

                        $campaign = get_posts(array(
                            'post_type' => Leyka_Campaign_Management::$post_type,
                            'name' => get_query_var('leyka_campaign_filter'),
                            'posts_per_page' => 1,
                        ));
                        if( !$campaign ) {
                            return;
                        }
                        $campaign = reset($campaign);

                        $query->set('meta_query', array(array('key' => 'leyka_campaign_id', 'value' => $campaign->ID,),));

                    }

                }

            }
            add_action('pre_get_posts', 'leyka_get_posts', 1);

            function leyka_success_page_widget_template($content) {

                if(
                    is_page(leyka_options()->opt('success_page'))
                    && leyka_options()->opt_template('show_success_widget_on_success')
                    && is_main_query()
                ) {

                    $donation_id = leyka_remembered_data('donation_id');
                    $campaign = null;
                    $campaign_id = null;

                    if( !$donation_id ) {
                        return '';
                    }
                    $donation = new Leyka_Donation($donation_id);
                    $campaign_id = $donation ? $donation->campaign_id : null;
                    $campaign = new Leyka_Campaign($campaign_id);
                    
                    if($campaign) {
                        $form_template = $campaign->template;
                    } else {
                        $form_template = '';
                    }
                    
                    if( !$form_template ) {
                        $form_template = leyka_remembered_data('template_id');
                    }
                    
                    $form_template_suffix = $form_template === 'star' ? '-'.$form_template : '';

                    ob_start();
                    include LEYKA_PLUGIN_DIR.'templates/service/leyka-template-success-widget'.$form_template_suffix.'.php';
                    $content = ob_get_clean();

                    if($form_template === 'star') {
                        $content .= get_the_content();
                    }

                }

                return $content;

            }
            add_filter('the_content', 'leyka_success_page_widget_template', 1);

            function leyka_failure_page_widget_template($content) {

                if(
                    is_page(leyka_options()->opt('failure_page'))
                    && leyka_options()->opt_template('show_failure_widget_on_failure')
                    && is_main_query()
                ) {

                    ob_start();
                    include(LEYKA_PLUGIN_DIR.'templates/service/leyka-template-failure-widget.php');
                    $content = ob_get_clean();

                }

                return $content;

            }
            add_filter('the_content', 'leyka_failure_page_widget_template', 1);

            function reinstall_cssjs_in_giger() {

                $theme = wp_get_theme();
                if(
                    $theme
                    && in_array($theme->template, array('giger', 'giger-kms'))
                    && !is_singular(Leyka_Campaign_Management::$post_type)
                ) {

                    $is_cssjs_reqiured = false;

                    if(in_array(get_the_ID(), array(leyka_options()->opt('failure_page'), leyka_options()->opt('success_page')))) {
                        $is_cssjs_reqiured = true;
                    } else if(leyka_form_is_screening()) {
                        $is_cssjs_reqiured = true;
                    }

                    if($is_cssjs_reqiured) {

                        $leyka_template_data = leyka_get_current_template_data();

                        if($leyka_template_data['id'] == 'revo') {
                            leyka()->load_public_cssjs(); // Forcibly add Leyka cssjs in giger for Revo templates
                        }

                    }

                }
            }
            add_action('template_redirect', 'reinstall_cssjs_in_giger', 90); // 90 is important (Giger priority is 80)

            add_action('wp_head', 'leyka_inline_scripts');
            function leyka_inline_scripts(){

                $colors = array('#1db318', '#1aa316', '#acebaa'); // Leyka green ?>

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

            // Embed campaign URL handler:
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

        }

        $this->apply_content_formatting(); // Internal formatting filters

        // Currency rates auto refreshment - disabled for now

        if(class_exists('Leyka_Options_Controller')) {
            add_action('leyka_do_procedure', array($this, '_do_procedure'), 10, 2);
        }

        add_action('wp_loaded', array($this, 'refresh_donors_data'), 100);

        do_action('leyka_initiated');

    }

    public function __get($param) {
        switch($param) {
            case 'v':
            case 'version': return LEYKA_VERSION;
            case 'plugin_slug': return $this->_plugin_slug;
            case 'payment_url': return $this->_payment_url;
            case 'payment_vars': return $this->_payment_vars;
            case 'submitted_donation_id':
            case 'donation_id': return $this->_submitted_donation_id;
            case 'auto_redirect': return $this->_submission_redirect_type === 'auto';
            case 'redirect_type':
            case 'submission_redirect_type':
                return $this->_submission_redirect_type;
            case 'form_is_screening': return !!$this->_form_is_screening;
            case 'extensions': return !!$this->_extensions;
            default: return '';
        }
    }

    public function __set($name, $value) {
        switch($name) {
            case 'form_is_screening':
            case 'form_is_displaying':
            case 'form_displayed':
                if( !$this->_form_is_screening && !!$value ) {
                    $this->_form_is_screening = !!$value;
                }
                break;
            default:
        }
    }

    /**
     * A shorthand wrapper for the options getter method.
     * @param $option_id string
     * @param $new_value mixed
     * @return mixed
     */
    public function opt($option_id, $new_value = null) {
        return leyka_options()->opt($option_id, $new_value);
    }

    public function get_extension_by_id($extension_id) {

        $extension_id = trim($extension_id);

        return empty($this->_extensions[$extension_id]) ? false : $this->_extensions[$extension_id];

    }

    /**
     * @param boolean|NULL $activation_status If given, get only Extensions with it. NULL to get all Extensions.
     * @return array Of Leyka_Extension objects.
     */
    public function get_extensions($activation_status = null) {

        if( !$activation_status ) {
            return $this->_extensions;
        } else if( !in_array($activation_status, array('active', 'inactive', 'activating')) ) {
            return array(); /** @todo Throw some Leyka_Exception */
        } else {

            $extensions = array();
            foreach($this->_extensions as $extension) { /** @var $extension Leyka_Extension */
                if($extension->get_activation_status() === $activation_status) {
                    $extensions[] = $extension;
                }
            }

            return $extensions;

        }

    }

    /**
     * @param $extension_id string
     * @return boolean True if given Extension has given activation status, false otherwise.
     */
    public function extension_is_active($extension_id) {
        return leyka_options()->is_multi_value_checked('extensions_active', trim($extension_id));
    }

    public function refresh_donors_data() {

        if( !leyka_options()->opt('donor_management_available') ) {
            return;
        }

        $donations_ordered = get_transient('leyka_donations2refresh_donor_data_cache');

        if(is_array($donations_ordered)) {

            foreach($donations_ordered as $donation_id) {

                $donation = new Leyka_Donation($donation_id);
                if( !$donation ) {
                    return;
                }

                // Donor's data cache refresh:
                if($donation->donor_user_id) {
                    try {
                        Leyka_Donor::calculate_donor_metadata(new Leyka_Donor($donation->donor_user_id));
                    } catch(Exception $e) {
                        // ...
                    }
                }

            }

            delete_transient('leyka_donations2refresh_donor_data_cache');

        }

    }

    public function add_gtm_data_layer_ua_simple() {

        if( !is_main_query() || !is_page(leyka()->opt('success_page')) ) {
            return;
        }

        $donation_id = leyka_remembered_data('donation_id');
        $campaign = null;
        $campaign_id = null;

        if( !$donation_id ) {
            return;
        }

        $donation = new Leyka_Donation($donation_id);
        $campaign_id = $donation ? $donation->campaign_id : null;
        $campaign = new Leyka_Campaign($campaign_id);

        if( !$campaign->id ) {
            return;
        }

        $donation_amount_total = round((float)$donation->amount_total, 2);?>

        <script>
            window.dataLayer = window.dataLayer || [];

            dataLayer.push({
                'donorEmail': '<?php echo $donation->donor_email;?>',
                'transactionId': '<?php echo (int)$donation_id;?>',
                'transactionAffiliation': '<?php echo get_bloginfo('name');?>',
                'transactionTotal': <?php echo $donation_amount_total;?>,
                'transactionTax': 0,
                'transactionShipping': 0,
                'transactionProducts': [{
                    'sku': '<?php echo (int)$campaign_id;?>',
                    'name': '<?php echo esc_attr($campaign->title);?>',
                    'category': '<?php echo esc_attr($donation->type_label);?>',
                    'price': <?php echo $donation_amount_total;?>,
                    'quantity': 1
                }],
                'donationCampaignPaymentTitle': '<?php echo esc_attr($campaign->payment_title);?>',
                'donationFundedDate': '<?php echo esc_attr($donation->date_funded);?>',
                'donationGateway': '<?php echo esc_attr($donation->gateway_label);?>',
                'donationPm': '<?php echo esc_attr($donation->pm_label);?>',
                'donationType': '<?php echo esc_attr($donation->type_label);?>',
                'donationAmount': <?php echo esc_attr($donation->amount);?>,
                'donationCurrency': '<?php echo esc_attr($donation->currency_label);?>'
            });
        </script>

    <?php }

    public function add_gtm_data_layer_ua_enchanced() {

        if( !is_main_query() ) {
            return;
        }

        if(is_singular(Leyka_Campaign_Management::$post_type)) {

            // Single campaign display - use "detail" e-commerce measurement:

            $campaign = new Leyka_Campaign(get_the_ID());
            if( !$campaign->id ) {
                return;
            }?>

        <script>
            window.dataLayer = window.dataLayer || [];

            dataLayer.push({
                'event': 'eec.detail',
                'actionField': {
                    list: '<?php _e('Campaign page view', 'leyka');?>'
                },
                'ecommerce': {
                    'detail': {
                        'products': [{
                            'name': '<?php echo $campaign->title;?>',
                            'id': '<?php echo $campaign->id;?>',
                            'brand': '<?php echo get_bloginfo('name');?>',
                            'category': '<?php _e('Donations', 'leyka');?>'
                        }]
                    }
                }
            });
        </script>

        <?php } else if(is_page(leyka()->opt('success_page'))) {

            // Success page display - use "purchase" e-commerce measurement:

            $donation_id = leyka_remembered_data('donation_id');
            $campaign = null;
            $campaign_id = null;

            if( !$donation_id ) {
                return;
            }

            $donation = new Leyka_Donation($donation_id);
            $campaign_id = $donation ? $donation->campaign_id : null;
            $campaign = new Leyka_Campaign($campaign_id);

            if( !$campaign->id ) {
                return;
            }

            $donation_amount_total = round((float)$donation->amount_total, 2);?>

        <script>
            window.dataLayer = window.dataLayer || [];

            dataLayer.push({
                'event': 'eec.purchase',
                'ecommerce': {
                    //'currencyCode': <?php //echo $donation->currency;?>//, // For some reason i doesn't work
                    'purchase': {
                        'actionField': {
                            'id': '<?php echo $donation->id;?>',
                            'affiliation': '<?php echo $campaign->title;?>',
                            'revenue': '<?php echo $donation_amount_total;?>',
                            'tax': 0,
                            'shipping': 0
                        },
                        'products': [{
                            'name': '<?php echo $campaign->title;?>',
                            'id': '<?php echo $donation->id;?>',
                            'price': '<?php echo $donation_amount_total;?>',
                            'brand': '<?php echo get_bloginfo('name');?>',
                            'category': '<?php echo $donation->type_label;?>',
                            'quantity': 1
                        }]
                    }
                }
            });
        </script>
        <?php }

        // Donation form submit click - "add" e-commerce measurement used in JS 'submit.leyka' handlers ?>

    <?php }

    public function add_gtm_data_layer_ua_enchanced_ua_only() {

        if( !is_main_query()) {
            return;
        }

        if(is_singular(Leyka_Campaign_Management::$post_type)) {

            if( // GUA direct integration - "detail" event:
                leyka_options()->opt('use_gtm_ua_integration') === 'enchanced_ua_only'
                && leyka_options()->opt('gtm_ua_tracking_id')
                && in_array('detail', leyka_options()->opt('gtm_ua_enchanced_events'))
            ) {

                require_once LEYKA_PLUGIN_DIR.'vendor/autoload.php';

                $campaign = new Leyka_Campaign(get_the_ID());

                $analytics = new TheIconic\Tracking\GoogleAnalytics\Analytics(true);
                $analytics // Main params:
                    ->setProtocolVersion('1')
                    ->setTrackingId(leyka_options()->opt('gtm_ua_tracking_id'))
                    ->setClientId(leyka_gua_get_client_id())
                    ->setDocumentLocationUrl(get_permalink($campaign->id))
                    // Transaction params:
                    ->addProduct(array( // Campaign params
                        'name' => $campaign->payment_title,
                        'brand' => get_bloginfo('name'), // Mb, it won't work with it
                    ))
                    ->setProductActionToDetail()
                    ->setEventCategory('Checkout')
                    ->setEventAction('Detail')
                    ->sendEvent();

            }
            // GUA direct integration - "detail" event END

        }

    }

    /** @todo Create a procedure to get actual currencies rates and save them in the plugin options values */
    public function do_currencies_rates_refresh() {
    }

    public function load_public_cssjs() {

        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'localize_scripts'));

    }

    public function parse_request() {

        if(stristr($_SERVER['REQUEST_URI'], 'leyka/service') !== FALSE) { // Leyka service URL

            $request = explode('leyka/service', $_SERVER['REQUEST_URI']);
            $request = explode('/', trim($request[1], '/'));

            if($request[0] === 'do_recurring') { // Active recurring shortcut URL
                do_action('leyka_do_procedure', 'active-recurring');
            } else if($request[0] === 'cancel_recurring' && !empty($request[1]) && !empty($request[2])) {

                $donation = new Leyka_Donation($request[1]);
                $init_recurrent_donation = Leyka_Donation::get_init_recurring_donation($donation);
                $hash = md5($donation->id.'_'.$init_recurrent_donation->id.'_leyka_cancel_recurring_subscription');

                if($donation && $hash == $request[2]) {
                    do_action("leyka_{$donation->gateway_id}_cancel_recurring_subscription", $donation);
                }

            } else if($request[0] === 'do_campaigns_targets_reaching_mailout') {

                // Campaigns targets reached mailout shortcut URL:
                do_action(
                    'leyka_do_procedure',
                    'campaigns-targets-reaching-mailout',
                    empty($request[1]) ? array() : array((int)$request[1])
                );

            } else if(isset($request[0], $request[1]) && stristr($request[0], 'procedure') !== false) {

                // Common procedure call URL,
                // like some-website.org/leyka/service/procedure/{procedure_name}[/{param_1}/{param_2}/...]
                // E.g.:
                // * some-website.org/leyka/service/procedure/active-recurring
                // * some-website.org/leyka/service/procedure/campaigns-targets-reaching-mailout/123

                do_action('leyka_do_procedure', $request[1], array_slice($request, 2));

            } else if($request[0] === 'get_usage_stats') {

                require_once LEYKA_PLUGIN_DIR.'bin/sodium-compat.phar';

                if( !$this->_outer_request_allowed() ) {
                    exit;
                }

                echo empty($_GET['tst']) ?
                    \Sodium\crypto_box_seal(
                        json_encode($this->_get_usage_stats($_REQUEST)),
                        \Sodium\hex2bin(get_option('leyka_stats_sipher_public_key'))
                    ) :
                    '<pre>'.print_r($this->_get_usage_stats($_REQUEST), 1).'</pre>';

            } else { // Gateway callback URL

                // Callback URLs are: some-website.org/leyka/service/{gateway_id}/{action_name}/
                // E.g., some-website.org/leyka/service/yandex/check_order/

                if( !empty($request[1]) && stristr($request[1], '?') !== false ) { // Remove GET params from the callback
                    $request[1] = mb_substr($request[1], 0, stripos($request[1], '?'));
                }

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
            'id'  => 'leyka-toolbar-desktop',
            'title' => __('Desktop', 'leyka'),
            'parent' => 'leyka-toolbar-menu',
            'href' => admin_url('admin.php?page=leyka'),
        ));
        $wp_admin_bar->add_node(array(
            'id' => 'leyka-toolbar-donations',
            'title' => __('Donations', 'leyka'),
            'parent' => 'leyka-toolbar-menu',
            'href' => admin_url('edit.php?post_type='.Leyka_Donation_Management::$post_type),
        ));
        $wp_admin_bar->add_node(array(
            'id' => 'leyka-toolbar-campaigns',
            'title' => __('Campaigns', 'leyka'),
            'parent' => 'leyka-toolbar-menu',
            'href' => admin_url('edit.php?post_type='.Leyka_Campaign_Management::$post_type),
        ));

        if(current_user_can('leyka_manage_options')) {
            $wp_admin_bar->add_node(array(
                'id' => 'leyka-toolbar-settings',
                'title' => __('Settings', 'leyka'),
                'parent' => 'leyka-toolbar-menu',
                'href' => admin_url('admin.php?page=leyka_settings'),
            ));
        }

    }

    /** @todo Make it a procedure */
    public function _do_currency_rates_refresh() {
        foreach(leyka_get_actual_currency_rates() as $currency => $rate) {
            update_option('leyka_currency_rur2'.mb_strtolower($currency), $rate);
        }
    }

    public function _do_procedure($procedure_id, $params = array()) {

        do_action('leyka_before_procedure', $procedure_id, $params);

        $procedure_script = LEYKA_PLUGIN_DIR.'procedures/leyka-'.$procedure_id.'.php';
        if(file_exists($procedure_script)) {

            $_POST['procedure_params'] = $params;
            require $procedure_script;

        }

        do_action('leyka_after_procedure', $procedure_id, $params);

    }

    protected function _outer_request_allowed() {

        $home_url_clear = rtrim(home_url(), '/');

        return isset($_SERVER['PHP_AUTH_USER'])
            && $_SERVER['PHP_AUTH_USER'] === 'stats-collector'
            && $_SERVER['PHP_AUTH_PW'] === md5($home_url_clear.'-'.get_option('leyka_stats_sipher_public_key'));

    }

    protected function _get_usage_stats(array $params = array()) {

        /** @todo Use Donations_Factory here */
        $query_params = array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => 'any',
            'meta_query' => array(
                'relation' => 'AND',
                array('key' => 'leyka_payment_type', 'value' => 'correction', 'compare' => '!='),
            ),
            'nopaging' => true,
        );
        if( !empty($params['timestamp_from']) && (int)$params['timestamp_from'] > 0 ) { // 'date_from' must be a timestamp

            $query_params['date_query']['after'] = date('Y-m-d H:i:s', (int)$params['timestamp_from']);
            $query_params['date_query']['inclusive'] = true;

            if( !empty($params['period']) ) { // Must be strtotime()-compatible string w/o sign (1 hour, 2 weeks, 3 months, ...)

                $params['period'] = str_replace(array('+', '-'), '', $params['period']);

                $query_params['date_query']['before'] = date(
                    'Y-m-d H:i:s', strtotime($query_params['date_query']['after'].' +'.$params['period'])
                );

            }

        }

        if( !empty($query_params['date_query']) ) {
            $query_params['date_query'] = array($query_params['date_query']);
        }

        $stats = array('donations' => array(),) + leyka_get_env_and_options();

        foreach(get_posts($query_params) as $donation) {

            $donation = new Leyka_Donation($donation);

            $donations_by_status = array();
            foreach(leyka_get_donation_status_list() as $status => $label) {
                $donations_by_status[$status] = 0;
            }

            if(empty($stats['donations'][$donation->gateway][$donation->pm])) {
                $stats['donations'][$donation->gateway][$donation->pm] = array(
                    'main_currency' => 'RUB',
                    'amount_collected' => 0.0, // In main currency
                    'donations_count' => 0,
                    'donations_by_status_count' => $donations_by_status,
                );
            }

            if($donation->status === 'funded') {
                $stats['donations'][$donation->gateway][$donation->pm]['amount_collected'] += $donation->main_curr_amount;
            }

            $stats['donations'][$donation->gateway][$donation->pm]['donations_count'] += 1;
            $stats['donations'][$donation->gateway][$donation->pm]['donations_by_status_count'][$donation->status] += 1;

        }

        return $stats;

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
        if(empty($_SESSION['leyka_errors']) || $anew) {
            $_SESSION['leyka_errors'] = $this->get_payment_form_errors();
        } else {
            $_SESSION['leyka_errors'] = array_merge($_SESSION['leyka_errors'], $this->get_payment_form_errors());
        }
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

    public function get_donation_types() {
        return apply_filters('leyka_donation_types', array(
            'single' => _x('Single', '"Single" donation type name', 'leyka'),
            'recurring' => _x('Recurring', '"Recurring" donation type name', 'leyka'),
            'correction' => _x('Correction', '"Correction" donation type name', 'leyka'),
        ));
    }

    public function get_donation_types_descriptions() {
        return apply_filters('leyka_donation_types_descriptions', array(
            'single' => _x("A one-time donation.", '«Single» donation type description', 'leyka'),
            'recurring' => _x('A monthly recurring donation.', '«Recurring» donation type description', 'leyka'),
            'correction' => _x('A donation manually added by the website administration.', '«Refunded» donation status description', 'leyka'),
        ));
    }

    /**
     * Retrieve all available payment/donation statuses.
     *
     * @param $with_hidden boolean
     * @return array of status_id => status label pairs
     */
    public function get_donation_statuses($with_hidden = true) {

        $with_hidden = !!$with_hidden;

        $statuses = apply_filters('leyka_donation_statuses', array(
            'submitted' => _x('Submitted', '«Submitted» donation status', 'leyka'),
            'funded'    => _x('Funded', '«Completed» donation status', 'leyka'),
            'refunded'  => _x('Refunded', '«Refunded» donation status', 'leyka'),
            'failed'    => _x('Failed', '«Failed» donation status', 'leyka'),
            'trash'     => _x('Trash', '«Deleted» donation status', 'leyka'),
        ), $with_hidden);

        if( !$with_hidden && isset($statuses['trash']) ) {
            unset($statuses['trash']);
        }

        return $statuses;

    }

    /**
     * Retrieve all available payment/donation statuses' descriptions.
     *
     * @return array of status_id => status_description pairs
     */
    public function get_donation_statuses_descriptions() {
        return apply_filters('leyka_donation_statuses_descriptions', array(
            'submitted' => _x("Donation attempt was made, but the payment itself wasn't sent.\n\nOr, maybe, the payment was completed, but Leyka wasn't notified of it. If that is the case, you should check if your payment gateway callbacks are set up correctly.", '«Submitted» donation status description', 'leyka'),
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
     * @param mixed $activation_status If given, get only gateways with it.
     * NULL for both types altogether.
     * @return array Of Leyka_Gateway objects.
     */
    public function get_gateways($activation_status = null) {

        if( !$activation_status ) {
            return $this->_gateways;
        } else if( !in_array($activation_status, array('active', 'inactive', 'activating')) ) {
            return array(); /** @todo Throw some Leyka_Exception */
        } else {

            $gateways = array();
            foreach($this->_gateways as $gateway) { /** @var $gateway Leyka_Gateway */
                if($gateway->get_activation_status() === $activation_status) {
                    $gateways[] = $gateway;
                }
            }

            return $gateways;

        }

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

    public function remove_gateway($gateway_id) {
        if( !empty($this->_gateways[$gateway_id]) ) {
            unset($this->_gateways[$gateway_id]);
        }
    }

    /**
     * @param Leyka_Extension $extension
     * @return bool
     */
    public function add_extension(Leyka_Extension $extension) {

        if(empty($this->_extensions[$extension->id]) && (leyka_options()->opt('plugin_demo_mode') || !$extension->debug_only) ) {

            $this->_extensions[$extension->id] = $extension;
            return true;

        } else {
            return false;
        }

    }

    public function remove_extension($extension_id) {
        if( !empty($this->_extensions[$extension_id]) ) {
            unset($this->_extensions[$extension_id]);
        }
    }

    /** Fired when the plugin is activated or when an update is needed. */
    public static function activate() {

        $leyka_last_ver = get_option('leyka_last_ver');

        if($leyka_last_ver && $leyka_last_ver == LEYKA_VERSION) { // Already at last version
            return;
        }

        if( !$leyka_last_ver ) {
            update_option('leyka_init_wizard_redirect', true);
        }

        if( !$leyka_last_ver || $leyka_last_ver < '3.1.1' ) {
            if(get_option('leyka_show_gtm_dataLayer_on_success')) {

                update_option('leyka_use_gtm_ua_integration', 'simple');
                delete_option('leyka_show_gtm_dataLayer_on_success');

            }
        }

        if( !$leyka_last_ver || $leyka_last_ver < '3.3' ) {

            // Update "donor account" donation meta storage. "donor_account_error" meta for errors, post_author field for donors:
            $donations = get_posts(array(
                'post_type' => Leyka_Donation_Management::$post_type,
                'post_status' => leyka_get_donation_status_list(false),
                'posts_per_page' => -1,
                'meta_query' => array(array('key' => 'leyka_donor_account', 'compare' => 'EXISTS'),),
                'fields' => 'ids',
            ));
            foreach($donations as $donation_id) {

                $donor_account = maybe_unserialize(get_post_meta($donation_id, 'leyka_donor_account', true));

                if(is_wp_error($donor_account)) {
                    update_post_meta($donation_id, 'donor_account_error', $donor_account);
                } else if((int)$donor_account > 0) {

                    $donor_user = get_user_by('id', (int)$donor_account);
                    if($donor_user && leyka_user_has_role(Leyka_Donor::DONOR_USER_ROLE, false, $donor_user)) {
                        wp_update_post(array('ID' => $donation_id, 'post_author' => $donor_user->ID,));
                    }

                }

                delete_post_meta($donation_id, 'leyka_donor_account');

            }

            // Add the new "Donor's account access" role:
            $donor_account_users = get_users(array('role__in' => array(Leyka_Donor::DONOR_USER_ROLE,), 'number' => -1,));

            $old_donor_role = get_role(Leyka_Donor::DONOR_USER_ROLE);
            if($old_donor_role) {
                $old_donor_role->remove_cap('access_donor_account_desktop');
            }

            foreach($donor_account_users as $donor_user) {

                $donor_user->add_cap(Leyka_Donor::DONOR_ACCOUNT_ACCESS_CAP);

                try { // Initialize & fill the Donor Cache for all existing Donor users
                    Leyka_Donor::calculate_donor_metadata(new Leyka_Donor($donor_user));
                } catch(Exception $e) {
                    //...
                }

            }

        }

        // From v3.3.0.1 - enable Donors management by default for all new installations:
        if( !$leyka_last_ver || $leyka_last_ver <= '3.3.0.1' ) {
            update_option('leyka_donor_management_available', true);
        }

        if($leyka_last_ver && $leyka_last_ver <= '3.5') { // Allow the deprecated form templates for old installations
            update_option('leyka_allow_deprecated_form_templates', true);
        }

        if($leyka_last_ver && $leyka_last_ver <= '3.6') { // Donors management & Donors' accounts fields logical link
            if(get_option('leyka_donor_accounts_available')) {
                update_option('leyka_donor_management_available', true);
            }
        }

        if($leyka_last_ver && $leyka_last_ver <= '3.8.0.1') { // CP IPs list fix
            if(get_option('leyka_cp_ip')) {
                update_option('leyka_cp_ip', '130.193.70.192, 185.98.85.109, 87.251.91.160/27, 185.98.81.0/28');
            }
        }

        // Set a flag to flush permalinks (needs to be done a bit later, than this activation itself):
        update_option('leyka_permalinks_flushed', 0);

        update_option('leyka_last_ver', LEYKA_VERSION);

    }

    public static function deactivate() {
        delete_option('leyka_permalinks_flushed');
    }

    public function apply_content_formatting() {

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

        if( // New CSS
            !leyka_options()->opt('load_scripts_if_need')
            || leyka_modern_template_displayed()
            || leyka_success_widget_displayed()
            || leyka_failure_widget_displayed()
            || leyka_persistent_campaign_donated()
            || leyka_is_widget_active()
        ) {
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
        
        $this->add_inline_custom_css();

    }

    protected function add_inline_custom_css() {

        $campaign_id = null;
        if(is_singular(Leyka_Campaign_Management::$post_type)) {
            $campaign_id = get_the_ID();
        } else if(is_page(leyka()->opt('success_page')) || is_page(leyka()->opt('failure_page'))) {
            $donation_id = leyka_remembered_data('donation_id');
            $donation = $donation_id ? new Leyka_Donation($donation_id) : null;
            $campaign_id = $donation ? $donation->campaign_id : null;
        }

        if($campaign_id) {
            $custom_css = get_post_meta($campaign_id, 'campaign_css', true);
            wp_add_inline_style($this->_plugin_slug.'-revo-plugin-styles', $custom_css);
        }

    }

    /** Register and enqueue public-facing JavaScript files. */
    public function enqueue_scripts() {

        if( // New JS:
            !leyka_options()->opt('load_scripts_if_need')
            || leyka_modern_template_displayed()
            || leyka_success_widget_displayed()
            || leyka_failure_widget_displayed()
        ) {
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
            'plugin_url' => LEYKA_PLUGIN_BASE_URL,
            'gtm_ga_eec_available' => (int)(leyka()->opt('use_gtm_ua_integration') === 'enchanced'),

            'correct_donation_amount_required_msg' => __('Donation amount must be specified to submit the form', 'leyka'),
            'donation_amount_too_great_msg' => __('Donation amount you entered is too great (maximum %s allowed)', 'leyka'),
            'donation_amount_too_small_msg' => __('Donation amount you entered is too small (minimum %s allowed)', 'leyka'),
            'amount_incorrect_msg' => __('Set an amount from %s to %s <span class="curr-mark">%s</span>', 'leyka'),
            'donor_name_required_msg' => __('Enter your name', 'leyka'),
            'oferta_agreement_required_msg' => __('You have to agree with the terms of the donation service', 'leyka'),

            'checkbox_check_required_msg' => __('This checkbox must be checked to submit the form', 'leyka'),
            'text_required_msg' => __('This field must be filled to submit the form', 'leyka'),
            'email_required_msg' => __('Email must be filled to submit the form', 'leyka'),
            'email_invalid_msg' => __('Enter an email in the some@email.com format', 'leyka'),
            'must_not_be_email_msg' => __("You shouldn't enter an email here", 'leyka'),
            'value_too_long_msg' => __('Entered value is too long', 'leyka'),
            'error_while_unsibscribe_msg' => __('Error while requesting unsubscription', 'leyka'),
            'default_error_msg' => __('Error', 'leyka'),
        ));

        $leyka_js_handle = wp_script_is($this->_plugin_slug.'-public') ?
            $this->_plugin_slug.'-public' :
            (wp_script_is($this->_plugin_slug.'-revo-public') ? $this->_plugin_slug.'-revo-public' : '');

        wp_localize_script(apply_filters('leyka_js_localized_script_id', $leyka_js_handle), 'leyka', $js_data);

    }

    /** Register leyka user roles and caps. */
    public function register_user_capabilities() {

        $role = get_role('administrator'); // Just in case. There were some exotic cases..
        if( !$role ) {
            return;
        }

        // Create all roles and capabilities:
        $caps = array(
            'read' => true, 'edit_#base#' => true, 'read_#base#' => true, 'delete_#base#' => true,
            'edit_#base#s' => true, 'edit_others_#base#s' => true, 'publish_#base#s' => true,
            'read_private_#base#s' => true, 'delete_#base#s' => true, 'delete_private_#base#s' => true,
            'delete_published_#base#s' => true, 'delete_others_#base#s' => true,
            'edit_private_#base#s' => true, 'edit_published_#base#s' => true,
            'upload_files' => true, 'unfiltered_html' => true, 'leyka_manage_donations' => true,
        );

        foreach($caps as $cap => $true) {

            $cap_donation = str_replace('#base#', 'donation', $cap);

            if(empty($role->capabilities[$cap_donation])) {
                $role->add_cap($cap_donation, true);
            }

            $caps[$cap_donation] = true;

            $cap_campaign = str_replace('#base#', 'campaign', $cap);

            if(empty($role->capabilities[$cap_campaign])) {
                $role->add_cap($cap_campaign, true);
            }

            $caps[$cap_campaign] = true;

            if(stristr($cap, '#base#') !== false) {
                unset($caps[$cap]);
            }

        }

        if(empty($role->capabilities['leyka_manage_options'])) {
            $role->add_cap('leyka_manage_options', true);
        }

        if( !get_role('donations_manager') ) {
            add_role('donations_manager', __('Donations Manager', 'leyka'), $caps);
        }
        if( !get_role('donations_administrator') ) {
            add_role(
                'donations_administrator',
                __('Donations Administrator', 'leyka'),
                array_merge($caps, array('leyka_manage_options' => true,))
            );
        }

        // Donor roles:
        if(leyka()->opt('donor_management_available')) {
            if( !get_role(Leyka_Donor::DONOR_USER_ROLE) ) {
                add_role(Leyka_Donor::DONOR_USER_ROLE, __('Donor', 'leyka'));
            }
        }

    }

    /**
     * Register leyka post types.
     */
    public function register_post_types(){

        // Donations:
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
            'rewrite' => array('slug' => 'donation', 'with_front' => false),
            'show_in_rest' => false, // True to use Gutenberg editor, false otherwise
        );

        register_post_type(Leyka_Donation_Management::$post_type, $args);

        // Donation editing messages:
        add_filter('post_updated_messages', array(Leyka_Donation_Management::get_instance(), 'set_admin_messages'));

        // Campaigns:
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
            'rewrite' => array('slug' => 'campaign', 'with_front' => false),
            'show_in_rest' => false, // True to use Gutenberg editor, false otherwise
        );

        register_post_type(Leyka_Campaign_Management::$post_type, $args);

        // Campaign editing messages:
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

    public function register_taxonomies() {

        if( !leyka()->opt('donor_management_available') ) {
            return;
        }

        register_taxonomy(
            Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME,
            'user',
            array(
                'public' => true,
                'labels' => array(
                    'name' => __('Donors tags', 'leyka'),
                    'singular_name'	=> __('Donors tag', 'leyka'),
                    'menu_name'	=> __('Donors tags', 'leyka'),
                    'search_items' => __('Search donors tag', 'leyka'),
                    'popular_items' => __('Popular donors tags', 'leyka'),
                    'all_items'	=> __('All donors tags', 'leyka'),
                    'edit_item'	=> __('Edit donors tag', 'leyka'),
                    'update_item' => __('Update donors tag', 'leyka'),
                    'add_new_item' => __('Add new donors tag', 'leyka'),
                    'new_item_name'	=> __('New donors tag name', 'leyka'),
                ),
                /** @todo Add a custom function for it */
//                'update_count_callback' => function() {
//                    return; // Important
//                }
            )
        );

    }

    /**
     * Add the plugin's rules themselves.
     * @var $rules array
     * @return array
     */
    public function insert_rewrite_rules(array $rules) {
        return array(
            'donor-account/?$' => 'index.php?post_type='.Leyka_Donation_Management::$post_type.'&leyka-screen=account',
            'donor-account/login/?$' => 'index.php?post_type='.Leyka_Donation_Management::$post_type.'&leyka-screen=login',
            'donor-account/reset-password/?$' => 'index.php?post_type='.Leyka_Donation_Management::$post_type.'&leyka-screen=reset-password',
            'donor-account/cancel-subscription/?$' => 'index.php?post_type='.Leyka_Donation_Management::$post_type.'&leyka-screen=cancel-subscription',
            'campaign/([^/]+)/donations/?$' => 'index.php?post_type='.Leyka_Donation_Management::$post_type.'&leyka_campaign_filter=$matches[1]',
            'campaign/([^/]+)/donations/page/([1-9]{1,})/?$' =>
                'index.php?post_type='.Leyka_Donation_Management::$post_type.'&leyka_campaign_filter=$matches[1]&paged=$matches[2]',
        ) + $rules; // The rules order is important
    }

    /**
     * Add the special query var to indicate a donations archive filtering by particular campaign.
     * @var $vars array
     * @return array
     */
    public function insert_rewrite_query_vars(array $vars) {

        array_push($vars, 'leyka_campaign_filter', 'leyka-screen');
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

                    $referer = explode('#', $referer);
                    $referer = reset($referer);

                }

                wp_redirect($referer.'#leyka-submit-errors');

            } else {

                header('HTTP/1.1 200 OK');

                require_once(LEYKA_PLUGIN_DIR.'templates/service/leyka-gateway-redirect-page.php');
                exit();

            }

        }

    }

    public function _do_payment_form_submission() {

        $this->clear_session_errors(); // Clear all previous submits errors, if there are some

        $form_errors = Leyka_Payment_Form::is_form_fields_valid();

        if(is_array($form_errors) && count($form_errors) > 0) {

            foreach($form_errors as $error) { /** @var WP_Error $error */
                $this->add_payment_form_error($error);
            }

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

        $pm = leyka_pf_get_payment_method_value();

        do_action(
            'leyka_payment_form_submission-'.$pm['gateway_id'],
            $pm['gateway_id'], $pm['payment_method_id'], $donation_id, $_POST
        );

        $this->_submitted_donation_id = $donation_id;

        $this->_payment_vars = apply_filters(
            'leyka_submission_form_data-'.$pm['gateway_id'],
            $this->_payment_vars, $pm['payment_method_id'], $donation_id
        );

        $this->_payment_url = apply_filters(
            'leyka_submission_redirect_url-'.$pm['gateway_id'],
            $this->_payment_url, $pm['payment_method_id']
        );

        $this->_submission_redirect_type = apply_filters(
            'leyka_submission_redirect_type-'.$pm['gateway_id'],
            'auto', $pm['payment_method_id'], $donation_id
        );

    }

    /** Save the basic donation data and return new donation ID, so gateway can add it's specific data to the logs. */
    public function log_submission() {

        if(empty($_POST['leyka_campaign_id']) || absint($_POST['leyka_campaign_id']) <= 0) {
            return false;
        }

        $campaign = new Leyka_Campaign(absint($_POST['leyka_campaign_id']));
        $pm_data = leyka_pf_get_payment_method_value();

        $params = array('purpose_text' => $campaign->payment_title, 'gateway_id' => $pm_data['gateway_id'],);
        if( // For the direct GA integration:
            leyka_options()->opt('use_gtm_ua_integration') === 'enchanced_ua_only'
            && leyka_options()->opt('gtm_ua_tracking_id')
            && in_array('purchase', leyka_options()->opt('gtm_ua_enchanced_events'))
        ) {

            $ga_client_id = leyka_gua_get_client_id();
            if(stristr($ga_client_id, '.')) { // A real GA client ID found, save it
                $params['ga_client_id'] = $ga_client_id;
            }

        }

        $donation_id = Leyka_Donation::add(apply_filters('leyka_new_donation_data', $params));

        if( !is_wp_error($donation_id) ) {

            $campaign->increase_submits_counter();

            do_action('leyka_log_donation', $pm_data['gateway_id'], $pm_data['payment_method_id'], $donation_id);
            do_action('leyka_log_donation-'.$pm_data['gateway_id'], $donation_id);

        }

        return $donation_id;

    }

    /** @todo Move the method to the special class for the emails management / logging */
    public function handle_non_init_recurring_donor_registration($donor_user_id, Leyka_Donation $donation) {

        // This handler is only for non-init recurring donations:
        if(
            !leyka()->opt('donor_accounts_available')
            || $donation->type !== 'rebill'
            || !$donation->init_recurring_donation_id
            || ($donation->init_recurring_donation_id && $donation->init_recurring_donation_id == $donation->id)
        ) {
            return false;
        }

        $campaign = new Leyka_Campaign($donation->campaign_id);
        $email_placeholders = array(
            '#SITE_NAME#',
            '#SITE_EMAIL#',
            '#ORG_NAME#',
            '#DONATION_ID#',
            '#DONATION_TYPE#',
            '#DONOR_NAME#',
            '#DONOR_EMAIL#',
            '#PAYMENT_METHOD_NAME#',
            '#CAMPAIGN_NAME#',
            '#CAMPAIGN_URL#',
            '#PURPOSE#',
            '#CAMPAIGN_TARGET#',
            '#SUM#',
            '#DATE#',
            '#RECURRING_SUBSCRIPTION_CANCELLING_LINK#',
            '#DONOR_ACCOUNT_LOGIN_LINK#',
        );
        $email_placeholder_values = array(
            get_bloginfo('name'),
            get_bloginfo('admin_email'),
            leyka()->opt('org_full_name'),
            $donation->id,
            leyka_get_payment_type_label($donation->type),
            $donation->donor_name ? $donation->donor_name : __('dear donor', 'leyka'),
            $donation->donor_email ? $donation->donor_email : __('unknown email', 'leyka'),
            $donation->payment_method_label,
            $campaign->title,
            $campaign->url,
            $campaign->payment_title,
            $campaign->target,
            $donation->amount.' '.$donation->currency_label,
            $donation->date,
            apply_filters(
                'leyka_'.$donation->gateway_id.'_recurring_subscription_cancelling_link',
                sprintf(
                    __('<a href="mailto:%s">write us a letter about it</a>', 'leyka'),
                    leyka_get_website_tech_support_email()
                ),
                $donation
            ),
        );

        // Donor account login link:
        $donor_account_login_text = '';

        if($donation->donor_account_error) { // Donor account wasn't created due to some error
            $donor_account_login_text = sprintf(__('To control your recurring subscriptions please contact the <a href="mailto:%s">website administration</a>.', 'leyka'), leyka_get_website_tech_support_email());
        } else if($donation->donor_account_id) {

            try {
                $donor = new Leyka_Donor($donation->donor_account_id);
            } catch(Exception $e) {
                $donor = false; // Don't send an email
            }

            $donor_account_login_text = $donor && $donor->account_activation_code ?
                sprintf(__('You may manage your donations in your <a href="%s" target="_blank">personal account</a>.', 'leyka'), home_url('/donor-account/login/?activate='.$donor->account_activation_code)) :
                sprintf(__('You may manage your donations in your <a href="%s" target="_blank">personal account</a>.', 'leyka'), home_url('/donor-account/login/?u='.$donation->donor_account_id));

        }

        $email_placeholder_values[] = apply_filters(
            'leyka_email_donor_acccount_link',
            $donor_account_login_text,
            $donation,
            $campaign
        );

        $email_to = $donation->donor_email;
        $title = apply_filters(
            'leyka_email_non_init_recurring_donor_registration_title',
            leyka()->opt('non_init_recurring_donor_registration_emails_title'),
            $donation,
            $campaign
        );
        $text = wpautop(str_replace(
            $email_placeholders,
            $email_placeholder_values,
            apply_filters(
                'leyka_email_non_init_recurring_donor_registration_text',
                leyka_options()->opt('non_init_recurring_donor_registration_emails_text'),
                $donation,
                $campaign
            )
        ));

        add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        $res = wp_mail(
            $email_to,
            $title,
            $text,
            array('From: '
                .apply_filters(
                    'leyka_email_from_name',
                    leyka_options()->opt_safe('email_from_name'),
                    $donation,
                    $campaign
                ).' <'.leyka_options()->opt_safe('email_from').'>',
            )
        );

        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        return $res;

    }

    /**
     * @todo Move the method to the special class for the emails management / logging
     *
     * @param $donor_account_error WP_Error
     * @param $donation int|WP_Post|Leyka_Donation
     */
    public function handle_donor_account_creation_error(WP_Error $donor_account_error, $donation) {

        $donation = leyka_get_validated_donation($donation);
        $donation->donor_account = $donor_account_error;

        // Notify website tech. support:
        $email_to = leyka_get_website_tech_support_email();
        if( !$email_to ) {
            return;
        }

        add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        wp_mail(
            $email_to,
            apply_filters(
                'leyka_donor_account_error_email_title',
                __("Warning: donor account wasn't created", 'leyka'),
                $donor_account_error,
                $donation
            ),
            wpautop(apply_filters(
                'leyka_donor_account_error_email_text',
                sprintf(
                    __("Hello,\n\n this is a technical notification from the <a href='%s'>%s</a> website.\n\nJust now Leyka plugin encountered an error while creating donor's personal account on initial recurring donation. The details are below.\n\nDonation ID: %s\nDonor: %s\nDonor's email: %s\nAccount creation error: %s\n\nThe <a href='%s'>recurring donation itself</a> was created successfully.", 'leyka'),
                    home_url(),
                    get_bloginfo('name'),
                    $donation->id,
                    $donation->donor_name,
                    $donation->donor_email,
                    $donor_account_error->get_error_message(),
                    admin_url('post.php?post='.$donation->id.'&action=edit')
                ),
                $donor_account_error,
                $donation
            )),
            array(
                'From: '.apply_filters(
                    'leyka_email_from_name',
                    leyka_options()->opt_safe('email_from_name'),
                    $donor_account_error,
                    $donation
                ).' <'.leyka_options()->opt_safe('email_from').'>',
            )
        );

        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

    }

    /** Templates manipulations. */
    /** @todo Move all templates related methods to the sep. class ("Leyka_Form_Templates", leyka_templates()). */

    /**
     * @param $params array.
     * @return array An array of donations forms templates info.
     */
    public function get_templates(array $params = array()) {

        $params = array_merge(array(
            'is_service' => false,
            'include_deprecated' => leyka_options()->opt('allow_deprecated_form_templates')
        ), $params);

        if( !$this->_templates ) {
            $this->_templates = array();
        }

        if( !!$params['is_service'] ) {
            $this->_templates = glob(LEYKA_PLUGIN_DIR.'templates/service/leyka-template-*.php');
        } else {

            $custom_templates = glob(STYLESHEETPATH.'/leyka-template-*.php');
            $custom_templates = $custom_templates ? $custom_templates : array();

            $this->_templates = apply_filters(
                'leyka_templates_list',
                array_merge($custom_templates, glob(LEYKA_PLUGIN_DIR.'templates/leyka-template-*.php'))
            );

        }

        if( !$this->_templates ) {
            $this->_templates = array();
        }

        $this->_templates = array_map(array($this, '_get_template_data'), $this->_templates);

        if( !$params['include_deprecated'] ) {
            foreach($this->_templates as $index => $template_data) {
                if( !empty($template_data['deprecated']) ) {
                    unset($this->_templates[$index]);
                }
            }
        }

        // Templates ordering:
        $ordered_templates = array();

        foreach($this->_templates_order as $ordered_template) {
            foreach($this->_templates as $template_data) {
                if($template_data['id'] == $ordered_template) {
                    $ordered_templates[ $template_data['id'] ] = $template_data;
                }
            }
        }

        foreach($this->_templates as $template_data) {
            if( !in_array($template_data['id'], $this->_templates_order) ) {
                $ordered_templates[ $template_data['id'] ] = $template_data;
            }
        }
        $this->_templates = $ordered_templates;

        return (array)$this->_templates;

    }

    protected function _get_template_data($file) {

        $data = get_file_data($file, array(
            'name' => 'Leyka Template',
            'description' => 'Description',
            'debug_only' => 'Debug only',
            'deprecated' => 'Deprecated',
        ));

        $data['file'] = $file;
        $data['basename'] = basename($file);
        $data['id'] = str_replace(array('leyka-template-', '.php'), '', $data['basename']);

        if(empty($data['name'])) {
            $data['name'] = $data['basename'];
        }

        return $data;

    }

    /** @deprecated From v3.5 use only $this->get_template($template_id). */
    public function get_template_data($file) {
        return $this->_get_template_data($file);
    }

    public function get_template($template_id, $is_service = false) {

        $templates = $this->get_templates(array('is_service' => !!$is_service, 'include_deprecated' => true,));
        if( !$templates ) {
            return false;
        }

        foreach($templates as $template) {

            $current_template_id = str_replace(array('leyka-template-', '.php'), '', $template['basename']);

            if($current_template_id == $template_id) {
                return $template;
            }

        }

        return false;

    }

    public function template_is_deprecated($template_id) {

        $template_main_file_addr = LEYKA_PLUGIN_DIR."templates/leyka-template-$template_id.php";
        if($template_id === 'default' || !file_exists($template_main_file_addr)) {
            /** @todo Throw some Ex? */ return false;
        }

        $template_data = get_file_data($template_main_file_addr, array(
            'name' => 'Leyka Template',
            'description' => 'Description',
            'debug_only' => 'Debug only',
            'deprecated' => 'Deprecated',
        ));

        if( !$template_data ) {
            /** @todo Throw some Ex? */ return false;
        }

        return !empty($template_data['deprecated']);

    }

}

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
__('Star', 'leyka');
__('A modern and lightweight form template', 'leyka');