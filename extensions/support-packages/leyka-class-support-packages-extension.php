<?php if( !defined('WPINC') ) die;
/**
 * Extension name: Support Packages
 * Version: 0
 * Author: Teplitsa of social technologies
 * Author URI: https://te-st.ru
 * Debug only: 0
 **/

class Leyka_Support_Packages_Extension extends Leyka_Extension {

    const MAX_PACKAGES_NUMBER = 5;

    protected static $_features = [
        'leyka_limited_content' => [
            'class' => 'Leyka_Support_Packages_Limit_Content_Feature',
            'is_shortcode' => true,
            'shortcode_atts' => ['support_plan' => '',]
        ],
    ];

    protected static $_instance;
    protected $_packages = null;

    protected function _set_attributes() {

        $this->_id = 'support_packages'; // Must be a unique string, like "support_packages"
        $this->_title = __('Support packages', 'leyka'); // A human-readable title, like "Support packages"

        // A human-readable short description (for backoffice extensions list page):
        $this->_description = __('The extension allows to create donors groups by amount of their recurring payments, and allow these groups access to closed website content.', 'leyka');

        // A human-readable full description (for backoffice extensions list page):
        $this->_full_description = __('Create exclusive content for your active recurring donors and attract new ones. By subscribing to recurring donations, donors will be allowed to read website pages and posts that you will mark as open only for them. The content access is easy to control via shortcodes.', 'leyka');

        // A human-readable description (for backoffice extension settings page):
        $this->_settings_description = __('If donor suddenly decides to change his/her monthly recurring donation amount, e.g., increases the amount from 999 RUB to 1050 RUB (and thus moves from "Base donors" group to the "Silver donors"), the switch between the support packages for this donor will proceed automatically.', 'leyka');

        // A human-readable description of how to enable the main feature (for backoffice extension settings page):
        $this->_connection_description = '<p><strong>Подключение функции «Ограничение доступа к контенту»</strong></p>
<p>Доступ можно ограничить ко всему посту или к частям текста с помощью шорткода</p>
<code>[leyka_limited_content support_plan="Программное название вознаграждения"]</code>
<br>Ваш текст<br>
<code>[/leyka_limited_content]</code>';

        $this->_screenshots = [
            LEYKA_PLUGIN_BASE_URL.'extensions/support-packages/img/widget-scheme.png' => LEYKA_PLUGIN_BASE_URL.'extensions/support-packages/img/widget-scheme-full.png',
        ];

        $this->_user_docs_link = '//leyka.te-st.ru/docs/pakety-podderzhki/'; // Extension user manual page URL
        $this->_has_wizard = false;
        $this->_has_color_options = true;

    }

    protected function _set_options_defaults() {

        $this->_options = apply_filters('leyka_'.$this->_id.'_extension_options', [
            ['section' => [
                'name' => $this->_id.'-main-options',
                'title' => __('Main options', 'leyka'),
                'is_default_collapsed' => false,
                'options' => [
                    $this->_id.'_title' => [
                        'type' => 'text',
                        'title' => __('1. Appeal title', 'leyka'),
                        'required' => true,
                        'placeholder' => __('Subscribe to read the whole', 'leyka'),
                        'default' => __('Subscribe to read the whole', 'leyka'),
                        'width' => 0.5,
                    ],
                    $this->_id.'_main_text' => [
                        'type' => 'textarea',
                        'title' => __('2. Appeal text', 'leyka'),
                        'required' => false,
                    ],
                    $this->_id.'_subscription_text' => [
                        'type' => 'textarea',
                        'title' => __('3. Text about subscription', 'leyka'),
                        'placeholder' => __('Subscription renews automatically. You can unsubscribe at any time in your Account', 'leyka'),
                        'required' => false,
                    ],
                    $this->_id.'_activation_button_label' => [
                        'type' => 'text',
                        'title' => __('4. Activation button label', 'leyka'),
                        'required' => true,
                        'placeholder' => __('Subscribe', 'leyka'),
                        'default' => __('Subscribe', 'leyka'),
                        'width' => 0.5,
                    ],
                    $this->_id.'_account_link_label' => [
                        'type' => 'text',
                        'title' => __('5. Account link label', 'leyka'),
                        'required' => true,
                        'placeholder' => esc_html__('I am already subscribed', 'leyka'),
                        'default' => esc_html__('I am already subscribed', 'leyka'),
                        'width' => 0.5,
                    ],
                    $this->_id.'_closed_content_icon' => [
                        'type' => 'file',
//                        'upload_format' => 'pics',
//                        'show_preview' => false,
                        'title' => '',
//                        'upload_title' => 'Выберите картинку',
                        'upload_label' => __('Load closed content icon', 'leyka'),
                        'description' => __('A *.png or *.svg file. The size is no more than 2 Mb', 'leyka'),
//                        'comment' => 'Тестовый коммент к полю загрузки картинки.',
//                        'required' => false,
                        'default' => '', /** @todo Add the default icon URL */
//                        'field_classes' => '', /** @todo Add the default icon URL */
                    ],
                    $this->_id.'_campaign' => [
                        'type' => 'campaign_select',
                        'title' => __('Campaign for recurring subscriptions', 'leyka'),
                        'required' => true,
                    ],
                ],
            ],],
            ['section' => [
                'name' => $this->_id.'-packages',
                'title' => __('Packages options', 'leyka'),
                'is_default_collapsed' => false,
                'options' => [
                    'custom_support_packages_settings' => [
                        'type' => 'custom_support_packages_settings', // Special option type
                    ],
                ],
            ],],
            ['section' => [
                'name' => $this->_id.'-for-devs',
                'title' => __('For developers', 'leyka'),
                'is_default_collapsed' => true,
                'options' => [
                    $this->_id.'_css' => [
                        'type' => 'textarea',
                        'is_code_editor' => 'css',
                        'title' => __('Styles settings', 'leyka'),
//                        'default' => '/* .some-selector-1 { color: black; } */ '.__('/* The main font color */', 'leyka')
//                            .'/* .some-selector-2 { color: orange; } */ '.__('/* The secondary font color */', 'leyka'),
                    ],
                ],
            ],],
        ]);

    }
    
    protected function get_color_options() {
        return [
            'type' => 'container',
            'classes' => 'extension-color-options support-packages-color-options',
            'entries' => [
                $this->_id.'_main_color' => [
                    'type' => 'colorpicker',
                    'title' => 'Главный цвет', // __('', 'leyka'),
                    'description' => 'Рекомендуем яркий цвет', // __('', 'leyka'),
                    'default' => '#F38D04',
                ],
                $this->_id.'_background_color' => [
                    'type' => 'colorpicker',
                    'title' => 'Цвет фона', // __('', 'leyka'),
                    'description' => 'Контрастный основному цвету', // __('', 'leyka'),
                    'default' => '#ffffff',
                ],
                $this->_id.'_caption_color' => [
                    'type' => 'colorpicker',
                    'title' => 'Цвет надписей', // __('', 'leyka'),
                    'description' => 'Контрастный основному цвету', // __('', 'leyka'),
                    'default' => '#ffffff',
                ],
                $this->_id.'_text_color' => [
                    'type' => 'colorpicker',
                    'title' => 'Цвет текста', // __('', 'leyka'),
                    'description' => 'Рекомендуем контрастный фону', // __('', 'leyka'),
                    'default' => '#000000',
                ],
            ],
        ];
    }

    public function activation_valid() {

        if( !leyka_options()->opt('donor_accounts_available') ) {
            return new WP_Error(
                $this->_id.'-accounts-disabled',
                sprintf(__('Donors accounts are mandatory for the Extension to work! Please, <a href="%s">enable Donors accounts</a> in the plugin settings.', 'leyka'), admin_url('admin.php?page=leyka_settings&stage=additional#donor_accounts'))
            );
        }

        return true;

    }

    public function admin_notices() {

        if( !$this->get_available_campaign() ) {
            echo '<div class="error">
                <p>'.sprintf(__("<strong>Leyka warning!</strong> The Support packages Extension currently doesn't have a campaign for donors to make recurring subscriptions. The campaign must be <strong>published</strong>, <strong>not marked as \"finished\"</strong> and, ideally, <strong>marked as persistent</strong> to be available.<br><br>Please see to it that you have at least <strong>one such campaign</strong>, and select the campaign in the <a href='%s'>Support packages settings page</a>.", 'leyka'), admin_url('admin.php?page=leyka_extension_settings&extension='.$this->_id)).'</p>
            </div>';
        }

    }

    public function load_admin_scripts() {

        if( !Leyka_Extension::is_admin_settings_page($this->_id) ) { // Extension CSS & JS is only for admin settings page
            return;
        }

        wp_enqueue_script(
            $this->_id.'-admin',
            LEYKA_PLUGIN_BASE_URL.'extensions/support-packages/assets/js/admin.js',
            ['jquery',],
            defined('WP_DEBUG') && WP_DEBUG ? uniqid() : null,
            true
        );

    }

    protected function _initialize_active() {

        add_filter('post_class', [$this, 'add_post_class'], 10, 3);
        add_filter('leyka_js_localized_strings', [$this, 'add_js_localized_strings']);
        add_action('admin_notices', [$this, 'admin_notices']);
        add_action('leyka_campaign_after_saving', [$this, '_packages_campaign_data_saving'], 10, 2);

        // Set up the Extension shortcodes:
        foreach(self::$_features as $feature_name => $feature_config) {
            if( !empty($feature_config['is_shortcode']) && $feature_config['is_shortcode'] ) {
                add_shortcode($feature_name, [$this, 'handle_shortcode']);
            }
        }

    }

    protected function _initialize_always() {

        add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']);

        add_action('leyka_set_support_packages_campaign_option_value', function($option_value){
            delete_option('leyka_support_packages_no_campaign_behavior');
        });

        if(is_admin()) {

            // Support packages custom option display:
            add_action('leyka_render_custom_support_packages_settings', [$this, '_render_support_packages_custom_option'], 10, 2);

            // Support packages custom option saving:
            add_action(
                'leyka_save_custom_option-custom_support_packages_settings',
                [$this, '_support_packages_custom_option_saving']
            );

        }

    }

    protected function _render_support_package_item_html($is_template = false, $placeholders = []) {

        $placeholders = wp_parse_args($placeholders, [
            'id' => '',
            'box_title' => __('New reward', 'leyka'),
            'package_title' => '',
            'amount_needed' => 0,
            'package_icon' => '',
        ]);

        $_COOKIE['leyka-support-packages-boxes-closed'] = empty($_COOKIE['leyka-support-packages-boxes-closed']) ?
            [] : json_decode(stripslashes('[\"someline\"]'));?>

        <div id="<?php echo $placeholders['id'] ? 'item-'.$placeholders['id'] : 'item-'.leyka_get_random_string(4);?>" class="multi-valued-item-box package-box <?php echo $is_template ? 'item-template' : '';?> <?php echo !$is_template && !empty($_COOKIE['leyka-support-packages-boxes-closed']) && !empty($placeholders['id']) && in_array($placeholders['id'], $_COOKIE['leyka-support-packages-boxes-closed']) ? 'closed' : '';?>" <?php echo $is_template ? 'style="display: none;"' : '';?>>

            <h3 class="item-box-title ui-sortable-handle">
                <span class="draggable"></span>
                <span class="title"><?php echo esc_html($placeholders['box_title']);?></span>
            </h3>

            <div class="box-content">

                <div class="option-block type-text">

                    <div class="leyka-text-field-wrapper">
                        <?php leyka_render_text_field('package_title', [
                            'title' => __('Reward title', 'leyka'),
                            'placeholder' => __('E.g., "Golden support level"', 'leyka'),
                            'required' => true,
                            'value' => $placeholders['package_title'],
                        ]);?>
                    </div>

                    <div class="field-errors"></div>

                </div>

                <?php if($placeholders['id']) {?>
                    <div class="option-block type-text-readonly">
                        <div class="leyka-text-field-wrapper">
                            <?php leyka_render_text_field('package_id', [
                                'title' => __('Package ID', 'leyka'),
                                'value' => $placeholders['id'],
                                'is_read_only' => true,
                            ]);?>
                        </div>
                    </div>
                <?php }?>

                <div class="option-block type-number">

                    <div class="leyka-number-field-wrapper">
                        <?php leyka_render_number_field('package_amount_needed', [
                            'title' => sprintf(__('Donations amount needed, %s', 'leyka'), leyka_get_currency_label()),
                            'placeholder' => '500',
                            'required' => true,
                            'value' => $placeholders['amount_needed'],
                        ]);?>
                    </div>

                    <div class="field-errors"></div>

                </div>

                <div class="settings-block option-block type-file">

                    <?php leyka_render_file_field('package_icon', [
                        'upload_label' => __('Load icon', 'leyka'),
                        'description' => __('A *.png or *.svg file. The size is no more than 2 Mb', 'leyka'),
                        'required' => true,
                        'value' => $placeholders['package_icon'],
                    ]);?>

                    <div class="field-errors"></div>

                </div>

                <div class="box-footer">
                    <div class="delete-item delete-package"><?php _e('Delete the reward', 'leyka');?></div>
                </div>

            </div>

        </div>

    <?php }

    public function _render_support_packages_custom_option($option_id, $data){

        $option_id = mb_stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;?>

        <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-<?php echo $option_id;?>-field-wrapper multi-valued-items-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) ? '' : implode(' ', $data['field_classes']);?>">

            <div class="leyka-main-multi-items leyka-main-support-packages" data-max-items="<?php echo Leyka_Support_Packages_Extension::MAX_PACKAGES_NUMBER;?>" data-min-items="1" data-items-cookie-name="leyka-support-packages-boxes-closed" data-item-inputs-names-prefix="leyka_package_">

            <?php $data['value'] = empty($data['value']) || !is_array($data['value']) ?
                leyka_options()->opt('custom_support_packages_settings') :
                $data['value'];

            if($data['value'] && is_array($data['value'])) { // Display existing items (the assoc. array keys order is important)
                foreach($data['value'] as $package_id => $options) {
                    $this->_render_support_package_item_html(false, [
                        'id' => $package_id,
                        'box_title' => $options['title'],
                        'package_title' => $options['title'],
                        'amount_needed' => $options['amount_needed'],
                        'package_icon' => $options['icon'],
                    ]);
                }
            }?>

            </div>

            <?php $this->_render_support_package_item_html(true); // Package box template ?>

            <div class="add-item bottom"><?php _e('Add reward', 'leyka');?></div>

            <input type="hidden" class="leyka-items-options" name="leyka_support_packages" value="">

        </div>

    <?php }

    public function _support_packages_custom_option_saving() {

        $_POST['leyka_support_packages'] = json_decode(urldecode($_POST['leyka_support_packages']));
        $result = [];

        foreach($_POST['leyka_support_packages'] as $package) {

            $package->id = stristr($package->id, 'item-') === false || empty($package->title) ?
                $package->id :
                trim(preg_replace('~[^-a-z0-9_]+~u', '-', mb_strtolower(leyka_cyr2lat($package->title))), '-');

            $result[$package->id] = [
                'title' => $package->title,
                'amount_needed' => $package->amount_needed,
                'icon' => $package->icon,
            ];

        }

        leyka_options()->opt('custom_support_packages_settings', $result);

    }

    /* Campaign data saving - handling for the case of "single available campaign ceased to be available" */
    public function _packages_campaign_data_saving($campaign_data, Leyka_Campaign $campaign) {

        if( !is_array($campaign_data) ) {
            return;
        }

        if( // The case when Packages Campaign is reactivated, or there is another Campaign available for the Extension
            (
                $campaign->id == leyka_options()->opt('support_packages_campaign')
                && ($campaign_data['post_status'] === 'publish' || !empty($campaign_data['publish']))
                && empty($campaign_data['is_finished'])
                && get_option('leyka_support_packages_no_campaign_behavior')
            )
            || $this->get_available_campaigns_count()
        ) {
            delete_option('leyka_support_packages_no_campaign_behavior');
        }

    }

    protected function _is_package_active($package, $recurring_subscriptions) {

        $total_subscriptions_amount = 0;
        foreach($recurring_subscriptions as $init_donation) {

            if($init_donation->cancel_recurring_requested) {
                continue;
            }

            $total_subscriptions_amount += $init_donation->amount;

        }

        return $total_subscriptions_amount >= $package->amount_needed;

    }

    public function is_package_active($package, $user) {

        $active_package = $this->get_user_active_package($user);

        return $active_package && $active_package->id === $package->id;

    }

    public function is_package_activation_available($package, $user) {

        $active_package = $this->get_user_active_package($user);

        return !$active_package || ($active_package->amount_needed < $package->amount_needed);

    }

    public function is_package_activated($package, $user) {

        try {
            $donor = new Leyka_Donor($user);
        } catch(Exception $ex) {
            return false;
        }

        return $this->_is_package_active($package, $donor->get_init_recurring_donations());

    }
    
    public function has_packages() {
        return count($this->get_packages()) > 0;
    }

    public function get_packages($min_package = null, $order_by_amount = false) {

        if($this->_packages === null) {

            $packages = leyka()->opt('custom_support_packages_settings');
            
            $this->_packages = [];
            foreach($packages as $package_id => $package_params) {

                $package_params['id'] = $package_id;
                $this->_packages[] = new Leyka_Support_Packages_Package($package_params);

            }

        }

        if($min_package) {

            $result_packages = [];
            foreach($this->_packages as $package) {

                if($min_package->amount_needed > $package->amount_needed) {
                    continue;
                }

                $result_packages[] = $package;

            }

        } else {
            $result_packages = $this->_packages;
        }

        if($order_by_amount) {

            if(mb_strtolower($order_by_amount) === 'asc') {

                usort($result_packages, function($package_1, $package_2){
                    if($package_1->amount_needed == $package_2->amount_needed) {
                        return 0;
                    } else {
                        return $package_1->amount_needed < $package_2->amount_needed ? -1 : 1;
                    }
                });

            } else {

                usort($result_packages, function($package_1, $package_2){
                    if($package_1->amount_needed == $package_2->amount_needed) {
                        return 0;
                    } else {
                        return $package_1->amount_needed > $package_2->amount_needed ? -1 : 1;
                    }
                });

            }

        }

        return $result_packages;

    }
    
    public function reset_packages() {
        $this->_packages = null;
    }
    
    public function get_user_activated_packages($user) {

        $active_packages = [];
        try {
            $donor = new Leyka_Donor($user);
        } catch(Exception $ex) {
            return $active_packages;
        }

        foreach($this->get_packages() as $package) {
            if($this->_is_package_active($package, $donor->get_init_recurring_donations(true))) {
                $active_packages[] = $package;
            }
        }

        return $active_packages;

    }

    public function get_package($package_id) {

        foreach($this->get_packages() as $package) {
            if($package->id === $package_id) {
                return $package;
            }
        }

        return null;

    }

    public function get_user_active_package($user) {

        try {
            $donor = new Leyka_Donor($user);
        } catch(Exception $ex) {
            return null;
        }

        $max_active_package = null;
        $max_active_package_amount = 0;
        foreach($this->get_packages() as $package) {

            if(
                $this->_is_package_active($package, $donor->get_init_recurring_donations())
                && $max_active_package_amount < $package->amount_needed
            ) {

                $max_active_package = $package;
                $max_active_package_amount = $package->amount_needed;

            }

        }

        return $max_active_package;

    }

    public function get_activate_feature_form($feature, $user) {

        $leyka_ext_sp_template_tags = new Leyka_Support_Packages_Template_Tags();

        ob_start();
        $leyka_ext_sp_template_tags->show_activate_feature_form($feature, $user, $this);
        return ob_get_clean();

    }
    
    public function is_feature_open($feature, $user) {

        if($feature->support_plan) {

            $package = $this->get_package($feature->support_plan);
            return $package && $this->is_package_activated($package, $user);

        }
        
        return false;

    }
    
    public function add_js_localized_strings($strings) {

        $custom_locked_icon_path = leyka()->opt('support_packages_closed_content_icon');

        if($custom_locked_icon_path) {

            $upload_dir = wp_get_upload_dir();
            $strings['ext_sp_article_locked_icon'] = $upload_dir['baseurl'].$custom_locked_icon_path;

        } else {
            $strings['ext_sp_article_locked_icon'] = LEYKA_PLUGIN_BASE_URL.'extensions/'
                .Leyka_Support_Packages_Extension::get_instance()->id_dash.'/img/icon-post-locked.png';
        }

        return $strings;

    }

    public function add_post_class($classes, $class, $post_id) {

        global $post;

        $feature_name = 'leyka_limited_content';
        $feature_config = Leyka_Support_Packages_Extension::$_features[$feature_name];

        $post = get_post($post_id);

        $pattern = get_shortcode_regex();
        if(preg_match_all('/'.$pattern.'/s', $post->post_content, $matches)) {
            foreach($matches[0] as $key => $value) {

                if($matches[2][$key] !== $feature_name) {
                    continue;
                }

                parse_str(str_replace(" ", "&", $matches[3][$key]), $atts);

                if( !empty($feature_config['shortcode_atts']) ) {
                    $feature_config['shortcode_atts'] = shortcode_atts($feature_config['shortcode_atts'], $atts);
                }

                $feature = new $feature_config['class']($feature_name, $feature_config);

                if( !$this->is_feature_open($feature, wp_get_current_user()) ) {
                    $classes[] = 'leyka-ext-sp-locked-content';
                }

            }
        }

        return $classes;

    }
    
    public function handle_shortcode($atts, $content = null, $tag = null) {

        $user = wp_get_current_user();

        if( !Leyka_Support_Packages_Extension::get_instance()->is_active ) {
            return do_shortcode($content);
        }

        foreach(Leyka_Support_Packages_Extension::$_features as $feature_name => $feature_config) {

            if($feature_name === $tag) {

                if( !empty($feature_config['shortcode_atts']) ) {
                    $feature_config['shortcode_atts'] = shortcode_atts($feature_config['shortcode_atts'], $atts);
                }

                /** @var Leyka_Support_Packages_Feature $feature */
                $feature = new $feature_config['class']($feature_name, $feature_config);
                if( !is_object($feature) ) {
                    return '';
                }

                if(
                    $this->is_feature_open($feature, $user)
                    || get_option('leyka_support_packages_no_campaign_behavior') === 'content-open'
                ) {
                    return $feature->do_if_open(['content' => $content]);
                } else {
                    return $feature->do_if_closed(['content' => $content])
                        .$this->get_activate_feature_form($feature, $user);
                }

            }

        }

        return '';

    }

    /** @return WP_Post A Campaign currently used for Support packages, or NULL if none found. */
    public function get_available_campaign() {

        $sp_campaign_id = leyka()->opt('support_packages_campaign');

        if($sp_campaign_id) {

            $sp_campaign = get_post($sp_campaign_id);
            if($sp_campaign->post_status === 'publish') {
                return $sp_campaign;
            }

        }

        $sp_campaign = get_posts([
            'post_type' => Leyka_Campaign_Management::$post_type,
            'post_status' => 'publish',
            'meta_query' => [
                ['key' => 'is_finished', 'value' => 1, 'compare' => '!=', 'type' => 'NUMERIC',],
                ['key' => 'campaign_type', 'value' => 'persistent'],
            ],
            'posts_per_page' => 1,
        ]);

        return $sp_campaign ? reset($sp_campaign) : null;

    }

    public function get_available_campaigns_count() {

        $campaigns = new WP_Query([
            'post_type' => Leyka_Campaign_Management::$post_type,
            'post_status' => 'publish',
            'meta_query' => [
                ['key' => 'is_finished', 'value' => 1, 'compare' => '!=', 'type' => 'NUMERIC',],
                ['key' => 'campaign_type', 'value' => 'persistent'],
            ],
            'nopaging' => true,
        ]);

        return $campaigns->found_posts;

    }

}

abstract class Leyka_Support_Packages_Feature {

    protected $_config = [];

    public function __construct($feature_name, array $config = []) {
        $this->_config = $config;
    }

    abstract public function do_if_open($params);

    abstract public function do_if_closed($params);

}

class Leyka_Support_Packages_Shortcode_Feature extends Leyka_Support_Packages_Feature {

    public function __construct($feature_name, $config = []) {
        parent::__construct($feature_name, $config);
    }

    public function do_if_open($params) {
        return '';
    }

    public function do_if_closed($params) {
        return '';
    }

}

class Leyka_Support_Packages_Limit_Content_Feature extends Leyka_Support_Packages_Shortcode_Feature {

    public function __construct($feature_name, $config = []) {
        parent::__construct($feature_name, $config);
    }

    public function __get($field) {
        switch($field) {
            case 'support_plan':
                return empty($this->_config['shortcode_atts']['support_plan']) ?
                    '' : $this->_config['shortcode_atts']['support_plan'];

            case 'activate_title':
                return leyka()->opt('support_packages_title');

            case 'activate_subtitle':
                return leyka()->opt('support_packages_main_text');

            default:
                return '';
        }
    }

    public function do_if_open($params) {
        return empty($params['content']) ? '' : do_shortcode($params['content']);
    }

    public function do_if_closed($params) {
        return '';
    }

}

class Leyka_Support_Packages_Package {

    protected $_package_data;

    public function __construct($package_config=null) {
        if(is_array($package_config)) {
            $this->_package_data = $package_config;
        }
    }

    public function __get($field) {
        switch($field) {
            case 'id':
            case 'ID':
                return $this->_package_data['id'];

            case 'icon_url':
                $upload_dir = wp_get_upload_dir();
                return $this->_package_data['icon'] ?
                    $upload_dir['baseurl'].$this->_package_data['icon'] :
                    LEYKA_PLUGIN_BASE_URL.'extensions/'.Leyka_Support_Packages_Extension::get_instance()->id_dash.'/img/sup-pack-star-circle-24x24.svg';
            case 'icon_path':
                $upload_dir = wp_get_upload_dir();
                return $this->_package_data['icon'] ?
                    $upload_dir['basedir'].$this->_package_data['icon'] :
                    LEYKA_PLUGIN_DIR.'extensions/'.Leyka_Support_Packages_Extension::get_instance()->id_dash.'/img/sup-pack-star-circle-24x24.svg';

            case 'title':
                return $this->_package_data['title'];

            case 'price':
            case 'amount_needed':
                return absint($this->_package_data['amount_needed']);

            case 'price_currency':
                $currencies = leyka_get_currencies_data();
                $currency_sign = $currencies[ leyka_options()->opt('currency_main') ]['label'];
                return $currency_sign ? $currency_sign : __('₽', 'leyka');

            default:
                return apply_filters('leyka_ext_get_unknown_support_package_field', null, $field, $this);
        }
    }

}

class Leyka_Support_Packages_Template_Tags {

    protected function _show_card_data_3rows($package, array $params = []) {

        $is_active = !empty($params['is_active']) && !!$params['is_active'];

        if(empty($params['classes'])) {
            $params['classes'] = [];
        }

        if($is_active) {
            $params['classes'][] = 'active';
        }

        $extra_classes_str = !empty($params['classes']) ? implode(' ', $params['classes']) : '';?>

        <div class="leyka-ext-sp-card <?php echo $extra_classes_str;?>" data-amount_needed="<?php echo $package->amount_needed;?>">

            <div class="leyka-ext-sp-card-row1">

                <div class="leyka-ext-sp-icon">
                	<?php if(preg_match("/\.svg$/", $package->icon_url)) {
                        if(is_file($package->icon_path)) {
                            readfile($package->icon_path);
                        }
                    } else {?>
                		<img src="<?php echo $package->icon_url;?>" alt="">
            		<?php }?>
            	</div>

                <div class="leyka-ext-sp-title"><?php echo $package->title;?></div>

            </div>

            <div class="leyka-ext-sp-card-row2">
                <div class="leyka-ext-sp-price"><?php echo leyka_format_amount($package->price);?></div>
                <div class="leyka-ext-sp-currency"><?php echo $package->price_currency;?></div>
            </div>

            <div class="leyka-ext-sp-card-row3">

                <div class="leyka-ext-sp-period"><?php _e('Per month', 'leyka')?></div>

                <div class="leyka-ext-sp-status">
                	<?php if($is_active) {?>
                	<span><?php _e('Current status', 'leyka')?></span>
                	<?php } elseif(!empty($params['campaign_post_permalink']) && !empty($params['is_activation_available']) && $params['is_activation_available']) {?>
            		<a href="<?php echo $params['campaign_post_permalink'];?>#leyka-activate-package|<?php echo $package->amount_needed;?>" class="leyka-activate-package-link"><?php _e('Choose', 'leyka')?></a>
                	<?php }?>
                </div>

            </div>

        </div>

        <?php
    }
    
    public function show_manage_card($package, $params = []) {

        if(empty($params['classes'])) {
            $params['classes'] = [];
        }

        $params['classes'][] = 'leyka-ext-sp-manage-card';

        $this->_show_card_data_3rows($package, $params);

    }

    public function show_banner_card($package, $params = []) {

        if(empty($params['classes'])) {
            $params['classes'] = [];
        }

        $params['classes'][] = 'leyka-ext-sp-banner-card';

        $this->_show_card_data_3rows($package, $params);

    }
    
    public function show_activate_feature_form($feature, $user, Leyka_Support_Packages_Extension $leyka_ext_sp) {

        $feature_min_package = $leyka_ext_sp->get_package($feature->support_plan);

        $packages = $leyka_ext_sp->get_packages($feature_min_package);
        $packages_count = max(count($packages), 3);

        $campaign_post = $leyka_ext_sp->get_available_campaign();
        $campaign_post_permalink = $campaign_post ? get_post_permalink($campaign_post) : '';?>

        <div class="leyka-ext-sp-activate-feature-overlay">

        	<div class="leyka-ext-sp-activate-feature-overlay-gradient"></div>

        	<div class="leyka-ext-sp-activate-feature-overlay-bg-wrapper">
                <div class="leyka-ext-sp-activate-feature-overlay-bg">

                    <div class="leyka-ext-sp-activate-feature <?php echo "packages-count-" . count($packages); ?>" style="max-width: <?php echo $packages_count * 186 - 16;?>px;">
                    	<h3><?php echo $feature->activate_title;?></h3>
                    	<div class="leyka-ext-sp-feature-subtitle"><?php echo $feature->activate_subtitle;?></div>
            			<div class="leyka-ext-support-packages">
            			<?php foreach($packages as $package) {
                            $this->show_manage_card($package, ['is_active' => false]);
                        }?>
            			</div>
            			
            			<div class="leyka-ext-sp-terms-action">
                			<div class="leyka-ext-sp-subsription-terms">
                				<?php $support_packages_subscription_text = leyka()->opt('support_packages_subscription_text');?>
                				<?php if($support_packages_subscription_text) {
                				    echo $support_packages_subscription_text;
                				} else {
                				    esc_html_e('Subscription renews automatically. You can unsubscribe at any time in', 'leyka');?> <a href="<?php echo site_url('/donor-account/cancel-subscription/');?>"><?php esc_html_e('your account', 'leyka');?></a>
                				<?php }?>
                			</div>
                			<a href="<?php echo $campaign_post_permalink;?>" class="leyka-ext-sp-subscribe-action"><?php echo leyka()->opt('support_packages_activation_button_label');?></a>
            			</div>
                	</div>

                    <div class="leyka-ext-sp-already-subsribed">
                    	<a href="<?php echo site_url('/donor-account/');?>" class="leyka-ext-sp-already-subscribed-link">
                    		<span class="leyka-ext-sp-already-subscribed-icon"><?php readfile(LEYKA_PLUGIN_DIR.'extensions/'.Leyka_Support_Packages_Extension::get_instance()->id_dash.'/img/person.svg');?></span>
                    		<span class="leyka-ext-sp-already-subscribed-caption"><?php echo leyka()->opt('support_packages_account_link_label');?></span>
                		</a>
                    </div>

                </div>
            </div>

        </div>

        <?php
    }

}

function leyka_add_extension_support_packages() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_extension(Leyka_Support_Packages_Extension::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_extension_support_packages');