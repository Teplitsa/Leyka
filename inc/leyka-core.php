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
    protected $_templates_order = ['need-help', 'star', 'revo', 'neo', 'toggles', 'radios',];

    /**
     * Gateways list.
     * @var array
     */
    protected $_gateways = [];

    /** @var array Of WP_Error instances. */
    protected $_form_errors = [];

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
    protected $_payment_vars = [];

    /**
     * Template list.
     * @var array
     */
    protected $_templates = null;

    /** * @var array */
    protected $_extensions = [];

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

        add_filter('leyka_option_value', function($value, $option_id){ // If LEYKA_DEBUG const is set, use its value
            return $option_id === 'plugin_debug_mode' ?
                (defined('LEYKA_DEBUG') && LEYKA_DEBUG !== 'inherit' ? !!LEYKA_DEBUG : $value) : $value;
        }, 10, 2);

        // By default, we'll assume some errors in the payment form, so redirect will get us back to it:
        $this->_payment_url = wp_get_referer();

        // Add GTM & UA e-commerce dataLayer if needed:
        if( in_array(leyka_options()->opt('use_gtm_ua_integration'), ['simple', 'enchanced', 'enchanced_ua_only']) ) {
            add_action('wp_head', [$this, 'add_gtm_data_layer_ua_'.leyka_options()->opt('use_gtm_ua_integration')], -1000);
        }

        $this->load_public_cssjs();

        add_action('init', [$this, 'register_post_types'], 1);
        add_action('init', [$this, 'register_user_capabilities'], 1);
        add_action('init', [$this, 'register_taxonomies'], 1);

        // Leyka rewrite rules - Donor's account pages endpoint:
        add_action('init', function(){

            add_rewrite_endpoint('donor-account', EP_ROOT, 'leyka-screen');
            add_rewrite_endpoint('donor_account', EP_ROOT, 'leyka-screen');

        });

        add_filter('request', function($query_vars){

            if(isset($query_vars['leyka-screen']) && empty($query_vars['leyka-screen'])) {
                $query_vars['leyka-screen'] = 'account';
            }

            return $query_vars;

        });

        // Leyka rewrite rules - Campaigns & Donations archives:
        add_filter('rewrite_rules_array', [$this, 'insert_rewrite_rules']);
        add_filter('query_vars', [$this, 'insert_rewrite_query_vars']);

        /** @todo Try to make all service URLs via "leyka/service" endpoint */
        add_action('parse_request', [$this, 'parse_request']); // Service URLs handlers

        if(get_option('leyka_plugin_stats_option_needs_sync')) {

            function leyka_sync_stats_option() {

                $stats_option_synch_result = leyka_sync_plugin_stats_option();

                if(is_wp_error($stats_option_synch_result)) {
                    return $stats_option_synch_result;
                }

                return delete_option('leyka_plugin_stats_option_needs_sync')
                    && update_option('leyka_plugin_stats_option_sync_done', time());

            }
            add_action('admin_init', 'leyka_sync_stats_option');

        }

        // Change the new recurring Donation purpose if it's Campaign is closed:
        add_action('leyka_donation_funded_status_changed', function($donation_id, $old_status, $new_status){

            $donation = Leyka_Donations::get_instance()->get($donation_id);
            if($donation->type !== 'rebill') {
                return;
            }

            $campaign = new Leyka_Campaign($donation->campaign_id);
            if($campaign->is_finished) {

                $donation->payment_title = apply_filters(
                    'leyka_finished_campaign_new_recurring_donation_purpose',
                    __('Charity donation', 'leyka'),
                    $donation,
                    $campaign
                );

            }

        }, 10, 3);

        add_action('admin_bar_menu', [$this, 'add_toolbar_menu'], 999);

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

            add_action('leyka_donor_account_created', [$this, 'handle_non_init_recurring_donor_registration'], 10, 2);
            add_action('leyka_donor_account_not_created', [$this, 'handle_donor_account_creation_error'], 10, 2);

        }

        // Donors management & Donors' accounts fields logical link:
        add_action('leyka_set_donor_accounts_available_option_value', function($option_value){
            if($option_value) {
                update_option('leyka_donor_management_available', true);
            }
        });

        if(is_admin()) { // Admin area only

            require_once(LEYKA_PLUGIN_DIR.'inc/settings/allocators/leyka-class-options-allocator.php');
            require_once(LEYKA_PLUGIN_DIR.'inc/leyka-settings-rendering-utils.php');
            require_once(LEYKA_PLUGIN_DIR.'inc/leyka-admin.php');
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

                        $campaign = get_posts([
                            'post_type' => Leyka_Campaign_Management::$post_type,
                            'name' => get_query_var('leyka_campaign_filter'),
                            'posts_per_page' => 1,
                        ]);
                        if( !$campaign ) {
                            return;
                        }
                        $campaign = reset($campaign);

                        $query->set('meta_query', [['key' => 'leyka_campaign_id', 'value' => $campaign->ID,],]);

                    }

                }

            }
            add_action('pre_get_posts', 'leyka_get_posts', 1);

            function leyka_success_page_widget_template($content) {

                if( !is_page(leyka_options()->opt('success_page')) ) {
                    return $content;
                }

                $donation_id = leyka_remembered_data('donation_id');

                if( !$donation_id ) {
                    return $content;
                }

                $donation = Leyka_Donations::get_instance()->get($donation_id);
                $campaign_id = $donation ? $donation->campaign_id : null;
                $campaign = new Leyka_Campaign($campaign_id);

                if( !$campaign->id ) {
                    return $content;
                }

                $form_template = $campaign->template;

                if( !$form_template ) {
                    $form_template = leyka_remembered_data('template_id');
                }

                if(
                    is_main_query()
                    && leyka_options()->opt_template('show_success_widget_on_success', $form_template ? : 'default')
                ) {
                    
                    $form_template_suffix = $form_template === 'star' || $form_template === 'need-help' ? '-star' : '';

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
                    is_main_query()
                    && leyka_options()->opt_template('show_failure_widget_on_failure')
                    && is_page(leyka_options()->opt('failure_page'))
                ) {

                    ob_start();
                    include(LEYKA_PLUGIN_DIR.'templates/service/leyka-template-failure-widget.php');
                    $content = ob_get_clean();

                }

                return $content;

            }
            add_filter('the_content', 'leyka_failure_page_widget_template', 1);

            add_action('wp_head', 'leyka_inline_scripts');
            function leyka_inline_scripts(){

                $colors = ['#1db318', '#1aa316', '#acebaa']; // Leyka green ?>

                <script>
                    document.documentElement.classList.add("leyka-js");
                </script>
                <style>
                    :root {
                        --color-main: 		<?php echo esc_attr( $colors[0] );?>;
                        --color-main-dark: 	<?php echo esc_attr( $colors[1] );?>;
                        --color-main-light: <?php echo esc_attr( $colors[2] );?>;
                    }
                </style>

                <?php
            }

            function leyka_template_init_include(WP_Query $query) { // Include template init script

                if( // Can't use $query->is_singular(Leyka_Campaign_Management::$post_type) here,
                    // because there is no $query->get_queried_object_id() value at this point
                    $query->is_main_query()
                    && $query->is_singular()
                    && $query->get('post_type') === Leyka_Campaign_Management::$post_type
                    && $query->get('name')
                ) {

                    $campaign_post = get_posts([
                        'post_type' => Leyka_Campaign_Management::$post_type,
                        'name' => $query->get('name'),
                        'posts_per_page' => 1,
                    ]);
                    $campaign_post = $campaign_post ? $campaign_post[0] : [];

                    if( !$campaign_post ) {
                        return;
                    }

                    $campaign = new Leyka_Campaign($campaign_post);
                    $template = leyka_get_current_template_data($campaign);

                    if($template && isset($template['file'])) {

                        $init_file = LEYKA_PLUGIN_DIR.'templates/leyka-' . $template['id'] . '/leyka-' . $template['id'] . '-init';
                        if( file_exists( $init_file  . '.php' ) ) {
                            require_once( $init_file . '.php' );
                        }

                    }

                }

            }
            add_action('pre_get_posts', 'leyka_template_init_include'); // add_action('wp_head', 'leyka_template_init_include');

            add_filter('template_include', function($template){

                // Embed campaign URL handler:
                /** @todo Check if it's still needed feature, or it may be removed. */
                if(is_main_query() && is_singular(Leyka_Campaign_Management::$post_type) && !empty($_GET['embed_object'])) {

                    $new_template = leyka_get_current_template_data(false, 'embed_'.$_GET['embed_object'], true);
                    if($new_template && !empty($new_template['file'])) {
                        $template = $new_template['file'];
                    }

                } else {

                    // Donor's account templates:
                    $leyka_screen = get_query_var('leyka-screen');
                    if( !$leyka_screen ) {
                        return $template;
                    }

                    switch($leyka_screen) {
                        case 'account':
                            $template = LEYKA_PLUGIN_DIR.'templates/account/account.php';
                            break;
                        case 'login':
                            $template = LEYKA_PLUGIN_DIR.'templates/account/login.php';
                            break;
                        case 'reset-password':
                            $template = LEYKA_PLUGIN_DIR.'templates/account/reset-password.php';
                            break;
                        case 'cancel-subscription':
                            $template = LEYKA_PLUGIN_DIR.'templates/account/cancel-subscription.php';
                            break;
                        default:
                    }

                }

                return $template;

            }, 100);

            add_action('template_redirect', [$this, 'gateway_redirect_page'], 1, 1);

        }

        $this->apply_content_formatting(); // Internal formatting filters

        // Plugin data placeholders in the Terms of service / Terms of PD pages content:
        add_filter('the_content', [$this, 'apply_terms_pages_content_placeholders'], 100);

        if(class_exists('Leyka_Options_Controller')) {
            add_action('leyka_do_procedure', [$this, '_do_procedure'], 10, 2);
        }

        add_action('wp_loaded', [$this, 'refresh_donors_data'], 100);

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
            case 'storage_mode':
            case 'storage_type':
            case 'donations_storage_type':
                $storage_type = get_option('leyka_donations_storage_type');
                return in_array($storage_type, ['sep', 'post']) ? $storage_type : 'post';
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

    /** Fired when the plugin is manually activated. NOT FIRED ON UPDATES. */
    public static function activate() {

        $leyka_last_ver = get_option('leyka_last_ver');

        if( !$leyka_last_ver || version_compare($leyka_last_ver, '3.18', '<=') ) {
            leyka_create_separate_donations_db_tables(); // Create plugin-specific DB tables if needed
        }

    }

    public static function deactivate() {
        delete_option('leyka_permalinks_flushed');
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
        }

        if( !in_array($activation_status, ['active', 'inactive', 'activating']) ) {
            return [];
        }

        $extensions = [];
        foreach($this->_extensions as $extension) { /** @var $extension Leyka_Extension */
            if($extension->get_activation_status() === $activation_status) {
                $extensions[] = $extension;
            }
        }

        return $extensions;

    }

    /**
     * @param $extension_id string
     *
     * @throws Exception
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

                $donation = Leyka_Donations::get_instance()->get($donation_id);
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

        if( !$donation_id ) {
            return;
        }

        $donation = Leyka_Donations::get_instance()->get($donation_id);
        $campaign_id = $donation ? $donation->campaign_id : null;
        $campaign = new Leyka_Campaign($campaign_id);

        if( !$campaign->id ) {
            return;
        }

        $donation_amount_total = round((float)$donation->amount_total, 2);?>

        <script>
            window.dataLayer = window.dataLayer || [];

            dataLayer.push({
                'donorEmail': '<?php echo esc_attr(sanitize_email( $donation->donor_email ));?>',
                'transactionId': '<?php echo esc_attr( (int)$donation_id );?>',
                'transactionAffiliation': '<?php echo esc_attr( get_bloginfo('name') );?>',
                'transactionTotal': <?php echo esc_attr( $donation_amount_total );?>,
                'transactionTax': 0,
                'transactionShipping': 0,
                'transactionProducts': [{
                    'sku': '<?php echo esc_attr( (int)$campaign_id );?>',
                    'name': '<?php echo esc_attr($campaign->title);?>',
                    'category': '<?php echo esc_attr($donation->type_label);?>',
                    'price': <?php echo esc_attr( $donation_amount_total );?>,
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

                dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
                dataLayer.push({
                    event: "view_item",
                    ecommerce: {
                        currency: "RUB",
                        value: 0,
                        items: [{
                            item_name: '<?php echo esc_attr( $campaign->title ); ?>',
                            item_id: '<?php echo esc_attr( $campaign->id ); ?>',
                            price: 0,
                            item_brand: "donate",
                            item_category: '<?php esc_html_e('Donations', 'leyka');?>', 
                            quantity: 1
                        }]
                    }
                }); 
                console.log("action: view_item")
            </script>

        <?php } else if(is_page(leyka()->opt('success_page'))) {

            // Success page display - use "purchase" e-commerce measurement:

            $donation_id = leyka_remembered_data('donation_id');

            if( !$donation_id ) {
                return;
            }

            $donation = Leyka_Donations::get_instance()->get($donation_id);
            $campaign_id = $donation ? $donation->campaign_id : null;
            $campaign = new Leyka_Campaign($campaign_id);

            if( !$campaign->id ) {
                return;
            }

            $donation_amount_total = round((float)$donation->amount_total, 2);?>

        <script>
            window.dataLayer = window.dataLayer || [];

            dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            dataLayer.push({
                event: "purchase",
                ecommerce: {
                    transaction_id: '<?php echo esc_attr( $donation->id ); ?>',
                    affiliation: '<?php echo esc_attr( $campaign->title ); ?>',
                    value: '<?php echo esc_attr( $donation_amount_total ); ?>',
                    tax: "0",
                    shipping: "0",
                    currency: "RUS", 
                    items: [{
                        item_name: '<?php echo esc_attr( $campaign->title ); ?>',
                        item_id: '<?php echo esc_attr( $donation->id ); ?>',
                        price: '<?php echo esc_attr( $donation_amount_total ); ?>',
                        item_brand: '<?php echo esc_html( get_bloginfo('name') ); ?>',
                        item_category: '<?php echo esc_attr( $donation->type_label ); ?>', 
                        quantity: 1
                    }]
                }
            });

            console.log("action: purchase")
             
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
                    ->addProduct([ // Campaign params
                        'name' => $campaign->payment_title,
                        'brand' => get_bloginfo('name'), // Mb, it won't work with it
                    ])
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

        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'localize_scripts']);

    }

    public function parse_request() {

        if(stristr($_SERVER['REQUEST_URI'], 'leyka/service') !== false) { // Leyka service URL

            $request = explode('leyka/service', $_SERVER['REQUEST_URI']);
            $request = explode('/', trim($request[1], '/'));

            if($request[0] === 'do_recurring') { // Active recurring shortcut URL
                do_action('leyka_do_procedure', 'active-recurring');
            } else if($request[0] === 'cancel_recurring' && !empty($request[1]) && !empty($request[2])) {

                $donation = Leyka_Donations::get_instance()->get_donation($request[1]);
                $hash = md5($donation->id.'_'.$donation->init_recurring_donation_id.'_leyka_cancel_recurring_subscription');

                if($donation && $hash === $request[2]) {
                    do_action("leyka_{$donation->gateway_id}_cancel_recurring_subscription_by_link", $donation);
                }

            } else if($request[0] === 'update_recurring_subscriptions') {
                do_action('leyka_do_procedure', 'update-recurring-subscriptions');
            } else if($request[0] === 'refresh_currencies_rates') {
                do_action('leyka_do_procedure', 'refresh-currencies-rates');
            } else if($request[0] === 'do_campaigns_targets_reaching_mailout') {

                // Campaigns target reached mailout shortcut URL:
                do_action(
                    'leyka_do_procedure',
                    'campaigns-targets-reaching-mailout',
                    empty($request[1]) ? [] : array((int)$request[1])
                );

            } else if(isset($request[0], $request[1]) && mb_stristr($request[0], 'procedure') !== false) {

                // Common procedure call URL,
                // like some-website.org/leyka/service/procedure/{procedure_name}[/{param_1}/{param_2}/...]
                // E.g.:
                // * some-website.org/leyka/service/procedure/active-recurring
                // * some-website.org/leyka/service/procedure/campaigns-targets-reaching-mailout/123

                do_action('leyka_do_procedure', $request[1], array_slice($request, 2));

            } else if($request[0] === 'get_usage_stats') {

                echo '';

                /* Statistics collection disabled
                // The file bin/sodium-compat.phar was removed in version 3.31.4
                // Plugin check: Phar files are not permitted.
                require_once LEYKA_PLUGIN_DIR.'bin/sodium-compat.phar';

                if( !$this->_outer_request_allowed() ) {
                    exit;
                }

                echo empty($_GET['tst']) ?
                    \Sodium\crypto_box_seal(
                        wp_json_encode($this->_get_usage_stats($_REQUEST)),
                        \Sodium\hex2bin(get_option('leyka_stats_sipher_public_key'))
                    ) :
                    '<pre>'.print_r($this->_get_usage_stats($_REQUEST), 1).'</pre>';
                */
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

        $wp_admin_bar->add_node([
            'id' => 'leyka-toolbar-menu',
            'title' => __('Leyka', 'leyka'),
            'href' => admin_url('admin.php?page=leyka'),
        ]);

        $wp_admin_bar->add_node([
            'id'  => 'leyka-toolbar-desktop',
            'title' => __('Desktop', 'leyka'),
            'parent' => 'leyka-toolbar-menu',
            'href' => admin_url('admin.php?page=leyka'),
        ]);
        $wp_admin_bar->add_node([
            'id' => 'leyka-toolbar-donations',
            'title' => __('Donations', 'leyka'),
            'parent' => 'leyka-toolbar-menu',
            'href' => admin_url('admin.php?page=leyka_donations'),
        ]);
        $wp_admin_bar->add_node([
            'id' => 'leyka-toolbar-campaigns',
            'title' => __('Campaigns', 'leyka'),
            'parent' => 'leyka-toolbar-menu',
            'href' => admin_url('edit.php?post_type='.Leyka_Campaign_Management::$post_type),
        ]);

        if(current_user_can('leyka_manage_options')) {
            $wp_admin_bar->add_node([
                'id' => 'leyka-toolbar-settings',
                'title' => __('Settings', 'leyka'),
                'parent' => 'leyka-toolbar-menu',
                'href' => admin_url('admin.php?page=leyka_settings'),
            ]);
        }

    }

    /** @todo Make it a procedure */
    public function _do_currency_rates_refresh() {
        foreach(leyka_get_actual_currency_rates() as $currency => $rate) {
            update_option('leyka_currency_rur2'.mb_strtolower($currency), $rate);
        }
    }

    public function _do_procedure($procedure_id, $params = []) {

        $procedure_id = mb_stristr($procedure_id, 'leyka-') !== false ? str_replace('leyka-', '', $procedure_id) : $procedure_id;

        do_action('leyka_before_procedure', $procedure_id, $params);

        // Via URL: some-website.org/leyka/service/procedure/campaigns-targets-reaching-mailout/123
        // Via PHP CLI: php [-f] /absolute/address/to/leyka/procedures/leyka-campaigns-target-reaching-mailout.php -- 123

        $procedure_script = apply_filters(
            'leyka_procedure_address',
            LEYKA_PLUGIN_DIR.'procedures/leyka-'.$procedure_id.'.php',
            $procedure_id,
            $params
        );

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

    protected function _get_usage_stats(array $params = []) {

        $donations_params = [
//            'post_status' => 'any',
//            'meta_query' => [
//                'relation' => 'AND',
//                ['key' => 'leyka_payment_type', 'value' => 'correction', 'compare' => '!='],
//            ],
            'payment_type' => ['single', 'rebill',],
            'nopaging' => true,
        ];
        if( !empty($params['timestamp_from']) && (int)$params['timestamp_from'] > 0 ) { // 'date_from' must be a timestamp

//            $query_params['date_query']['after'] = gmdate('Y-m-d H:i:s', (int)$params['timestamp_from']);
//            $query_params['date_query']['inclusive'] = true;
            $donations_params['date_from'] = gmdate('Y-m-d H:i:s', (int)$params['timestamp_from']);

            if( !empty($params['period']) ) { // Must be strtotime()-compatible string w/o sign (1 hour, 2 weeks, 3 months, ...)

                $params['period'] = str_replace(['+', '-'], '', $params['period']);

                $donations_params['date_to'] = gmdate(
                    'Y-m-d H:i:s', strtotime($donations_params['date_from'].' +'.$params['period'])
                );

            }

        }

//        if( !empty($donations_params['date_query']) ) {
//            $donations_params['date_query'] = [$donations_params['date_query']];
//        }

        $stats = ['donations' => [],] + leyka_get_env_and_options();

        foreach(Leyka_Donations::get_instance()->get($donations_params) as $donation) {

            $donations_by_status = [];
            foreach(leyka_get_donation_status_list() as $status => $label) {
                $donations_by_status[$status] = 0;
            }

            if(empty($stats['donations'][$donation->gateway][$donation->pm])) {
                $stats['donations'][$donation->gateway][$donation->pm] = [
                    'main_currency' => 'RUB',
                    'amount_collected' => 0.0, // In main currency
                    'donations_count' => 0,
                    'donations_by_status_count' => $donations_by_status,
                ];
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
        if(empty($_SESSION['Leyka_Donations_Errors']) || $anew) {
            $_SESSION['Leyka_Donations_Errors'] = $this->get_payment_form_errors();
        } else {
            $_SESSION['Leyka_Donations_Errors'] = array_merge($_SESSION['Leyka_Donations_Errors'], $this->get_payment_form_errors());
        }
    }

    /** @return bool */
    public function has_session_errors() {
        return !empty($_SESSION['Leyka_Donations_Errors']) && count($_SESSION['Leyka_Donations_Errors']);
    }

    /** @return array */
    public function get_session_errors() {
        return empty($_SESSION['Leyka_Donations_Errors']) ? [] : $_SESSION['Leyka_Donations_Errors'];
    }

    public function clear_session_errors() {
        $_SESSION['Leyka_Donations_Errors'] = [];
    }

    public static function get_recurring_subscription_statuses() {
        return apply_filters('leyka_recurring_subscription_statuses', [
            'active' => _x('Active', '"Active" recurring subscription status', 'leyka'),
            'problematic' => _x('Problematic', '"Problematic" recurring subscription status', 'leyka'),
            'non-active' => _x('Non-active', '"Non-active" recurring subscription status', 'leyka')
        ]);
    }

    public static function get_donation_types() {
        return apply_filters('leyka_donation_types', [
            'single' => _x('Single', '"Single" donation type name', 'leyka'),
            'recurring' => _x('Recurring', '"Recurring" donation type name', 'leyka'),
            'correction' => _x('Correction', '"Correction" donation type name', 'leyka'),
        ]);
    }

    public static function get_donation_types_descriptions() {
        return apply_filters('leyka_donation_types_descriptions', [
            'single' => _x("A one-time donation.", '«Single» donation type description', 'leyka'),
            'recurring' => _x('A monthly recurring donation.', '«Recurring» donation type description', 'leyka'),
            'correction' => _x('A donation manually added by the website administration.', '«Refunded» donation status description', 'leyka'),
        ]);
    }

    /**
     * Retrieve all available donation status names.
     *
     * @param $with_hidden boolean
     * @return array of status_id => status label pairs
     */
    public static function get_donation_statuses($with_hidden = true) {

        $with_hidden = !!$with_hidden;

        $statuses = apply_filters('leyka_donation_statuses', [
            'submitted' => _x('Submitted', '«Submitted» donation status', 'leyka'),
            'funded'    => _x('Funded', '«Completed» donation status', 'leyka'),
            'refunded'  => _x('Refunded', '«Refunded» donation status', 'leyka'),
            'failed'    => _x('Failed', '«Failed» donation status', 'leyka'),
            'trash'     => _x('Trash', '«Deleted» donation status', 'leyka'),
        ], $with_hidden);

        if( !$with_hidden && isset($statuses['trash']) ) {
            unset($statuses['trash']);
        }

        return $statuses;

    }

    /**
     * Retrieve all available donation status descriptions.
     *
     * @return array of status_id => status_description pairs
     */
    public static function get_donation_statuses_descriptions() {
        return apply_filters('leyka_donation_statuses_descriptions', [
            'submitted' => _x("Donation attempt was made, but the payment itself wasn't sent.\n\nOr, maybe, the payment was completed, but Leyka wasn't notified of it. If that is the case, you should check if your payment gateway callbacks are set up correctly.", '«Submitted» donation status description', 'leyka'),
            'funded' => _x('Donation was finished, the funds were received by your account.', '«Completed» donation status description', 'leyka'),
            'refunded' => _x('Donation funds were returned to the donor.', '«Refunded» donation status description', 'leyka'),
            'failed' => _x("Donation payment was incomplete due to an error. The funds weren't received.", '«Failed» donation status description', 'leyka'),
            'trash' => _x('Donation information was deleted.', '«Trash» donation status description', 'leyka'),
        ]);
    }

    /**
     * Retrieve all available donation status descriptions.
     *
     * @return array of status_id => status_description pairs
     */
    public static function get_donation_statuses_short_names() {
        return apply_filters('leyka_donation_statuses_short_names', [
            'submitted' => _x('Submitted', '«Submitted» donation status short (one word) title', 'leyka'),
            'funded' => _x('Funded', '«Funded» donation status short (one word) title', 'leyka'),
            'refunded' => _x('Refunded', '«Refunded» donation status short (one word) title', 'leyka'),
            'failed' => _x('Failed', '«Failed» donation status short (one word) title', 'leyka'),
            'trash' => _x('Trash', '«Trash» donation status short (one word) title', 'leyka'),
        ]);
    }

    /**
     * Retrieve all available donation status descriptions for Donors.
     *
     * @return array of status_id => status_description pairs
     */
    public static function get_donation_statuses_descriptions_for_donors() {
        return apply_filters('leyka_donation_statuses_descriptions_for_donors', [
            'submitted' => _x("Donation attempt was made, but the funds weren't received.\n\nMaybe, the payment wasn't completed, or the receiver's website wasn't notified of it.", '«Submitted» donation status description', 'leyka'),
            'funded' => _x('Donation was finished, the funds were properly received.', '«Completed» donation status description', 'leyka'),
            'refunded' => _x('Donation funds were returned to you.', '«Refunded» donation status description', 'leyka'),
            'failed' => _x("Donation payment was incomplete due to an error. The funds weren't received.", '«Failed» donation status description', 'leyka'),
            'trash' => _x('Donation information was deleted.', '«Trash» donation status description', 'leyka'),
        ]);
    }

    /**
     * Retreive an info about given donation status.
     *
     * @param $status_id string
     * @param $info_field string|false Either 'label' or 'description' string, or false to get all info as an array.
     * @return array|false Either an array with status info, or false if given status ID is incorrect.
     */
    public static function get_donation_status_info($status_id, $info_field = false) {

        $status_names = self::get_donation_statuses();
        $status_short_names = self::get_donation_statuses_short_names();
        $status_descriptions = self::get_donation_statuses_descriptions();
        $status_descriptions_for_donors = self::get_donation_statuses_descriptions_for_donors();

        $status_info = ['id' => $status_id];

        if( !empty($status_names[$status_id]) ) {
            $status_info['label'] = $status_names[$status_id];
        }
        if( !empty($status_short_names[$status_id]) ) {
            $status_info['short_label'] = $status_short_names[$status_id];
        }
        if( !empty($status_descriptions[$status_id]) ) {
            $status_info['description'] = $status_descriptions[$status_id];
        }
        if( !empty($status_descriptions_for_donors[$status_id]) ) {
            $status_info['description_for_donors'] = $status_descriptions_for_donors[$status_id];
        }

        if( !count($status_info) ) {
            return false;
        } else if($info_field && !empty($status_info[$info_field]) ) {
            return $status_info[$info_field];
        } else {
            return $status_info;
        }

    }

    public static function get_donor_types() {
        return apply_filters('leyka_donor_types', [
            'single' => _x('Single', "Donor's type", 'leyka'),
            'regular' => _x('Regular', "Donor's type", 'leyka'),
        ]);
    }

    /**
     * Retrieve all available campaign target states.
     *
     * @return array of state_id => state label pairs
     */
    public static function get_campaign_target_states() {
        return apply_filters('leyka_campaign_target_states', [
            'no_target'   => _x('No target', 'Campaign state when target is not set', 'leyka'),
            'is_reached'  => _x('Reached', 'Campaign state when target is reached', 'leyka'),
            'in_progress' => _x('In progress', 'Campaign state when target is not reached yet', 'leyka'),
        ]);
    }

    /**
     * @param array $params An assoc. array of possible fields:
     * * mixed 'activation_status' - If given, get only gateways with it.  Give NULL for all types altogether.
     * * mixed 'country_id' - Either string country ID ('ru', 'by'), or NULL to turn off the country filtering. Default is current country ID ('receiver_country' option value).
     *
     * @return array Of Leyka_Gateway objects.
     * @throws Exception
     */
    public function get_gateways(array $params = []) {

        $params = wp_parse_args($params, [
            'activation_status' => null,
            'country_id' => leyka_options()->opt_safe('receiver_country'),
            'orderby' => null,
            'order' => 'desc',
        ]);

        if($params['activation_status'] && !in_array($params['activation_status'], ['active', 'inactive', 'activating'])) {
            throw new Exception(sprintf(
                esc_html__('Unknown gateways activation status given: %s', 'leyka'), esc_html($params['activation_status'])
            ));
        }

        $params['country_id'] = $params['country_id'] ? trim($params['country_id']) : null;

        $gateways = [];
        foreach($this->_gateways as $gateway) { /** @var $gateway Leyka_Gateway */

            if($params['activation_status'] && $gateway->get_activation_status() !== $params['activation_status']) {
                continue;
            }

            $gateways[$gateway->id] = $gateway;

        }

        if( !empty($params['orderby']) && in_array($params['orderby'], ['activation_status',]) ) {

            $params['order'] = in_array(mb_strtolower($params['order']), ['asc', 'desc',]) ?
                mb_strtolower($params['order']) : 'desc';

            // Order by Gateway activation status, then by title:
            @usort($gateways, function(Leyka_Gateway $gateway_1, Leyka_Gateway $gateway_2){

                $activation_status_1 = $gateway_1->get_activation_status();
                $activation_status_2 = $gateway_2->get_activation_status();

                if($activation_status_1 == $activation_status_2) {
                    return strcmp($gateway_1->title, $gateway_2->title);
                }

                /** @todo $params['order'] isn't used now - can't pass the $params inside the closure. Find a way for it. */
                return $activation_status_1 < $activation_status_2 ? -1 : 1;

            });

        }

        return $gateways;

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

    public function apply_content_formatting() {

        add_filter('leyka_the_content', 'wptexturize');
        add_filter('leyka_the_content', 'convert_smilies');
        add_filter('leyka_the_content', 'convert_chars');
        add_filter('leyka_the_content', 'wpautop');

    }

    public function apply_terms_pages_content_placeholders($content) {

        if(in_the_loop() && is_main_query()) {

            if( is_page(leyka_options()->opt('terms_of_service_page')) ) {
                $content = apply_filters('leyka_terms_of_service_text', $content);
            } else if( is_page(leyka_options()->opt('pd_terms_page')) ) {
                $content = apply_filters('leyka_terms_of_pd_usage_text', $content);
            }

        }

        return $content;

    }

    /** Register and enqueue front-office styles. */
    public function enqueue_styles() {

        if(stristr($_SERVER['REQUEST_URI'], 'leyka-process-donation') !== false) { // Leyka service URL

            wp_enqueue_style(
                $this->_plugin_slug.'-redirect-styles',
                LEYKA_PLUGIN_BASE_URL.'css/gateway-redirect-page.css',
                [],
                LEYKA_VERSION
            );
            return;

        }

        $is_need_help_success_page =
            is_page(leyka_options()->opt('success_page'))
            && leyka_remembered_data('template_id') === 'need-help'
            && leyka_options()->opt_template('show_success_widget_on_success', 'need-help');

        if(leyka_modern_template_displayed('need-help') || $is_need_help_success_page) {
            wp_enqueue_style(
                $this->_plugin_slug.'-inter-font-styles',
                'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap',
                [],
                LEYKA_VERSION
            );
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
                'leyka-new-templates-styles',
                LEYKA_PLUGIN_BASE_URL.'assets/css/public.css',
                [],
                LEYKA_VERSION
            );
        }

        if( !leyka_form_is_displayed() ) {
            return;
        }

        // Enqueue the normal Leyka CSS just in case some other plugin elements exist on page:
        wp_enqueue_style(
            $this->_plugin_slug.'-plugin-styles',
            LEYKA_PLUGIN_BASE_URL.'css/public.css',
            [],
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
            $donation = $donation_id ? Leyka_Donations::get_instance()->get($donation_id) : null;
            $campaign_id = $donation ? $donation->campaign_id : null;

        }

        if($campaign_id) {
            wp_add_inline_style('leyka-new-templates-styles', get_post_meta($campaign_id, 'campaign_css', true));
        }

    }

    /** Register and enqueue front-office JS. */
    public function enqueue_scripts() {

        if( // New JS:
            !leyka_options()->opt('load_scripts_if_need')
            || leyka_modern_template_displayed()
            || leyka_success_widget_displayed()
            || leyka_failure_widget_displayed()
        ) {
            wp_enqueue_script(
                'leyka-new-templates-public',
                LEYKA_PLUGIN_BASE_URL.'assets/js/public.js',
                ['jquery',],
                LEYKA_VERSION,
                true
            );
        }

        if( !leyka_form_is_displayed() ) {
            return;
        }

        // Enqueue the normal Leyka scripts just in case some other plugin elements exist on page:
        wp_enqueue_script(
            'leyka-modal',
            LEYKA_PLUGIN_BASE_URL.'js/jquery.easyModal.min.js',
            ['jquery',],
            LEYKA_VERSION,
            true
        );

        wp_enqueue_script(
            'leyka-public',
            LEYKA_PLUGIN_BASE_URL.'js/public.js',
            ['jquery', $this->_plugin_slug.'-modal',],
            LEYKA_VERSION,
            true
        );

        do_action('leyka_enqueue_scripts'); // Allow the gateways to add their own scripts

    }

    public function localize_scripts() {

        $js_data = apply_filters('leyka_js_localized_strings', [
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
        ]);

        $leyka_js_handle = wp_script_is('leyka-public') ? 'leyka-public' : 'leyka-new-templates-public';

        wp_localize_script(apply_filters('leyka_js_localized_script_id', $leyka_js_handle), 'leyka', $js_data);

    }

    /** Register leyka user roles and caps. */
    public function register_user_capabilities() {

        $role = get_role('administrator'); // Just in case. There were some exotic cases
        if( !$role ) {
            return;
        }

        // Create all roles and capabilities:
        $caps = [
            'read' => true, 'edit_#base#' => true, 'read_#base#' => true, 'delete_#base#' => true,
            'edit_#base#s' => true, 'edit_others_#base#s' => true, 'publish_#base#s' => true,
            'read_private_#base#s' => true, 'delete_#base#s' => true, 'delete_private_#base#s' => true,
            'delete_published_#base#s' => true, 'delete_others_#base#s' => true,
            'edit_private_#base#s' => true, 'edit_published_#base#s' => true,
            'upload_files' => true, 'unfiltered_html' => true, 'leyka_manage_donations' => true,
        ];

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
                array_merge($caps, ['leyka_manage_options' => true,])
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

        if(leyka_get_donations_storage_type() === 'post') { // Donations PT (only if needed)

            $args = [
                'label' => __('Donations', 'leyka'),
                'labels' => [
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
                ],
                'exclude_from_search' => true,
                'public' => true,
                'show_ui' => true,
                'show_in_nav_menus' => false,
                'show_in_menu' => false,
                'show_in_admin_bar' => false,
                'supports' => false,
                'taxonomies' => [],
                'has_archive' => apply_filters('leyka_donations_archive_slug', 'donations'),
                'capability_type' => ['donation', 'donations'],
                'map_meta_cap' => true,
                'rewrite' => ['slug' => 'donation', 'with_front' => false],
                'show_in_rest' => false, // True to use Gutenberg editor, false otherwise
            ];

            register_post_type(Leyka_Donation_Management::$post_type, $args);

            // Donation editing messages:
            add_filter('post_updated_messages', [Leyka_Donation_Management::get_instance(), 'set_admin_messages']);

            // Post-typed Donations custom statuses:
            register_post_status('submitted', [
                'label'                     => _x('Submitted', '«Submitted» donation status', 'leyka'),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop('Submitted <span class="count">(%s)</span>', 'Submitted <span class="count">(%s)</span>', 'leyka'),
            ]);

            register_post_status('funded', [
                'label'                     => _x('Funded', '«Completed» donation status', 'leyka'),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop('Funded <span class="count">(%s)</span>', 'Funded <span class="count">(%s)</span>', 'leyka'),
            ]);

            register_post_status('refunded', [
                'label'                     => _x('Refunded', '«Refunded» donation status', 'leyka'),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop('Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'leyka'),
            ]);

            register_post_status('failed', [
                'label'                     => _x('Failed', '«Failed» donation status', 'leyka'),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop('Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'leyka'),
            ]);

        }

        // Campaigns:
        $args = [
            'labels' => [
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
            ],
            'exclude_from_search' => false,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_nav_menus' => true,
            'show_in_menu' => false,
            'show_in_admin_bar' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'revisions',],
            'taxonomies' => [],
            'has_archive' => true,
            'capability_type' => ['campaign', 'campaigns'],
            'map_meta_cap' => true,
            'rewrite' => ['slug' => 'campaign', 'with_front' => false],
            'show_in_rest' => true, // True to use Gutenberg editor, false otherwise
        ];

        register_post_type(Leyka_Campaign_Management::$post_type, $args);

        // Campaign editing messages:
        add_filter('post_updated_messages', [Leyka_Campaign_Management::get_instance(), 'set_admin_messages']);

        do_action('leyka_cpt_registered');

    }

    public function register_taxonomies() {

        if(leyka()->opt('donor_management_available')) {

            register_taxonomy(
                Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME,
                'user',
                [
                    'public' => true,
                    'labels' => [
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
                    ],
//                    'update_count_callback' => function() { // We may have to add a custom function for it
//                        return; // Important
//                    }
                ]
            );

        }

        if(leyka_options()->opt('campaign_categories_available')) {

            register_taxonomy(
                Leyka_Campaign::CAMPAIGNS_CATEGORIES_TAXONOMY_NAME,
                Leyka_Campaign_Management::$post_type,
                [
                    'public' => true,
                    'hierarchical' => true,
                    'show_in_rest' => true,
                    'show_admin_column' => true,
                    'labels' => [
                        'name' => __('Campaigns categories', 'leyka'),
                        'singular_name'	=> __('Campaigns category', 'leyka'),
                        'menu_name'	=> __('Categories', 'leyka'), // &#9492;&nbsp;
                        'search_items' => __('Search campaigns categories', 'leyka'),
                        'popular_items' => __('Popular campaigns categories', 'leyka'),
                        'all_items'	=> __('All campaigns categories', 'leyka'),
                        'edit_item'	=> __('Edit campaigns category', 'leyka'),
                        'update_item' => __('Update campaigns category', 'leyka'),
                        'add_new_item' => __('Add new campaigns category', 'leyka'),
                        'new_item_name'	=> __('New campaigns category name', 'leyka'),
                    ],
                    'rewrite' => ['slug' => 'campaign-category', 'with_front' => false,],
//                    'query_var' => '',
//                    'update_count_callback' => function(){ // We may have to add a custom function for it
//                        return; // Important
//                    }
                ]
            );

        }

    }

    /**
     * Add the plugin's rules themselves.
     * @var $rules array
     * @return array
     */
    public function insert_rewrite_rules(array $rules) {

        $leyka_rewrite_rules = [
            'campaign/([^/]+)/donations/?$' => 'index.php?post_type='.Leyka_Donation_Management::$post_type.'&leyka_campaign_filter=$matches[1]',
            'campaign/([^/]+)/donations/page/([0-9]{1,})/?$' =>
                'index.php?post_type='.Leyka_Donation_Management::$post_type.'&leyka_campaign_filter=$matches[1]&paged=$matches[2]',
        ];

        if(leyka_get_donations_storage_type() === 'sep') {
            $leyka_rewrite_rules = $leyka_rewrite_rules + [
                'donations/?$' => 'index.php?pagename=donations',
                'donations/page/([1-9]{1,})/?$' => 'index.php?pagename=donations&paged=$matches[1]',
            ];
        } else { // Donations as posts - just use the archive pages
            $leyka_rewrite_rules = $leyka_rewrite_rules + [
                'donations/?$' => 'index.php?post_type='.Leyka_Donation_Management::$post_type,
                'donations/page/([1-9]{1,})/?$' => 'index.php?post_type='.Leyka_Donation_Management::$post_type.'&paged=$matches[1]',
            ];
        }

        return $leyka_rewrite_rules + $rules; // The rules order is important

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
     * @depracated Used for old templates only.
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

            if( !$this->_payment_url ) {
                $this->add_payment_form_error(new WP_Error('no_payment_url_set', __("The gateway URL to redirect isn't set", 'leyka')));
            }

            if($this->payment_form_has_errors()) {

                $this->_add_session_errors(); // Error handling

                $referer = wp_get_referer();
                if(strstr($referer, '#') !== false) {

                    $referer = explode('#', $referer);
                    $referer = reset($referer);

                }

                wp_redirect($referer.'#leyka-submit-errors');
                exit();

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

        if(is_array($form_errors) && $form_errors) {

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

        leyka_remember_donation_data(['donation_id' => $donation_id]);

        $pm = leyka_pf_get_payment_method_value();

        do_action(
            'leyka_payment_form_submission-'.$pm['gateway_id'],
            $pm['gateway_id'],
            $pm['payment_method_id'],
            $donation_id,
            $_POST
        );

        $this->_submitted_donation_id = $donation_id;

        $this->_payment_vars = apply_filters(
            'leyka_submission_form_data-'.$pm['gateway_id'],
            $this->_payment_vars,
            $pm['payment_method_id'],
            $donation_id
        );

        $this->_payment_url = apply_filters(
            'leyka_submission_redirect_url-'.$pm['gateway_id'],
            $this->_payment_url,
            $pm['payment_method_id']
        );

        $this->_submission_redirect_type = apply_filters(
            'leyka_submission_redirect_type-'.$pm['gateway_id'],
            'auto',
            $pm['payment_method_id'],
            $donation_id
        );

    }

    /** Save the basic donation data and return new donation ID, so gateway can add it's specific data to the logs. */
    public function log_submission() {

        if(empty($_POST['leyka_campaign_id']) || absint($_POST['leyka_campaign_id']) <= 0) {
            return false;
        }

        $campaign = new Leyka_Campaign(absint($_POST['leyka_campaign_id']));
        $pm_data = leyka_pf_get_payment_method_value();

        $params = [
            'purpose_text' => $campaign->payment_title,
            'gateway_id' => $pm_data['gateway_id'],
            'pm_id' => $pm_data['payment_method_id'],
        ];

        if( // For the direct GA integration:
            leyka_options()->opt('use_gtm_ua_integration') === 'enchanced_ua_only'
            && leyka_options()->opt('gtm_ua_tracking_id')
            && in_array('purchase', leyka_options()->opt('gtm_ua_enchanced_events'))
        ) {

            $ga_client_id = leyka_gua_get_client_id();
            if(mb_stristr($ga_client_id, '.')) { // A real GA client ID found, save it
                $params['ga_client_id'] = $ga_client_id;
            }

        }

        // Saving values of Additional form fields:
        foreach($campaign->get_calculated_additional_fields_settings() as $field_slug => $field) {
            $params['additional_fields'][$field_slug] = esc_attr(wp_strip_all_tags($_POST['leyka_'.$field_slug]));
        }

        $donation_id = Leyka_Donations::get_instance()->add(apply_filters('leyka_new_donation_data', $params));

        if( !is_wp_error($donation_id) ) {

            $campaign->increase_submits_counter();

            do_action('leyka_log_donation', $pm_data['gateway_id'], $pm_data['payment_method_id'], $donation_id);
            do_action('leyka_log_donation-'.$pm_data['gateway_id'], $donation_id);

        }

        return $donation_id;

    }

    /** @todo Move the method to the special class for the emails management / logging */
    public function handle_non_init_recurring_donor_registration($donor_user_id, Leyka_Donation_Base $donation) {

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
        $email_placeholders = [
            '#SITE_NAME#',
            '#SITE_EMAIL#',
            '#SITE_URL#',
            '#ORG_NAME#',
            '#ORG_SHORT_NAME#',
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
        ];
        $email_placeholder_values = [
            get_bloginfo('name'),
            get_bloginfo('admin_email'),
            home_url(),
            leyka()->opt('org_full_name'),
            leyka()->opt('org_short_name'),
            $donation->id,
            leyka_get_payment_types_list($donation->type),
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
        ];

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
            [
                'From: '.apply_filters(
                    'leyka_email_from_name',
                    leyka_options()->opt_safe('email_from_name'),
                    $donation,
                    $campaign
                ).' <'.leyka_options()->opt_safe('email_from').'>',
            ]
        );

        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        return $res;

    }

    /**
     * @todo Move the method to the special class for the emails management / logging
     *
     * @param $donor_account_error WP_Error
     * @param $donation int|WP_Post|Leyka_Donation_Base
     */
    public function handle_donor_account_creation_error(WP_Error $donor_account_error, $donation) {

        $donation = Leyka_Donations::get_instance()->get_donation($donation);
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
                    admin_url('admin.php?page=leyka_donation_info&donation='.$donation->id)
                ),
                $donor_account_error,
                $donation
            )),
            [
                'From: '.apply_filters(
                    'leyka_email_from_name',
                    leyka_options()->opt_safe('email_from_name'),
                    $donor_account_error,
                    $donation
                ).' <'.leyka_options()->opt_safe('email_from').'>',
            ]
        );

        remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

    }

    /** Templates manipulations. */
    /** @todo Move all templates related methods to the sep. class ("Leyka_Form_Templates", leyka_templates()). */

    /**
     * @param $params array.
     * @return array An array of donations forms templates info.
     */
    public function get_templates(array $params = []) {

        $params = array_merge([
            'is_service' => false,
            'include_deprecated' => leyka_options()->opt('allow_deprecated_form_templates')
        ], $params);

//        $this->_templates = $this->_templates ? : [];

        if( !!$params['is_service'] ) {
            $this->_templates = glob(LEYKA_PLUGIN_DIR.'templates/service/leyka-template-*.php');
        } else {

            $custom_templates = glob(get_stylesheet_directory().'/leyka-template-*.php');
            $custom_templates = $custom_templates ? : [];

            $this->_templates = apply_filters(
                'leyka_templates_list',
                array_merge($custom_templates, glob(LEYKA_PLUGIN_DIR.'templates/leyka-template-*.php'))
            );

        }

        $this->_templates = array_map([$this, '_get_template_data'], $this->_templates);

        foreach($this->_templates as $index => $template_data) { // Remove the disabled templates from the list
            if( !empty($template_data['disabled']) && $template_data['disabled'] !== 'false') {
                unset($this->_templates[$index]);
            }
        }

        if( !$params['include_deprecated'] ) {
            foreach($this->_templates as $index => $template_data) {
                if( !empty($template_data['deprecated']) ) {
                    unset($this->_templates[$index]);
                }
            }
        }

        // Templates ordering:
        $ordered_templates = [];

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

        $data = get_file_data($file, [
            'name' => 'Leyka Template',
            'description' => 'Description',
            'debug_only' => 'Debug only',
            'deprecated' => 'Deprecated',
            'disabled' => 'Disabled',
        ]);

        $data['file'] = $file;
        $data['basename'] = basename($file);
        $data['id'] = str_replace(['leyka-template-', '.php'], '', $data['basename']);

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

        $templates = $this->get_templates(['is_service' => !!$is_service, 'include_deprecated' => true,]);
        if( !$templates ) {
            return false;
        }

        foreach($templates as $template) {

            $current_template_id = str_replace(['leyka-template-', '.php'], '', $template['basename']);

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

        $template_data = get_file_data($template_main_file_addr, [
            'name' => 'Leyka Template',
            'description' => 'Description',
            'debug_only' => 'Debug only',
            'deprecated' => 'Deprecated',
            'disabled' => 'Disabled',
        ]);

        if( !$template_data ) {
            /** @todo Throw some Ex? */ return false;
        }

        return !empty($template_data['deprecated']);

    }

    public function template_is_disabled($template_id) {

        $template_main_file_addr = LEYKA_PLUGIN_DIR."templates/leyka-template-$template_id.php";
        if($template_id === 'default' || !file_exists($template_main_file_addr)) {
            /** @todo Throw some Ex? */ return false;
        }

        $template_data = get_file_data($template_main_file_addr, [
            'name' => 'Leyka Template',
            'description' => 'Description',
            'debug_only' => 'Debug only',
            'deprecated' => 'Deprecated',
            'disabled' => 'Disabled',
        ]);

        if( !$template_data ) {
            /** @todo Throw some Ex? */ return false;
        }

        return !empty($template_data['disabled']) && $template_data['disabled'] !== 'false';

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
__('Need Help', 'leyka');
__('A modern and lightweight form template', 'leyka');
__('Another modern and lightweight form template', 'leyka');
_x('phone', 'Field type title', 'leyka');
_x('date', 'Field type title', 'leyka');
_x('text', 'Field type title', 'leyka');
_x('Recurrings', 'Dashboard portlet title', 'leyka');
_x('Active', 'Recurring subscription status, singular (like [subscription is] "Active/Non-active/Problematic")', 'leyka');
_x('Non-active', 'Recurring subscription status, singular (like [subscription is] "Active/Non-active/Problematic")', 'leyka');
_x('Problematic', 'Recurring subscription status, singular (like [subscription is] "Active/Non-active/Problematic")', 'leyka');
_x('Recurrings', '"Recurring donations" with one multiple-case word', 'leyka');
