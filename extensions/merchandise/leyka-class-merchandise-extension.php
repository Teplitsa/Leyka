<?php if( !defined('WPINC') ) die;
/**
 * Leyka Extension: Merchandise/gifts for donors
 * Version: 1.0
 * Author: Teplitsa of social technologies
 * Author URI: https://te-st.ru
 **/

class Leyka_Merchandise_Extension extends Leyka_Extension {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'merchandise';
        $this->_title = __('Donation rewards', 'leyka');

        // A human-readable short description (for backoffice extensions list page):
        $this->_description = __('The extension allows you to add photos and descriptions of donation rewards to the Leyka form.', 'leyka');

        // A human-readable full description (for backoffice extensions list page):
        $this->_full_description = '';

        // A human-readable description (for backoffice extension settings page):
        $this->_settings_description = __('After activating the extension, an additional section appears in the campaign settings - "Rewards for donations". In it, you can specify the name and description of the reward, as well as add a photo. The reward is related to the size of the donation. The data about the selected reward is saved in the donations table.', 'leyka');

        // A human-readable description of how to enable the main feature (for backoffice extension settings page):
        $this->_connection_description = '';

        $this->_user_docs_link = '//leyka.te-st.ru/docs/merchandise-manual';
        $this->_has_wizard = false;
        $this->_has_color_options = false;

    }

    protected function _set_options_defaults() {

        $this->_options = apply_filters('leyka_'.$this->_id.'_extension_options', [
            ['section' => [
                'name' => $this->_id.'-merchandise_library',
                'title' => __('Donations rewards library', 'leyka'),
                'is_default_collapsed' => false,
                'options' => [
                    'merchandise_library' => [
                        'type' => 'custom_merchandise_library', // Special option type
                        'field_classes' => ['merchandise-settings'],
                        'default' => [],
                    ],
                ],
            ],],
        ]);

    }

    /** Will be called only if the Extension is active. */
    protected function _initialize_active() {

        if(is_admin()) {

            // Campaign metabox:
            add_action('add_meta_boxes', function(){

                add_meta_box(
                    Leyka_Campaign_Management::$post_type.'_merchandise',
                    __('Rewards for donations', 'leyka'),
                    [$this, 'merchandise_campaign_metabox'],
                    Leyka_Campaign_Management::$post_type,
                    'normal',
                    'low'
                );

            });

            // Donations admin list column:
            add_filter('leyka_admin_donations_columns_names', [$this, '_merchandise_admin_donations_list_column_name']);

            add_filter(
                'leyka_admin_donation_merchandise_column_content',
                [$this, '_merchandise_admin_donations_list_column_content'],
                10, 2
            );

            // Donation edit page:
            add_action('leyka_donation_info_data_post_content', [$this, '_merchandise_admin_donation_info']);

            // To save Merchandise data on Campaign saving:
            add_action('leyka_campaign_after_saving', [$this, '_merchandise_campaign_data_saving'], 10, 2);

        }

        // Campaign Merchandise data:

        // To initialize Merchandise data as Campaign meta on object construction:
        add_filter('leyka_campaign_constructor_meta', [$this, '_merchandise_campaign_data_initializing'], 10, 2);

        // To get/set Merchandise settings from Campaign object:
        add_filter('leyka_get_unknown_campaign_field', [$this, '_merchandise_campaign_data_get'], 10, 3);
        add_action('leyka_set_unknown_campaign_field', [$this, '_merchandise_campaign_data_set'], 10, 3);

        // Campaign Merchandise data - END

        // Donation Merchandise data:

        // To initialize Merchandise data as Donation meta on object construction:
        add_filter('leyka_donation_constructor_meta', [$this, '_merchandise_donation_data_initializing'], 10, 2);

        // To get/set Merchandise data from Donation object:
        add_filter('leyka_get_unknown_donation_field', [$this, '_merchandise_donation_data_get'], 10, 3);
        add_action('leyka_set_unknown_donation_field', [$this, '_merchandise_donation_data_set'], 10, 3);

        // To add Merchandise data for new Donations:
        add_filter('leyka_new_donation_specific_data', [$this, '_merchandise_new_donation_data'], 10, 3);

        // To add Merchandise-related placeholders to admin notifications emails:
        add_filter('leyka_email_manager_notification_placeholders', [$this, '_merchandise_manager_emails_placeholders'], 10, 1);
        add_filter(
            'leyka_email_manager_notification_placeholders_values',
            [$this, '_merchandise_manager_emails_placeholders_values'],
            10, 3
        );
        add_filter('leyka_email_placeholders_help_list_content', [$this, '_merchandise_emails_placeholders_help_list']);

        add_filter('leyka_donations_export_headers', [$this, '_merchandise_donations_export_headers']);
        add_filter('leyka_donations_export_line', [$this, '_merchandise_donations_export_line'], 1, 2);

        // Donation Merchandise data - END

        add_action('wp_enqueue_scripts', [$this, 'load_public_scripts']);

        $this->update_extension_data();

        // Add the Merchandise block to public Donation forms:
        add_action('leyka_template_star_after_amount', [$this, 'display_merchandise_field_star'], 2, 10);
        add_action('leyka_template_need-help_after_amount', [$this, 'display_merchandise_field_star'], 2, 10);
        // 'display_merchandise_field_need_help'

    }

    /** Triggers on the main plugin data update routine. WARNING: mb, the method should use $wpdb for plugin options handling.*/
    public function update_extension_data() {

        if( // Data format update - from Merch v.1 (since plugin v.3.19) to Merch v.2 (since plugin v.3.22)
            LEYKA_VERSION
            && version_compare(LEYKA_VERSION, '3.22', '<=')
            && !get_option('leyka_merchandise_updated')
        ) {

            $campaigns_with_merchandise = get_posts([
                'post_type' => Leyka_Campaign_Management::$post_type,
                'post_status' => ['publish','pending', 'draft',],
                'meta_query' => [
                    'key' => 'leyka_campaign_merchandise_settings',
                    'compare' => 'EXISTS',
                ],
                'nopaging' => true,
            ]);

            if( !$campaigns_with_merchandise ) {

                update_option('leyka_merchandise_updated', LEYKA_VERSION);
                return;

            }

            require_once ABSPATH.'wp-admin/includes/file.php';
            require_once ABSPATH.'wp-admin/includes/media.php';
            require_once ABSPATH.'wp-admin/includes/image.php';

            $all_merchandise_items_used = [];

            foreach($campaigns_with_merchandise as $campaign) {

                $campaign = new Leyka_Campaign($campaign);

                $campaign_merchandise_settings_new = [];
                foreach($campaign->merchandise_settings as $merchandise_id => $settings) {

                    if(isset($all_merchandise_items_used[$merchandise_id]) || !is_array($settings)) {
                        continue;
                    }

                    if($settings['thumbnail'] && is_string($settings['thumbnail'])) { // Add the picture to the WP Media library
                        $settings['thumbnail'] = $this->_image_to_media($settings['thumbnail']);
                    }

                    $all_merchandise_items_used[$merchandise_id] = [
                        'title' => $settings['title'],
                        'donation_amount_needed' => $settings['donation_amount_needed'],
                        'description' => $settings['description'],
                        'thumbnail' => $settings['thumbnail'] && !is_wp_error($settings['thumbnail']) ?
                            absint($settings['thumbnail']) : '',
                        'campaigns' => [$campaign->id,],
                        'for_all_campaigns' => '',
                        'campaigns_exceptions' => [],
                    ];

                    $campaign_merchandise_settings_new[] = $merchandise_id;

                }

                $campaign->merchandise_settings = $campaign_merchandise_settings_new;

            }

            leyka_options()->opt('leyka_merchandise_library', $all_merchandise_items_used);

            update_option('leyka_merchandise_updated', LEYKA_VERSION);

        }

    }

    /**
     * A service function used in the Extension data update (Merch v.1 -> v.2) sub-routine.
     *
     * @param string Image URL in the WP content folder.
     * @return int|WP_Error A new Media library entry ID, or WP_Error if something went wrong.
     */
    protected function _image_to_media($image_url) {

        if(absint($image_url)) { // In case image Media ID is somehow already set & passed here
            return absint($image_url);
        }

        $wp_uploads_dir = wp_upload_dir();

        $full_file_url = $wp_uploads_dir['baseurl'].$image_url;
        $file_array = ['name' => wp_basename($full_file_url), 'tmp_name' => download_url($full_file_url)];

        if(is_wp_error($file_array['tmp_name'])) { // If error storing temporarily, return the error
            return $file_array['tmp_name'];
        }

        $image_media_id = media_handle_sideload($file_array, 0, ''); // Do the validation and storage stuff

        if(is_wp_error($image_media_id)) { // If error storing permanently, unlink
            @unlink($file_array['tmp_name']);
        } else { // Media library entry created, delete the old file
            @unlink($wp_uploads_dir['basedir'].$image_url);
        }

        return $image_media_id;

    }

    /** Will be called everytime the Extension is loading into the plugin (i.e. always). */
    protected function _initialize_always() {

        if(is_admin()) {

            add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']);

            // Merchandise library custom option display:
            add_action('leyka_render_custom_merchandise_library', [$this, '_render_merchandise_library_custom_option'], 10, 2);

            // Merchandise library custom option saving:
            add_action('leyka_save_custom_option-merchandise_library', [$this, '_merchandise_library_custom_option_saving']);

        }

    }

    public function load_admin_scripts() {

        if( // Extension CSS & JS is only for admin settings page
            !Leyka_Extension::is_admin_settings_page($this->_id)
            && (empty($_GET['post']) || !absint($_GET['post']) || empty($_GET['action']) || $_GET['action'] !== 'edit')
        ) {
            return;
        }

        if( !did_action('wp_enqueue_media') ) {
            wp_enqueue_media();
        }

        wp_enqueue_script(
            $this->_id.'-admin',
            self::get_base_url().'/assets/js/admin.js',
            ['jquery'],
            defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? uniqid() : null
        );

    }

    public function load_public_scripts() {

        wp_enqueue_style(
            $this->_id.'-front',
            self::get_base_url().'/assets/css/public.css',
            [],
            defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? uniqid() : null
        );

        wp_enqueue_script(
            $this->_id.'-front',
            self::get_base_url().'/assets/js/public.js',
            ['jquery'],
            defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? uniqid() : null
        );

    }

    // Admin functions:

    public function _merchandise_campaign_item_html($is_template = false, $placeholders = []) {

        $placeholders = wp_parse_args($placeholders, [
            'id' => '',
            'box_title' => __('New reward', 'leyka'),
            'title' => '',
            'donation_amount_needed' => false,
            'description' => '',
            'thumbnail' => false,
            'for_all_campaigns' => false,
            'campaign_id' => false,
        ]);

        $merchandise_library = leyka_options()->opt('merchandise_library');

        if($is_template) {

            if($merchandise_library) {

                $merchandise_select_values = ['-' => __('Select the reward', 'leyka'),];

                foreach($merchandise_library as $merchandise_id => $settings) {

                    // If Merchandise item is excluded for the current Campaign, disable its option:
                    if(
                        !empty($settings['campaigns_exceptions'])
                        && is_array($settings['campaigns_exceptions'])
                        && in_array($placeholders['campaign_id'], $settings['campaigns_exceptions'])
                    ) {

                        $merchandise_select_values[$merchandise_id] = [
                            'option_label' => '['.leyka_format_amount($settings['donation_amount_needed'])
                                .'&nbsp;'.leyka_get_currency_label().']&nbsp;'
                                .$settings['title'].'&nbsp;('.__('excluded for this campaign', 'leyka').')',
                            'disabled' => true,
                        ];

                    } else {

                        $merchandise_select_values[$merchandise_id] =
                            '['.leyka_format_amount($settings['donation_amount_needed'])
                            .'&nbsp;'.leyka_get_currency_label().']&nbsp;'
                            .$settings['title'];

                    }

                }

            }

            $merchandise_select_values['+'] = __('+ Create a new reward', 'leyka');?>

            <div id="item-<?php echo leyka_get_random_string(4);?>" class="multi-valued-item-box merchandise-box <?php echo $is_template ? 'item-template' : '';?>" <?php echo $is_template ? 'style="display: none;"' : '';?>>

                <h3 class="item-box-title ui-sortable-handle">
                    <span class="draggable"></span>
                    <span class="title" data-empty-box-title="<?php _e('New reward', 'leyka');?>">
                        <?php echo esc_html($placeholders['box_title']);?>
                    </span>
                </h3>

                <div class="box-content">

                    <div class="single-line">

                        <div class="option-block type-select">
                            <div class="leyka-select-field-wrapper">
                                <?php leyka_render_select_field('campaign_merchandise_add', [
                                    'title' => __('Rewards available', 'leyka'),
                                    'type' => 'select',
                                    'value' => count($merchandise_select_values) > 1 ? '-' : '+',
                                    'required' => true,
                                    'list_entries' => $merchandise_select_values,
                                    'hide_title' => true,
                                    'field_classes' => ['leyka-campaign-item-add-wrapper'],
                                ]);?>
                            </div>
                            <div class="field-errors"></div>
                        </div>

                    </div>

                    <div class="leyka-campaign-new-merchandise leyka-campaign-new-item-subfields" style="display: none;">
                        <?php $this->_render_merchandise_library_main_subfields_html();?>
                    </div>

                    <ul class="notes-and-errors">
                        <li class="any-field-note"><?php _e('When you upload or choose an image for reward, please use a picture at least 536 pixels wide.', 'leyka');?></li>
                    </ul>

                    <div class="box-footer">
                        <div class="remove-campaign-merchandise delete-item">
                            <?php _e('Remove the reward from this campaign', 'leyka');?>
                        </div>
                    </div>

                </div>

            </div>

        <?php } else { // An existing Merchandise item ?>

            <div id="<?php echo $placeholders['id'] ? : 'item-'.leyka_get_random_string(4);?>" class="multi-valued-item-box merchandise-box closed">

                <h3 class="item-box-title ui-sortable-handle">

                    <span class="draggable"></span>
                    <span class="title">
                        <?php echo '['
                            .leyka_format_amount($placeholders['donation_amount_needed'])
                            .'&nbsp;'.leyka_get_currency_label().']&nbsp;'
                            .esc_html($placeholders['box_title']);?>
                    </span>

                </h3>

                <div class="box-content">

                    <ul class="notes-and-errors">

                        <li class="edit-field-note">
                            <?php echo sprintf(
                                __('If you wish to edit the reward settings, you may do it in <a href="%s" target="_blank">rewards library</a>.', 'leyka'),
                                admin_url('admin.php?page=leyka_settings&stage=extensions&extension=merchandise#leyka_merchandise-merchandise_library')
                            );?>
                        </li>

                        <?php if($placeholders['for_all_campaigns']) {?>
                            <li class="no-delete-for-all-campaigns-items-note">
                                <?php echo sprintf(
                                    __('The reward cannot be removed from the campaign - it is marked "for all campaigns" in the <a href="%s" target="_blank">donations rewards library</a>.', 'leyka'),
                                    admin_url('admin.php?page=leyka_settings&stage=extensions&extension=merchandise#leyka_merchandise-merchandise_library')
                                );?>
                            </li>
                        <?php }?>

                    </ul>

                    <?php if( !$placeholders['for_all_campaigns']) {?>
                        <div class="box-footer">
                            <div class="remove-campaign-merchandise delete-item">
                                <?php _e('Remove the reward from this campaign', 'leyka');?>
                            </div>
                        </div>
                    <?php }?>

                </div>

            </div>

        <?php }

    }

    public function merchandise_campaign_metabox(WP_Post $campaign) {

        $campaign = new Leyka_Campaign($campaign);?>

        <div class="leyka-admin leyka-settings-page">

            <div class="leyka-options-section">

                <div class="leyka-campaign-merchandise-wrapper multi-valued-items-field-wrapper">

                    <div class="leyka-main-multi-items leyka-main-merchandise" data-min-items="0" data-max-items="<?php echo 30;?>" data-item-inputs-names-prefix="leyka_campaign_merchandise_" data-show-new-item-if-empty="0">

                        <?php // Display existing Campaign Merchandise items (the assoc. array keys order is important):
                        foreach(self::get_calculated_merchandise_settings($campaign) as $merchandise_id => $settings) {

                            $this->_merchandise_campaign_item_html(false, [
                                'id' => $merchandise_id,
                                'box_title' => $settings['title'],
                                'title' => $settings['title'],
                                'donation_amount_needed' => $settings['donation_amount_needed'],
                                'thumbnail' => $settings['thumbnail'],
                                'description' => $settings['description'],
                                'for_all_campaigns' => $settings['for_all_campaigns'],
                                'campaign_id' => $campaign->id,
                            ]);

                        }?>

                    </div>


                    <?php $this->_merchandise_campaign_item_html(true, ['campaign_id' => $campaign->id,]); // Item box template ?>

                    <div class="add-merchandise add-item bottom"><?php _e('Add reward', 'leyka');?></div>

                    <input type="hidden" class="leyka-items-options" name="leyka_campaign_merchandise" value="">

                </div>

            </div>
        </div>

        <?php
    }

    // Merchandise library (special multi-valued item box option) - items common fields:
    protected function _render_merchandise_library_main_subfields_html(array $placeholders = []) {

        $placeholders = wp_parse_args($placeholders, [
            'title' => '',
            'donation_amount_needed' => 0,
            'thumbnail' => false,
            'description' => '',
        ]);?>

        <div class="single-line">

            <div class="option-block type-text">

                <div class="leyka-text-field-wrapper">

                    <?php leyka_render_text_field('merchandise_title', [
                        'title' => __('Reward title', 'leyka'),
                        'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('A cool hat with our logo', 'leyka')),
                        'value' => $placeholders['title'],
                        'required' => true,
                    ]);?>
                </div>

                <div class="field-errors"></div>

            </div>

        </div>

        <div class="single-line">

            <div class="option-block type-number">

                <div class="leyka-number-field-wrapper">
                    <?php leyka_render_number_field('merchandise_donation_amount_needed', [
                        'title' => sprintf(
                            __('Donations amount needed for the reward, %s', 'leyka'),
                            leyka_get_currency_label()
                        ),
                        'required' => true,
                        'value' => absint($placeholders['donation_amount_needed']) ? : 1000,
                        'length' => 6,
                        'min' => 1,
                        'max' => 9999999,
                    ]);?>
                </div>

                <div class="field-errors"></div>

            </div>

            <div class="settings-block option-block type-file type-media-upload">

                <?php leyka_render_media_upload_field('merchandise_thumbnail', [
                    'title' => __('Reward picture', 'leyka'),
                    'upload_label' => __('Upload picture', 'leyka'),
                    'upload_title' => __('Select a picture for the reward', 'leyka'),
                    'upload_button_label' => __('Use the picture', 'leyka'),
                    'required' => false,
                    'value' => $placeholders['thumbnail'],
                    'comment' => __('For reward thumbnail, please, use a picture <strong>at least 536 pixels wide</strong>.', 'leyka'),
                ]);?>

                <div class="field-errors"></div>

            </div>

        </div>

        <div class="single-line">

            <div class="settings-block option-block type-html">
                <?php leyka_render_textarea_field('merchandise_description', [
                    'title' => __('Description text', 'leyka'),
                    'value' => $placeholders['description'],
                    'required' => false,
                ]);?>
                <div class="field-errors"></div>
            </div>

        </div>

    <?php }

    protected function _render_merchandise_item_html($is_template = false, $placeholders = []) {

        $placeholders = wp_parse_args($placeholders, [
            'id' => '',
            'box_title' => __('New reward', 'leyka'),
            'title' => '',
            'description' => '',
            'donation_amount_needed' => 0,
            'thumbnail' => false,
            'campaigns' => [],
            'for_all_campaigns' => false,
            'campaigns_exceptions' => [],
        ]);

        $_COOKIE['leyka-merchandise-boxes-closed'] = empty($_COOKIE['leyka-merchandise-boxes-closed']) ?
            [] : json_decode(stripslashes('[\"someline\"]'));?>

        <div id="<?php echo $is_template || !$placeholders['id'] ? 'item-'.leyka_get_random_string(4) : $placeholders['id'];?>" class="multi-valued-item-box field-box <?php echo $is_template ? 'item-template' : '';?> <?php echo !$is_template && !empty($_COOKIE['leyka-merchandise-boxes-closed']) && !empty($placeholders['id']) && in_array($placeholders['id'], $_COOKIE['leyka-merchandise-boxes-closed']) ? 'closed' : '';?>" style="<?php echo $is_template ? 'display: none;' : '';?>">

            <h3 class="item-box-title ui-sortable-handle">
                <span class="draggable"></span>
                <span class="title" data-empty-box-title="<?php _e('New reward', 'leyka');?>">
                    <?php echo esc_html($placeholders['box_title']);?>
                </span>
            </h3>

            <div class="box-content">

                <?php $this->_render_merchandise_library_main_subfields_html($placeholders);

                leyka_multi_valued_item_campaign_subfields_html([
                    'item_campaigns' => $placeholders['campaigns'],
                    'item_campaigns_field_title' => __('Campaigns that will use the reward', 'leyka'),

                    'item_for_all_campaigns' => !!$placeholders['for_all_campaigns'],
                    'item_for_all_campaigns_field_title' => __('The reward is for all campaigns by default', 'leyka'),

                    'item_campaigns_exceptions' => $placeholders['campaigns_exceptions'],
                    'item_campaigns_exceptions_field_title' => __('Campaigns that will NOT use the reward', 'leyka'),
                ]);?>

                <ul class="notes-and-errors">
                    <li class="any-field-note"><?php _e('For reward thumbnail, please, use a picture <strong>at least 536 pixels wide</strong>.', 'leyka');?></li>
                </ul>

                <div class="box-footer">
                    <div class="remove-campaign-merchandise delete-item">
                        <?php _e('Remove the reward', 'leyka');?>
                    </div>
                </div>

            </div>

        </div>

    <?php }

    public function _render_merchandise_library_custom_option($option_id, $data = []) {

        $option_id = mb_stristr($option_id, 'leyka_') ? $option_id : 'leyka_'.$option_id;
        $data = $data ? : leyka_options()->get_info_of($option_id);?>

        <div id="<?php echo $option_id.'-wrapper';?>" class="leyka-<?php echo $option_id;?>-field-wrapper multi-valued-items-field-wrapper <?php echo empty($data['field_classes']) || !is_array($data['field_classes']) ? '' : implode(' ', $data['field_classes']);?>">

            <div class="leyka-main-multi-items leyka-main-merchandise" data-max-items="" data-min-items="0" data-items-cookie-name="leyka-merchandise-boxes-closed" data-item-inputs-names-prefix="leyka_merchandise_" data-show-new-item-if-empty="1">

                <?php $data['value'] = empty($data['value']) || !is_array($data['value']) ?
                    leyka_options()->opt('merchandise_library') :
                    $data['value'];

                // Display existing common fields (the assoc. array keys order is important):
                if($data['value'] && is_array($data['value'])) {
                    foreach($data['value'] as $item_id => $item) {

                        $this->_render_merchandise_item_html(false, [
                            'id' => $item_id,
                            'box_title' => $item['title'],
                            'title' => $item['title'],
                            'description' => empty($item['description']) ? '' : $item['description'],
                            'donation_amount_needed' => $item['donation_amount_needed'],
                            'thumbnail' => $item['thumbnail'],
                            'campaigns' => empty($item['campaigns']) ? [] : $item['campaigns'],
                            'for_all_campaigns' => !empty($item['for_all_campaigns']),
                            'campaigns_exceptions' => empty($item['campaigns_exceptions']) ?
                                [] : $item['campaigns_exceptions'],
                        ]);
                    }
                }?>

            </div>

            <?php $this->_render_merchandise_item_html(true); // Merchandise item box template ?>

            <div class="add-reward add-item bottom"><?php _e('Add reward', 'leyka');?></div>

            <input type="hidden" class="leyka-items-options" name="leyka_merchandise_library" value="">

        </div>

        <?php
    }

    public function _merchandise_library_custom_option_saving() {

        $_POST['leyka_merchandise_library'] = json_decode(urldecode($_POST['leyka_merchandise_library']));
        $result = [];

        foreach($_POST['leyka_merchandise_library'] as $item) {

            $item->id = mb_stripos($item->id, 'item-') === false || empty($item->title) ?
                $item->id :
                trim(preg_replace('~[^-a-z0-9_]+~u', '-', mb_strtolower(leyka_cyr2lat($item->title))), '-');

            $result[$item->id] = [
                'title' => $item->title,
                'donation_amount_needed' => absint($item->donation_amount_needed),
                'thumbnail' => $item->thumbnail,
                'description' => $item->description,
                'campaigns' => $item->campaigns,
                'for_all_campaigns' => $item->leyka_item_for_all_campaigns,
                'campaigns_exceptions' => $item->campaigns_exceptions,
            ];

            if($item->campaigns && !$item->leyka_item_for_all_campaigns) {
                foreach($item->campaigns as $campaign_id) { // "Add item to Campaign" links to selected Campaigns

                    if( !$campaign_id ) {
                        continue;
                    }

                    $campaign = new Leyka_Campaign($campaign_id);
                    $campaign_merchandise = $campaign->merchandise_settings;

                    // Either no Campaigns were set, or Merchandise item is already in Campaign items list:
                    if( !is_array($campaign_merchandise) || in_array($item->id, $campaign_merchandise) ) {
                        continue;
                    }

                    $campaign_merchandise[] = $item->id;
                    $campaign->merchandise_settings = $campaign_merchandise;

                }
            }

        }

        leyka_options()->opt('merchandise_library', $result);

    }

    public function _merchandise_campaign_data_saving($campaign_data, Leyka_Campaign $campaign) {

        if( !is_array($campaign_data) || !isset($campaign_data['leyka_campaign_merchandise']) ) {
            return;
        }

        $campaign_data['leyka_campaign_merchandise'] = json_decode(urldecode($campaign_data['leyka_campaign_merchandise']));

        $updated_merchandise_settings = [];
        $merchandise_library = leyka_options()->opt('merchandise_library');

        foreach($campaign_data['leyka_campaign_merchandise'] as $merchandise) {

            if( !empty($merchandise->add) && $merchandise->add === '+' ) { // Totally new Merchandise - 1st, add it to the Library

                if(
                    empty($merchandise->leyka_merchandise_title)
                    || empty($merchandise->leyka_merchandise_donation_amount_needed)
                ) {
                    continue;
                }

                $merchandise->id = mb_stripos($merchandise->id, 'item-') === false ?
                    $merchandise->id :
                    trim(
                        preg_replace(
                            '~[^-a-z0-9_]+~u',
                            '-',
                            mb_strtolower(leyka_cyr2lat($merchandise->leyka_merchandise_title))
                        ),
                        '-'
                    );

                if( !isset($merchandise_library[$merchandise->id]) ) {
                    $merchandise_library[$merchandise->id] = [
                        'title' => $merchandise->leyka_merchandise_title,
                        'donation_amount_needed' => $merchandise->leyka_merchandise_donation_amount_needed,
                        'thumbnail' => $merchandise->leyka_merchandise_thumbnail,
                        'description' => $merchandise->leyka_merchandise_description,
                        'campaigns' => [$campaign->id], // By default, new Merchandise is just for the currectly edited Campaign
                        'for_all_campaigns' => false,
                    ];
                }

                $merchandise->add = $merchandise->id;

            } else if( // Extisting (in the Library) merchandise is added to the Campaign settings
                mb_stristr($merchandise->id, 'item-') !== false
                && $merchandise->add
                && isset($merchandise_library[$merchandise->add])
                && !in_array($campaign->id, $merchandise_library[$merchandise->add]['campaigns'])
            ) {
                $merchandise_library[$merchandise->add]['campaigns'][] = $campaign->id;
            }

            // $merchandise->add or $merchandise->id is a merchandise ID/slug:
            $updated_merchandise_settings[] = $merchandise->add ? : $merchandise->id;

        }

        if(leyka_options()->opt('merchandise_library') != $merchandise_library) {
            leyka_options()->opt('merchandise_library', $merchandise_library);
        }

        if($updated_merchandise_settings != $campaign->merchandise_settings) {

            // If some Merchandise are removed from Campaign, remove the Campaign ID from their settings in the Library:
            $merchandise_removed = false;
            foreach($campaign->merchandise_settings as $merchandise_id) {

                if( !in_array($merchandise_id, $updated_merchandise_settings) ) { // The merchandise is removed

                    $merchandise_removed = true;
                    $campaign_key_in_array = array_search($campaign->id, $merchandise_library[$merchandise_id]['campaigns']);

                    if( !empty($merchandise_library[$merchandise_id]['campaigns'][$campaign_key_in_array]) ) {
                        unset($merchandise_library[$merchandise_id]['campaigns'][$campaign_key_in_array]);
                    }

                }

            }

            if($merchandise_removed) {
                leyka_options()->opt('merchandise_library', $merchandise_library);
            }

            $campaign->merchandise_settings = $updated_merchandise_settings;

        }

    }

    public function _merchandise_admin_donations_list_column_name($columns){

        $columns['merchandise'] = __('Donation reward', 'leyka');

        return $columns;

    }

    public function _merchandise_admin_donations_list_column_content($content, Leyka_Donation_Base $donation){

        $merchandise_library = leyka_options()->opt('merchandise_library');

        if($donation->merchandise_id && !empty($merchandise_library[$donation->merchandise_id])) {
            $content = $merchandise_library[$donation->merchandise_id]['title'];
        }

        return $content;

    }

    public function _merchandise_admin_donation_info(Leyka_Donation_Base $donation){

        $merchandise_library = leyka_options()->opt('merchandise_library');

        if($donation->merchandise_id && !empty($merchandise_library[$donation->merchandise_id])) {
            $content = $merchandise_library[$donation->merchandise_id]['title'];
        } else {
            $content = __('none', 'leyka');
        }?>

        <div class="leyka-ddata-string">
            <label><?php _e('Donation reward', 'leyka');?>:</label>
            <div class="leyka-ddata-field">
                <span class="fake-input"><?php echo $content;?></span>
            </div>
        </div>

        <?php
    }

    // Donations export:
    public function _merchandise_donations_export_headers(array $export_headers) {

        $export_headers[] = __('Donation reward', 'leyka');

        return $export_headers;

    }

    public function _merchandise_donations_export_line(array $export_line, Leyka_Donation_Base $donation) {

        $merchandise_library = leyka_options()->opt('merchandise_library');

        $export_line[] = $donation->merchandise_id && !empty($merchandise_library[$donation->merchandise_id]) ?
            $merchandise_library[$donation->merchandise_id]['title'] : '';

        return $export_line;

    }
    // Donations export - END
    // Admin functions - END

    // Campaign Merchandise data handling methods:
    public function _merchandise_campaign_data_initializing(array $campaign_meta, $campaign_id) {

        if( !$campaign_id ) {
            return $campaign_meta;
        }

        $campaign_meta['merchandise_settings'] = get_post_meta($campaign_id, 'leyka_campaign_merchandise_settings', true);
        $campaign_meta['merchandise_settings'] = $campaign_meta['merchandise_settings'] ?
            maybe_unserialize($campaign_meta['merchandise_settings']) : [];

        return $campaign_meta;

    }

    public function _merchandise_campaign_data_get($value, $field_name, Leyka_Campaign $campaign) {

        if( !in_array($field_name, ['merchandise', 'merchandise_settings']) || !$campaign->id ) {
            return $value;
        }

        $res = get_post_meta($campaign->id, 'leyka_campaign_merchandise_settings', true);

        return $res ? : [];

    }

    public function _merchandise_campaign_data_set($field_name, $value, Leyka_Campaign $campaign) {

        if( !in_array($field_name, ['merchandise', 'merchandise_settings']) || !$campaign->id ) {
            return;
        }

        // Can't set $campaign->_campaign_meta['merchandise_settings'] here - it's a protected attribute.
        /** @todo Make a method like $campaign->refresh_meta($meta_name), to load a meta value from DB anew, and call it here. */
        update_post_meta($campaign->id, 'leyka_campaign_merchandise_settings', $value);

    }
    // Campaign merchandise data handling methods - END

    // Donation merchandise data handling methods:
    public function _merchandise_donation_data_initializing(array $donation_meta, $donation_id) {

        if( !$donation_id ) {
            return $donation_meta;
        }

        $donation_meta['merchandise_id'] = Leyka_Donations::get_instance()->get_donation_meta($donation_id, 'merchandise_id');

        return $donation_meta;

    }

    public function _merchandise_donation_data_get($value, $field_name, Leyka_Donation_Base $donation) {

        if( !in_array($field_name, ['merchandise', 'merchandise_id']) || !$donation->id ) {
            return $value;
        }

        return Leyka_Donations::get_instance()->get_donation_meta($donation->id, 'merchandise_id');

    }

    public function _merchandise_donation_data_set($field_name, $value, Leyka_Donation_Base $donation) {

        if( !in_array($field_name, ['merchandise', 'merchandise_id']) || !$donation->id ) {
            return;
        }

        // Can't set $donation->_donation_meta['merchandise_id'] here - it's a protected attribute.
        /** @todo Make a method like $donation->refresh_meta($meta_name), to load a meta value from DB anew, and call it here. */
        Leyka_Donations::get_instance()->set_donation_meta($donation->id, 'merchandise_id', $value);

    }

    public function _merchandise_new_donation_data($donation_meta_fields, $donation_id, $params) {

        if(empty($_POST['leyka_donation_merchandise_id'])) {
            return $donation_meta_fields;
        }

        $_POST['leyka_donation_merchandise_id'] = esc_sql($_POST['leyka_donation_merchandise_id']);

        $campaign = new Leyka_Campaign($params['campaign_id']);
        $campaign_merchandise_settings = self::get_calculated_merchandise_settings($campaign);

        if( $campaign_merchandise_settings && !empty($campaign_merchandise_settings[$_POST['leyka_donation_merchandise_id']]) ) {
            $donation_meta_fields['merchandise_id'] = $_POST['leyka_donation_merchandise_id'];
        }

        return $donation_meta_fields;

    }
    // Donation merchandise data handling methods - END

    // Emails placeholders:
    public function _merchandise_manager_emails_placeholders(array $placeholders){

        array_push($placeholders, '#DONATION_MERCHANDISE_TITLE#', '#DONATION_MERCHANDISE_AMOUNT#');

        return $placeholders;

    }

    public function _merchandise_manager_emails_placeholders_values(array $placeholders_values, array $placeholders, Leyka_Donation_Base $donation) {

        $campaign = $donation->campaign;
        $merchandise_library = leyka_options()->opt('merchandise_library');

        $merchandise_title = isset($donation->merchandise_id)
            && !empty($merchandise_library[$donation->merchandise_id])
            && is_array($campaign->merchandise_settings)
            && in_array($donation->merchandise_id, $campaign->merchandise_settings) ?
                $merchandise_library[$donation->merchandise_id]['title'] : '';

        $merchandise_donation_amount_needed = isset($donation->merchandise_id)
            && !empty($merchandise_library[$donation->merchandise_id])
            && is_array($campaign->merchandise_settings)
            && in_array($donation->merchandise_id, $campaign->merchandise_settings) ?
                $merchandise_library[$donation->merchandise_id]['donation_amount_needed'] : '';

        array_push($placeholders_values, $merchandise_title, $merchandise_donation_amount_needed);

        return $placeholders_values;

    }

    public function _merchandise_emails_placeholders_help_list($placeholders_list_content) {

        $placeholders_list_content .= '<span class="item">
        <code>#DONATION_MERCHANDISE_TITLE#</code><span class="description">Название выбранной награды за пожертвование</span>
    </span>
    <span class="item">
        <code>#DONATION_MERCHANDISE_AMOUNT#</code><span class="description">Минимальная сумма для выбранной награды за пожертвование</span>
    </span>';

        return $placeholders_list_content;

    }

    /**
     * Get all Campaign Merchandise items with their settings.
     *
     * @return array Assoc. array of Campaign Merchandise (in correct order) in the form of merchandise_id => settings.
     */
    public static function get_calculated_merchandise_settings(Leyka_Campaign $campaign) {

        $merchandise_library = leyka_options()->opt('merchandise_library');
        $campaign_merchandise = [];

        foreach($campaign->merchandise_settings as $merchandise_id) {

            // Merchandise is in Campaign settings, but not in the Library - mb, it was deleted from there:
            if( !is_string($merchandise_id) || empty($merchandise_library[$merchandise_id]) ) {
                continue;
            }

            // Merchandise is in Campaign settings & in the Library, but the Library doesn't have the current Campaign set for it:
            if(
                !$merchandise_library[$merchandise_id]['for_all_campaigns']
                && (
                    !is_array($merchandise_library[$merchandise_id]['campaigns'])
                    || !in_array($campaign->id, $merchandise_library[$merchandise_id]['campaigns'])
                )
            ) {
                continue;
            }

            if( // Merchandise is "for all Campaigns", but the current Campaign has been excluded for it
                $merchandise_library[$merchandise_id]['for_all_campaigns']
                && $merchandise_library[$merchandise_id]['campaigns_exceptions']
                && is_array($merchandise_library[$merchandise_id]['campaigns_exceptions'])
                && in_array($campaign->id, $merchandise_library[$merchandise_id]['campaigns_exceptions'])
            ) {
                continue;
            }

            $campaign_merchandise[$merchandise_id] = $merchandise_library[$merchandise_id];

        }

        // Include the Merchandise "for all Campaigns", if they aren't already in the Campaign Merchandise settings
        // (and they aren't excluded for the current Campaign in their own Merchandise settings):
        foreach($merchandise_library as $merchandise_id => $settings) {

            if(
                empty($settings['for_all_campaigns'])
                || (
                    $settings['campaigns_exceptions']
                    && is_array($settings['campaigns_exceptions'])
                    && in_array($campaign->id, $settings['campaigns_exceptions'])
                )
                || !empty($campaign_merchandise[$merchandise_id])
            ) {
                continue;
            }

            $campaign_merchandise[$merchandise_id] = $settings;

        }

        return $campaign_merchandise;

    }

    public function display_merchandise_field_star(array $template_data, Leyka_Campaign $campaign) {?>

        <div class="section section--merchandise">

            <div class="section-title-container">
                <div class="section-title-line"></div>
                <div class="section-title-text" role="heading" aria-level="3"><?php _e('Donation reward', 'leyka');?></div>
            </div>

            <div class="section__fields merchandise-grid">

                <ul class="merchandise-swiper"><!-- Will be filled with JS --></ul>

                <ul class="merchandise-swiper-not-usable-slides" style="display: none;">
                <?php foreach(self::get_calculated_merchandise_settings($campaign) as $merchandise_id => $settings) {?>
                    <li class="merchandise-item" data-merchandise-id="<?php echo esc_attr($merchandise_id);?>" data-donation-amount-needed="<?php echo absint($settings['donation_amount_needed']);?>">

                        <h3 class="merchandise-label"><?php echo esc_html($settings['title']);?></h3>

                        <?php if($settings['thumbnail']) {?>

                            <img class="merchandise-image" src="<?php echo wp_get_attachment_image_url($settings['thumbnail'], 'medium_large');?>" alt="<?php echo esc_attr($settings['title']);?>">

                        <?php }

                        if($settings['description']) {?>
                            <div class="merchandise-description"><?php echo nl2br($settings['description']);?></div>
                        <?php }?>

                    </li>
                <?php }?>
                </ul>

            </div>

            <input type="hidden" name="leyka_donation_merchandise_id" value="">

        </div>

    <?php }

}

function leyka_add_extension_merchandise() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_extension(Leyka_Merchandise_Extension::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_extension_merchandise');