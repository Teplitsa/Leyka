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
            // No options for this Extension yet
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

        }

        // Campaign merchandise data:

        // To initialize merchandise data as Campaign meta on object construction:
        add_filter('leyka_campaign_constructor_meta', [$this, '_merchandise_campaign_data_initializing'], 10, 2);

        // To get/set merchandise settings from Campaign object:
        add_filter('leyka_get_unknown_campaign_field', [$this, '_merchandise_campaign_data_get'], 10, 3);
        add_action('leyka_set_unknown_campaign_field', [$this, '_merchandise_campaign_data_set'], 10, 3);

        // To save merchandise data on Campaign saving:
        add_action('leyka_campaign_data_after_saving', [$this, '_merchandise_campaign_data_saving'], 10, 2);

        // Campaign merchandise data - END

        // Donation merchandise data:

        // To initialize merchandise data as Donation meta on object construction:
        add_filter('leyka_donation_constructor_meta', [$this, '_merchandise_donation_data_initializing'], 10, 2);

        // To get/set merchandise data from Donation object:
        add_filter('leyka_get_unknown_donation_field', [$this, '_merchandise_donation_data_get'], 10, 3);
        add_action('leyka_set_unknown_donation_field', [$this, '_merchandise_donation_data_set'], 10, 3);

        // To add merchandise data for new Donations:
        add_filter('leyka_new_donation_specific_data', [$this, '_merchandise_new_donation_data'], 10, 3);

        // To add merchandise-related placeholders to admin notifications emails:
        add_filter('leyka_email_manager_notification_placeholders', [$this, '_merchandise_manager_emails_placeholders'], 10, 1);
        add_filter(
            'leyka_email_manager_notification_placeholders_values',
            [$this, '_merchandise_manager_emails_placeholders_values'],
            10, 3
        );
        add_filter('leyka_email_placeholders_help_list_content', [$this, '_merchandise_emails_placeholders_help_list']);

        add_filter('leyka_donations_export_headers', [$this, '_merchandise_donations_export_headers']);
        add_filter('leyka_donations_export_line', [$this, '_merchandise_donations_export_line'], 1, 2);

        // Donation merchandise data - END

        add_action('wp_enqueue_scripts', [$this, 'load_public_scripts']);

        // Add the merchandise block to public Donation forms:
        add_action('leyka_template_star_after_amount', [$this, 'display_merchandise_field_star'], 2, 10);
//        add_action('leyka_template_need_help_after_amount', [$this, 'display_merchandise_field_need_help'], 2, 10);

    }

    /** Will be called everytime the Extension is loading into the plugin (i.e. always). */
    protected function _initialize_always() {
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
    public function merchandise_campaign_metabox(WP_Post $campaign) {

        $campaign = new Leyka_Campaign($campaign);?>

        <div class="leyka-admin leyka-settings-page">

            <div class="leyka-options-section">

                <?php function leyka_campaign_merchandise_field_html($is_template = false, $placeholders = []) {

                    $placeholders = wp_parse_args($placeholders, [
                        'id' => '',
                        'box_title' => __('New reward', 'leyka'),
                        'title' => '',
                        'description' => false,
                        'donation_amount_needed' => 0,
                        'thumbnail' => false,
                    ]);?>

                    <div id="<?php echo $is_template || !$placeholders['id'] ? 'item-'.leyka_get_random_string(4) : $placeholders['id'];?>" class="multi-valued-item-box field-box <?php echo $is_template ? 'item-template' : '';?>" style="<?php echo $is_template ? 'display: none;' : '';?>">

                        <h3 class="item-box-title ui-sortable-handle">
                            <span class="draggable"></span>
                            <span class="title" data-empty-box-title="<?php _e('New reward', 'leyka');?>">
                                <?php echo esc_html($placeholders['box_title']);?>
                            </span>
                        </h3>

                        <div class="box-content">

                            <div class="single-line">

                                <div class="option-block type-text">
                                    <div class="leyka-select-field-wrapper">
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

                                <div class="settings-block option-block type-file">
                                    <?php leyka_render_file_field('merchandise_thumbnail', [
                                        'upload_label' => __('Load picture', 'leyka'),
                                        'required' => false,
                                        'value' => $placeholders['thumbnail'],
                                    ]);?>
                                    <div class="field-errors"></div>
                                </div>

                            </div>

                            <div class="single-line">

                                <div class="settings-block option-block type-html">
                                    <?php leyka_render_html_field('merchandise_description', [
                                        'title' => __('Description text', 'leyka'),
                                        'value' => $placeholders['description'],
                                        'required' => false,
                                    ]);?>
                                    <div class="field-errors"></div>
                                </div>

                            </div>

                            <ul class="notes-and-errors">
                            </ul>

                            <div class="box-footer">
                                <div class="remove-campaign-merchandise delete-item">
                                    <?php _e('Remove the reward from this campaign', 'leyka');?>
                                </div>
                            </div>

                        </div>

                    </div>

                <?php }?>

                <div class="leyka-campaign-merchandise-wrapper multi-valued-items-field-wrapper">

                    <div class="leyka-main-multi-items leyka-main-merchandise" data-min-items="0" data-max-items="<?php echo 20;?>" data-item-inputs-names-prefix="leyka_campaign_merchandise_" data-show-new-item-if-empty="0">

                        <?php // Display existing campaign merchandise (the assoc. array keys order is important):
                        foreach($campaign->merchandise_settings as $item_id => $item) {

                            // Field is in Campaign settings, but not in the Library - mb, it was deleted from there:
                            if( !$item_id ) {
                                continue;
                            }

                            leyka_campaign_merchandise_field_html(false, [
                                'id' => $item_id,
                                'box_title' => $item['title'],
                                'title' => $item['title'],
                                'description' => $item['description'],
                                'donation_amount_needed' => $item['donation_amount_needed'],
                                'thumbnail' => $item['thumbnail'],
                            ]);

                        }?>

                    </div>

                    <?php leyka_campaign_merchandise_field_html(true); // Merchandise box template ?>

                    <div class="add-field add-item bottom"><?php _e('Add reward', 'leyka');?></div>

                    <input type="hidden" class="leyka-items-options" name="leyka_campaign_merchandise" value="">

                </div>

            </div>
        </div>

        <?php
    }

    public function _merchandise_campaign_data_saving($campaign_data, Leyka_Campaign $campaign) {

        if( !is_array($campaign_data) ) {
            return;
        }

        $campaign_data['leyka_campaign_merchandise'] = json_decode(urldecode($campaign_data['leyka_campaign_merchandise']));

        $merchandise_data = [];
        foreach($campaign_data['leyka_campaign_merchandise'] as $item) {

            $item->id = mb_stripos($item->id, 'item-') === false || empty($item->leyka_merchandise_title) ?
                $item->id :
                trim(preg_replace('~[^-a-z0-9_]+~u', '-', mb_strtolower(leyka_cyr2lat($item->leyka_merchandise_title))), '-');

            if( !$item->leyka_merchandise_title || !$item->leyka_merchandise_donation_amount_needed ) {
                continue;
            }

            $merchandise_data[$item->id] = [
                'title' => $item->leyka_merchandise_title,
                'description' => $item->leyka_merchandise_description,
                'donation_amount_needed' => $item->leyka_merchandise_donation_amount_needed,
                'thumbnail' => $item->leyka_merchandise_thumbnail,
            ];

        }

        $campaign->merchandise_settings = $merchandise_data;

    }

    public function _merchandise_admin_donations_list_column_name($columns){

        $columns['merchandise'] = __('Donation reward', 'leyka');

        return $columns;

    }

    public function _merchandise_admin_donations_list_column_content($content, Leyka_Donation_Base $donation){

        $campaign = $donation->campaign; /** @var $campaign Leyka_Campaign */
        $campaign_merchandise_settings = $campaign->merchandise_settings;

        if($donation->merchandise && !empty($campaign_merchandise_settings[$donation->merchandise])) {
            $content = $campaign_merchandise_settings[$donation->merchandise]['title'];
        }

        return $content;

    }

    public function _merchandise_admin_donation_info(Leyka_Donation_Base $donation){

        $campaign = $donation->campaign; /** @var $campaign Leyka_Campaign */
        $campaign_merchandise_settings = $campaign->merchandise_settings;

        if($donation->merchandise && !empty($campaign_merchandise_settings[$donation->merchandise])) {
            $content = $campaign_merchandise_settings[$donation->merchandise]['title'];
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

        $campaign = $donation->campaign; /** @var $campaign Leyka_Campaign */
        $campaign_merchandise_settings = $campaign->merchandise_settings;

        $export_line[] = $donation->merchandise && !empty($campaign_merchandise_settings[$donation->merchandise]) ?
            $campaign_merchandise_settings[$donation->merchandise]['title'] : '';

        return $export_line;

    }
    // Donations export - END
    // Admin functions - END

    // Campaign merchandise data handling methods:
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

        $campaign = new Leyka_Campaign($params['campaign_id']);

        if(
            isset($_POST['leyka_donation_merchandise_id'])
            && !empty($campaign->merchandise_settings[$_POST['leyka_donation_merchandise_id']])
        ) {
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

        array_push(
            $placeholders_values,
            isset($donation->merchandise_id) && !empty($campaign->merchandise_settings[$donation->merchandise_id]) ?
                $campaign->merchandise_settings[$donation->merchandise_id]['title'] : '',
            isset($donation->merchandise_id) && !empty($campaign->merchandise_settings[$donation->merchandise_id]) ?
                $campaign->merchandise_settings[$donation->merchandise_id]['donation_amount_needed'] : ''
        );

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

    public function display_merchandise_field_star(array $template_data, Leyka_Campaign $campaign) {

        $uploads_dir_url = wp_get_upload_dir();
        $uploads_dir_url = $uploads_dir_url['baseurl'];?>

        <div class="section section--merchandise">

            <div class="section-title-container">
                <div class="section-title-line"></div>
                <div class="section-title-text" role="heading" aria-level="3"><?php _e('Donation reward', 'leyka');?></div>
            </div>

            <div class="section__fields merchandise-grid">
            <?php foreach($campaign->merchandise_settings as $merchandise_id => $settings) {?>

                <div class="merchandise-item" data-merchandise-id="<?php echo esc_attr($merchandise_id);?>" data-donation-amount-needed="<?php echo absint($settings['donation_amount_needed']);?>">

                    <label class="merchandise__button">

                        <span class="merchandise__label"><?php echo esc_html($settings['title']);?></span>

                        <?php if($settings['thumbnail']) {?>

                            <span class="merchandise__icon">
                                    <img class="merchandise-icon" src="<?php echo $uploads_dir_url.$settings['thumbnail'];?>" alt="<?php echo esc_attr($settings['title']);?>">
                                </span>

                        <?php }?>

                        <span class="merchandise__description"><?php echo $settings['description'];?></span>

                    </label>

                </div>
            <?php }?>
            </div>

            <input type="hidden" name="leyka_donation_merchandise_id" value="">

        </div>

    <?php }

}

function leyka_add_extension_merchandise() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_extension(Leyka_Merchandise_Extension::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_extension_merchandise');