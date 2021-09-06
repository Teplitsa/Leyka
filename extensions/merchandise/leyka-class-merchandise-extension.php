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
        $this->_title = __('Merchandise/gifts for donors', 'leyka');

        // A human-readable short description (for backoffice extensions list page):
        $this->_description = 'Добавьте варианты подарков за пожертвования на ваши Лейко-формы, чтобы донор мог получать подарки от вас в зависимости от размера его пожертвования.';

        // A human-readable full description (for backoffice extensions list page):
        $this->_full_description = 'Это более подробное описание расширения, символов на 150-300. Например, вот такое длинное, как эта строка.';

        // A human-readable description (for backoffice extension settings page):
        $this->_settings_description = 'Это текст с описанием или комментарием работы расширения, который выводится на странице настроек расширения в административном разделе Лейки. Текст может быть довольно длинным, но мы рекомендуем помнить, что молчание - золото. А лаконичность - так вообще платина; лучше только телепатия.';

        // A human-readable description of how to enable the main feature (for backoffice extension settings page):
        $this->_connection_description = '<p><strong>В этом месте можно вывести краткое описание использования некоторой функции</strong></p>
<p>Например, вы можете использовать функцию «раз-два-три» так:</p>
<code>[some_shortcode param1="Какая-то надпись"]</code>
<br>Ваш текст<br>
<code>[/some_shortcode]</code>';

        $this->_user_docs_link = '//leyka.te-st.ru/docs/merch-manual';
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

        add_action('add_meta_boxes', function(){

            add_meta_box(
                Leyka_Campaign_Management::$post_type.'_merchandise',
                __('Campaign merchandise / gifts for donors', 'leyka'),
                [$this, 'merchandise_metabox'],
                Leyka_Campaign_Management::$post_type,
                'normal',
                'low'
            );

        });

        // To initialize merchandise data as Campaign meta on object construction:
        add_filter('leyka_campaign_constructor_meta', [$this, '_merchandise_campaign_data_initializing'], 10, 2);

        // To get/set merchandise settings from Campaign object:
        add_filter('leyka_get_unknown_campaign_field', [$this, '_merchandise_campaign_data_get'], 10, 3);
        add_action('leyka_set_unknown_campaign_field', [$this, '_merchandise_campaign_data_set'], 10, 3);

        // To save merchandise data on Campaign saving:
        add_action('leyka_campaign_data_after_saving', [$this, '_merchandise_campaign_data_saving'], 10, 2);

    }

    /** Will be called everytime the Extension is loading into the plugin (i.e. always). */
    protected function _initialize_always() {
    }

    public function merchandise_metabox(WP_Post $campaign) {

        $campaign = new Leyka_Campaign($campaign);?>

        <div class="leyka-admin leyka-settings-page">

            <div class="leyka-options-section">

                <?php function leyka_campaign_merchandise_field_html($is_template = false, $placeholders = []) {

                    $placeholders = wp_parse_args($placeholders, [
                        'id' => '',
                        'box_title' => __('New merchandise', 'leyka'),
                        'title' => '',
                        'description' => false,
                        'min_donation_amount' => 0,
                        'thumbnail' => false,
                    ]);?>

                    <div id="<?php echo $is_template || !$placeholders['id'] ? 'item-'.leyka_get_random_string(4) : $placeholders['id'];?>" class="multi-valued-item-box field-box <?php echo $is_template ? 'item-template' : '';?>" style="<?php echo $is_template ? 'display: none;' : '';?>">

                        <h3 class="item-box-title ui-sortable-handle">
                            <span class="draggable"></span>
                            <span class="title" data-empty-box-title="<?php _e('New merchandise', 'leyka');?>">
                                <?php echo esc_html($placeholders['box_title']);?>
                            </span>
                        </h3>

                        <div class="box-content">

                            <div class="single-line">

                                <div class="option-block type-text">
                                    <div class="leyka-select-field-wrapper">
                                        <?php leyka_render_text_field('merchandise_title', [
                                            'title' => __('Merchandise title', 'leyka'),
                                            'placeholder' => sprintf(__('E.g., %s'), __('A cool hat with our logo', 'leyka')),
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
                                        <?php leyka_render_number_field('merchandise_min_donation_amount', [
                                            'title' => sprintf(
                                                __('Min. donations amount needed for the gift, %s', 'leyka'),
                                                leyka_get_currency_label()
                                            ),
                                            'required' => true,
                                            'value' => absint($placeholders['min_donation_amount']) ? : 2000,
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
//                                            'description' => sprintf(
//                                                __('A *.png or *.jpg file. The size is no more than %s Mb', 'leyka'),
//                                                (int)ini_get('upload_max_filesize')
//                                            ),
                                        'required' => false,
                                        'value' => $placeholders['thumbnail'],
//                                            'field_classes' => 'leyka-upload-field-merchandise',
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
                                    <?php _e('Remove the merchandise from this campaign', 'leyka');?>
                                </div>
                            </div>

                        </div>

                    </div>

                <?php }?>

                <div class="leyka-campaign-merchandise-wrapper multi-valued-items-field-wrapper">

                    <div class="leyka-main-multi-items leyka-main-merchandise" data-min-items="0" data-max-items="<?php echo 10;?>" data-item-inputs-names-prefix="leyka_campaign_merchandise_" data-show-new-item-if-empty="0">

                        <?php // Display existing campaign merchandise (the assoc. array keys order is important):
                        foreach($campaign->merchandise_settings as $item_id => $item) {

                            // Field is in Campaign settings, but not in the Library - mb, it was deleted from there:
                            if(empty($item_id)) {
                                continue;
                            }

                            leyka_campaign_merchandise_field_html(false, [
                                'id' => $item_id,
                                'box_title' => $item['title'],
                                'title' => $item['title'],
                                'description' => $item['description'],
                                'min_donation_amount' => $item['min_donation_amount'],
                                'thumbnail' => $item['thumbnail'],
                            ]);

                        }?>

                    </div>

                    <?php leyka_campaign_merchandise_field_html(true); // Merchandise box template ?>

                    <div class="add-field add-item bottom"><?php _e('Add merchandise', 'leyka');?></div>

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

        foreach($campaign_data['leyka_campaign_merchandise'] as $item) {

            $item->id = mb_stripos($item->id, 'item-') === false || empty($item->leyka_merchandise_title) ?
                $item->id :
                trim(preg_replace('~[^-a-z0-9_]+~u', '-', mb_strtolower(leyka_cyr2lat($item->leyka_merchandise_title))), '-');

            if( !$item->leyka_merchandise_title || !$item->leyka_merchandise_min_donation_amount ) {
                continue;
            }

            $merchandise_data[$item->id] = [
                'title' => $item->leyka_merchandise_title,
                'description' => $item->leyka_merchandise_description,
                'min_donation_amount' => $item->leyka_merchandise_min_donation_amount,
                'thumbnail' => $item->leyka_merchandise_thumbnail,
            ];

        }

        $campaign->merchandise_settings = $merchandise_data;

    }

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

        return get_post_meta($campaign->id, 'leyka_campaign_merchandise_settings', true);

    }

    public function _merchandise_campaign_data_set($field_name, $value, Leyka_Campaign $campaign) {

        if( !in_array($field_name, ['merchandise', 'merchandise_settings']) || !$campaign->id ) {
            return $value;
        }

//        $campaign->_campaign_meta['merchandise_settings'] = $value;
        update_post_meta($campaign->id, 'leyka_campaign_merchandise_settings', $value);

    }

}

function leyka_add_extension_merchandise() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_extension(Leyka_Merchandise_Extension::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_extension_merchandise');