<?php if( !defined('WPINC') ) die;

/**
 * Leyka Donation Campaign Functionality
 **/

class Leyka_Campaign_Management extends Leyka_Singleton {

	protected static $_instance = null;

	public static $post_type = 'leyka_campaign';

	protected function __construct() {

		add_action('add_meta_boxes', array($this, 'set_metaboxes'));
		add_filter('manage_'.self::$post_type.'_posts_columns', array($this, 'manage_columns_names'));
		add_action('manage_'.self::$post_type.'_posts_custom_column', array($this, 'manage_columns_content'), 2, 2);
		add_action('save_post', array($this, 'save_data'), 2, 2);

        add_action('restrict_manage_posts', array($this, 'manage_filters'));
        add_action('pre_get_posts', array($this, 'do_filtering'));

		add_filter('post_row_actions', array($this, 'row_actions'), 10, 2);

	}

    /**
     * @param $messages array
     * @return array
     */
    public function set_admin_messages($messages) {

        $current_post = get_post();

        $messages[Leyka_Campaign_Management::$post_type] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf(
                __('Campaign updated. <a href="%s">View it</a>', 'leyka'),
                esc_url(home_url('?p='.$current_post->ID))
            ),
            2 => __('Field updated.', 'leyka'),
            3 => __('Field deleted.', 'leyka'),
            4 => __('Campaign updated.', 'leyka'),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ? sprintf(__('Campaign restored to revision from %s', 'leyka'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
            6 => sprintf(
                __('Campaign published. <a href="%s">View it</a>', 'leyka'),
                esc_url(home_url('?p='.$current_post->ID))
            ),
            7 => __('Campaign saved.', 'leyka'),
            8 => sprintf(
                __('Campaign submitted. <a target="_blank" href="%s">Preview it</a>', 'leyka'),
                esc_url(add_query_arg('preview', 'true', home_url('?p='.$current_post->ID)))
            ),
            9 => sprintf(
                __('Campaign scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview it</a>', 'leyka'),
                // translators: Publish box date format, see http://php.net/date
                date_i18n(__( 'M j, Y @ G:i'), strtotime($current_post->post_date)),
                esc_url(home_url('?p='.$current_post->ID))
            ),
            10 => sprintf(
                __('Campaign draft updated. <a target="_blank" href="%s">Preview it</a>', 'leyka'),
                esc_url(add_query_arg('preview', 'true', home_url('?p='.$current_post->ID)))
            ),
        );

        return $messages;

    }

    /**
     * @param $actions array
     * @param $campaign WP_Post
     * @return array
     */
    public function row_actions($actions, $campaign) {

        $current_screen = get_current_screen();

        if( !$current_screen || !is_object($current_screen) || $current_screen->post_type != self::$post_type ) {
            return $actions;
        }

        unset($actions['inline hide-if-no-js']);
        return $actions;

    }

    public function manage_filters() {

        if(get_current_screen()->id == 'edit-'.self::$post_type && current_user_can('leyka_manage_donations')) {?>

            <label for="campaign-state-select"></label>
            <select id="campaign-state-select" name="campaign_state">
                <option value="all" <?php echo empty($_GET['campaign_state']) ? 'selected="selected"' : '';?>>
                    - <?php _e('Collection state', 'leyka');?> -
                </option>
                <option value="is_finished" <?php echo !empty($_GET['campaign_state']) && $_GET['campaign_state'] == 'is_finished' ? 'selected="selected"' : '';?>><?php _e('Closed', 'leyka');?></option>
                <option value="is_open" <?php echo !empty($_GET['campaign_state']) && $_GET['campaign_state'] == 'is_open' ? 'selected="selected"' : '';?>><?php _e('Opened', 'leyka');?></option>

            </select>

            <label for="target-state-select"></label>
            <select id="target-state-select" name="target_state">
                <option value="" <?php echo empty($_GET['target_state']) ? 'selected="selected"' : '';?>>
                    - <?php _e('Target', 'leyka');?> -
                </option>

                <?php foreach(leyka()->get_campaign_target_states() as $state => $label) {?>
                <option value="<?php echo $state;?>" <?php echo !empty($_GET['target_state']) && $_GET['target_state'] == $state ? 'selected="selected"' : '';?>>
                    <?php echo $label;?>
                </option>
                <?php }?>
            </select>
    <?php }

    }

    /**
     * @param $query WP_Query
     */
    public function do_filtering(WP_Query $query) {

        if(
            !is_admin()
            || !$query->is_main_query()
            || !get_current_screen()
            || get_current_screen()->id !== 'edit-'.self::$post_type
        ) {
            return;
        }

        $meta_query = array('relation' => 'AND');

        if( !empty($_REQUEST['campaign_state']) && $_REQUEST['campaign_state'] !== 'all' ) {
            $meta_query[] = array('key' => 'is_finished', 'value' => $_REQUEST['campaign_state'] == 'is_finished' ? 1 : 0);
        }

        if( !empty($_REQUEST['target_state']) ) {
            $meta_query[] = array('key' => 'target_state', 'value' => $_REQUEST['target_state']);
        }

        if(count($meta_query) > 1) {
            $query->set('meta_query', $meta_query);
        }

    }

    public function set_metaboxes() {

        add_meta_box(
            self::$post_type.'_excerpt',
            __('Annotation', 'leyka'),
            array($this, 'annotation_meta_box'),
            self::$post_type,
            'normal',
            'high'
        );

        add_meta_box(self::$post_type.'_data',
            __('Campaign settings', 'leyka'),
            array($this, 'data_meta_box'),
            self::$post_type,
            'normal',
            'high'
        );

        add_meta_box(
            self::$post_type.'_additional_fields',
            __('Additional campaign form fields', 'leyka'),
            array($this, 'additional_fields_meta_box'),
            self::$post_type,
            'normal',
            'high'
        );

        // Metaboxes are only for campaign editing page (not for new campaign page):
        $screen = get_current_screen();
        if($screen->post_type == self::$post_type && $screen->base === 'post' && !$screen->action) {

//            add_meta_box(
//                self::$post_type.'_embed', __('Campaign embedding', 'leyka'),
//                array($this, 'embedding_meta_box'), self::$post_type, 'normal', 'high'
//            );

		    add_meta_box(
                self::$post_type.'_donations',
                __('Donations history', 'leyka'),
                array($this, 'donations_meta_box'),
                self::$post_type,
                'normal',
                'high'
            );

            add_meta_box(
                self::$post_type.'_statistics',
                __('Campaign statistics', 'leyka'),
                array($this, 'statistics_meta_box'),
                self::$post_type,
                'side',
                'low'
            );

        }

	}

    /**
     * @param $campaign WP_Post
     */
    public function data_meta_box(WP_Post $campaign) {

        $campaign = new Leyka_Campaign($campaign);

		$cur_template = $campaign->template ? $campaign->template : 'default';?>

        <fieldset id="campaign-form-template" class="metabox-field campaign-field campaign-template temporary-campaign-fields">

            <h3 class="field-title">
                <label for="campaign-form-template-field"><?php _e('Template for payment form', 'leyka');?></label>
            </h3>

            <div class="field-wrapper flex">

                <?php $templates = leyka()->get_templates();

                if(
                    $cur_template !== 'default'
                    && leyka()->template_is_deprecated($cur_template)
                    && !leyka_options()->opt('allow_deprecated_form_templates')
                ) {
                    $templates[] = leyka()->get_template($cur_template);
                }

                $default_template = leyka()->get_template(leyka_options()->opt('donation_form_template'));
                if( !$default_template || leyka()->template_is_disabled($default_template['id']) ) {
                    $default_template = leyka()->get_template('star');
                }?>

                <select id="campaign-form-template-field" name="campaign_template" data-default-template-id="<?php echo empty($default_template['id']) ? '' : esc_attr($default_template['id']);?>">

                    <option value="default" <?php selected($cur_template, 'default');?>>
                        <?php echo sprintf(__('Default template (%s)', 'leyka'), __($default_template['name'], 'leyka'));?>
                    </option>

                    <?php foreach($templates as $template) {

                        $template_id = esc_attr($template['id']);
                        $template_deprecated = leyka()->template_is_deprecated($template_id);?>

                        <option value="<?php echo $template_id;?>" <?php selected($cur_template, $template_id);?> class="<?php echo $template_deprecated ? 'template-deprecated' : '';?>">
                            <?php echo __($template['name'], 'leyka')
                                .($template_deprecated ? ' ('.__('deprecated', 'leyka').')' : '');?>
                        </option>

                    <?php }?>

                </select>

                <?php /** @todo Check if this div is used */ ?>
                <div class="form-template-demo" style="display: none;">
                <?php foreach($templates as $template) {

                    $template_id = esc_attr($template['id']);?>

                    <img class="form-template-screenshot <?php echo $template_id;?>" src="<?php echo LEYKA_PLUGIN_BASE_URL.'/img/theme-screenshots/screen-'.$template_id.'-002.png';?>" alt="" style="display: none;">

                <?php }?>
                </div>

            </div>

            <div class="field-wrapper flex daily-rouble-settings-wrapper" style="<?php echo $cur_template === 'need-help' ? '' : 'display:none;'?>">

                <label for="daily-rouble-mode-on">
                    <input type="checkbox" id="daily-rouble-mode-on" name="daily_rouble_mode_on" value="1" <?php echo $campaign->daily_rouble_mode_on ? 'checked' : '';?>>&nbsp;<?php echo sprintf(__('Turn on the "<a href="%s" target="_blank">Rouble per day</a>" mode', 'leyka'), 'https://leyka.te-st.ru/docs/ruble-a-day/');?>
                </label>

                <div class="field-wrapper flex daily-rouble-settings" style="<?php //echo $campaign->daily_rouble_mode_on ? '' : 'display:none;';?>">

                    <div class="field-wrapper flex">

                        <h3 class="field-title">
                            <label for="daily-rouble-amount-variants"><?php _e('Daily amount variants', 'leyka');?></label>
                        </h3>

                        <input type="text" id="daily-rouble-amount-variants" name="daily_rouble_amounts" value="<?php echo $campaign->daily_rouble_amounts ? $campaign->daily_rouble_amounts : '1,2,3,4,5,10';?>" placeholder="<?php _e('E. g., 1,2,3,4,5,10');?>">

                    </div>

                    <div class="field-wrapper flex">

                        <h3 class="field-title">
                            <label for="daily-rouble-pm"><?php _e('Daily amount payment method', 'leyka');?></label>
                        </h3>

                        <?php $recurring_pm_list = leyka_get_pm_list(true);
                        foreach($recurring_pm_list as $index => $pm) { /** @var $pm Leyka_Payment_Method */
                            if( !$pm->has_recurring_support() ) {
                                unset($recurring_pm_list[$index]);
                            }
                        }

                        $pm = leyka_get_pm_by_id($campaign->daily_rouble_pm_id, true);?>

                        <select name="daily_rouble_pm" id="daily-rouble-pm" <?php echo $recurring_pm_list ? '' : 'disabled="disabled"';?>>
                            <option value="" <?php echo $pm && $pm->is_active ? '' : 'selected="selected"';?>><?php _e('Not set', 'leyka');?></option>

                        <?php foreach($recurring_pm_list as $pm) {?>
                            <option value="<?php echo $pm->full_id;?>" <?php echo $pm->full_id === $campaign->daily_rouble_pm_id ? 'selected="selected"' : '';?>>
                                <?php echo $pm->title.' ('.$pm->gateway->title.')';?>
                            </option>
                        <?php }?>

                        </select>

                    </div>

                </div>

            </div>

        </fieldset>

        <fieldset id="campaign-type" class="metabox-field campaign-field campaign-type">

            <h3 class="field-title"><?php _e('Campaign type', 'leyka');?></h3>

            <div class="field-wrapper">
                <label class="field-label">
                    <input type="radio" name="campaign_type" value="temporary" <?php echo $campaign->type === 'temporary' ? 'checked="checked"' : '';?>><?php _e('Temporary', 'leyka');?>
                </label>
                <label class="field-label">
                    <input type="radio" name="campaign_type" value="persistent" <?php echo $campaign->type === 'persistent' ? 'checked="checked"' : '';?>><?php _e('Persistent', 'leyka');?>
                </label>
            </div>
        </fieldset>

        <fieldset id="donations-types" class="metabox-field campaign-field donaitons-types" style="<?php echo $campaign->daily_rouble_mode_on ? 'display: none;' : '';?>">

            <h3 class="field-title"><?php _e('Donations types available', 'leyka');?></h3>

            <div class="field-wrapper">
                <label class="field-label">
                    <input type="checkbox" name="donations_type[]" value="recurring" <?php echo in_array('recurring', $campaign->donations_types_available) || $campaign->status === 'auto-draft' ? 'checked="checked"' : '';?>><?php echo _x('Recurring', 'In mult., like "recurring donations"', 'leyka');?>
                </label>
                <label class="field-label">
                    <input type="checkbox" name="donations_type[]" value="single" <?php echo in_array('single', $campaign->donations_types_available) || $campaign->status === 'auto-draft' ? 'checked="checked"' : '';?>><?php echo _x('Single', 'In mult., like "single donations"', 'leyka');?>
                </label>
            </div>

        </fieldset>

        <fieldset id="donation-type-default" class="metabox-field campaign-field donaiton-type-default" style="<?php echo $campaign->daily_rouble_mode_on ? 'display: none;' : '';?>">

            <h3 class="field-title">
                <?php _e('Donation type by default', 'leyka');?>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip">
                        <?php _e('What donation type is going to be used when donor sees a campaign form firsthand? The default type may influence number of the single & recurring donations.', 'leyka');?>
                    </span>
                </span>
            </h3>

            <div class="field-wrapper">
                <label class="field-label">
                    <input type="radio" name="donations_type_default" value="recurring" <?php echo $campaign->donations_type_default === 'recurring' ? 'checked="checked"' : '';?>><?php echo _x('Recurring', 'In single, like "recurring donation"', 'leyka');?>
                </label>
                <label class="field-label">
                    <input type="radio" name="donations_type_default" value="single" <?php echo $campaign->donations_type_default === 'single' ? 'checked="checked"' : '';?>><?php echo _x('Single', 'In single, like "single donation"', 'leyka');?>
                </label>
            </div>

        </fieldset>

        <fieldset id="target-amount" class="metabox-field campaign-field campaign-target temporary-campaign-fields">

            <h3 class="field-title">
                <label for="campaign-target">
                    <?php echo sprintf(__('Target (%s)', 'leyka'), leyka_options()->opt('currency_rur_label'));?>
                </label>
            </h3>

            <input type="text" name="campaign_target" id="campaign-target" value="<?php echo $campaign->target;?>">

        </fieldset>

        <fieldset id="payment-title" class="metabox-field campaign-field campaign-purpose">

            <h3 class="field-title">
                <label for="payment_title"><?php _e('Payment purpose', 'leyka');?></label>
            </h3>

            <?php $payment_title = $campaign->payment_title ? $campaign->payment_title : $campaign->title;?>

            <input type="text" name="payment_title" id="payment_title" class="leyka-field-wide" value="<?php echo $payment_title;?>" placeholder="<?php _e("If the field is empty, the campaign title will be used", 'leyka');?>" maxlength="128">

            <div class="campaign-field-description" data-description-for="payment_title">

                <?php $description_max_length = 128 - mb_strlen(_x('[RS]', 'For "recurring subscription"', 'leyka')) - 1;

                echo sprintf(__('The value should be max. %d characters length (currently: <span class="leyka-field-current-value-length">%d</span> / %d)', 'leyka'), $description_max_length, mb_strlen($payment_title), $description_max_length);?>

            </div>

        </fieldset>

        <?php $curr_page = get_current_screen();
        if($curr_page->action !== 'add') {?>

        <fieldset id="campaign-finished" class="metabox-field without-title campaign-field campaign-finished">
            <label for="is-finished">
                <input type="checkbox" id="is-finished" name="is_finished" value="1" <?php echo $campaign->is_finished ? 'checked' : '';?>> <?php _e('Donations collection stopped', 'leyka');?>
            </label>
        </fieldset>
	    <?php }?>

        <fieldset id="campaign-css" class="metabox-field campaign-field campaign-css">

            <h3 class="field-title">
                <label for="campaign-css-field"><?php _e('Campaign CSS-styling', 'leyka');?></label>
                <span class="field-q">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                    <span class="field-q-tooltip">
                        <?php _e('Make your changes in the campaign page styles via this field. You can always get back to the original set of styles.', 'leyka');?>
                    </span>
                </span>
            </h3>

            <div class="field-wrapper css-editor">

                <?php $campaign_css_original = array(
                    'star' => '/* :root { --leyka-color-main: #ff510d; } */ '
                        .__('/* Active buttons & switches background color */', 'leyka')."\n".
                        '/* :root { --leyka-color-main-second: #ffc29f; } */ '
                        .__('/* Controls borders color */', 'leyka')."\n".
                        '/* :root { --leyka-color-text-light: #ffffff; } */ '
                        .__('/* Active buttons & switches text color */', 'leyka')."\n".
                        '/* :root { --leyka-color-main-third: #fef5f1; } */ '
                        .__('/* Selected payment method background color */', 'leyka')."\n".
                        '/* :root { --leyka-color-main-inactive: rgba(255,81,13, 0.5); } */ '
                        .__('/* Inactive main submit background color */', 'leyka')."\n".
                        '/* :root { --leyka-color-error: #d43c57; } */ '
                        .__('/* Error messages text color */', 'leyka')."\n".
                        '/* :root { --leyka-color-gray-dark: #474747; } */ '
                        .__('/* The main text color (controls & content) */', 'leyka')."\n".
                        '/* :root { --leyka-color-gray-semi-dark: #656565; } */ '
                        .__('/* Single/recurring switch inactive variant text color */', 'leyka')."\n".
                        '/* :root { --leyka-color-gray: #666666; } */ '
                        .__('/* Form fields labels color */', 'leyka')."\n".
                        '/* :root { --leyka-color-gray-superlight: #ededed; } */ '
                        .__('/* Checkboxes & other fields borders color */', 'leyka')."\n".
                        '/* :root { --leyka-color-white: #ffffff; } */ '
                        .__('/* The main form background color */', 'leyka')."\n".
                        '/* :root { --leyka-font-main: unset; } */ '
                        .__('/* The main form font family */', 'leyka')."\n".
                        '/* :root { --leyka-color-gradient: #ffffff; } */ '
                        .__('/* Payment methods selector gradient color */', 'leyka')."\n",

                    'need-help' => '/* :root { --leyka-need-help-color-main: #000000; } */ '
                        .__('/* Active buttons & switches highlight color */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-color-main-second: #000000; } */ '
                        .__('/* Secondary elements color */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-color-main-inactive: #CCCCCC; } */ '
                        .__('/* The inactive elements color. Most of the times, the main color with lighter shade */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-color-form-background: #FAFAFA; } */ '
                        .__('/* Form background color */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-color-blocks-border: #E6E6E6; } */ '
                        .__('/* Form blocks border color */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-color-blocks-background: #FFFFFF; } */ '
                        .__('/* Form blocks background color */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-color-blocks-active-border: var(--leyka-need-help-color-main); } */ '
                        .__('/* Form active blocks border color */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-color-blocks-active-background: var(--leyka-need-help-color-blocks-background); } */ '
                        .__('/* Form active blocks background color */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-color-error: var(--leyka-need-help-color-error); } */ '
                        .__('/* Form error messages color */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-color-text: #2A2A2A; } */ '
                        .__('/* Form text color */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-color-text-light: #666666; } */ '
                        .__('/* Form text color, lighter shade */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-color-text-superlight: #999999; } */ '
                        .__('/* Form text color, the most light shade */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-color-text-dark-bg: #FFFFFF; } */ '
                        .__('/* Form text color, for elements with dark background */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-font-family-main: Inter, sans-serif; } */ '
                        .__('/* Form text font */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-font-family-blocks: Inter, sans-serif; } */ '
                        .__('/* Form blocks text font */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-font-family-submit: Inter, sans-serif; } */ '
                        .__('/* Form submit text font */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-font-family-section-titles: Inter, sans-serif; } */ '
                        .__('/* Form sections titles text font */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-font-size-main: 16px; } */ '
                        .__('/* Form text size */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-font-size-blocks-default: 16px; } */ '
                        .__('/* Form blocks text size */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-font-size-amounts: 16px; } */ '
                        .__('/* Donation amount blocks text size */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-font-size-pm-options: 12px; } */ '
                        .__('/* Payment method blocks text size */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-font-size-donor-fields: 16px; } */ '
                        .__('/* Donor data fields text size */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-font-size-submit: 16px; } */ '
                        .__('/* Form submit text size */', 'leyka')."\n".
                        '/* :root { --leyka-need-help-font-size-section-titles: 18px; } */ '
                        .__('/* Form sections titles text size */', 'leyka')."\n"
                );

                $additional_css_used = $campaign->additional_css && !in_array(
                    preg_replace('/\s+/u', '', html_entity_decode($campaign->additional_css, ENT_QUOTES)),
                    array_map(function($value){
                    return preg_replace('/\s+/u', '', $value);
                }, $campaign_css_original));

                $campaign_template_id = $campaign->template_id ? // The new campaigns don't have even the "default" template yet
                    $campaign->template_id : leyka_options()->opt('donation_form_template');?>

                <textarea id="campaign-css-field" name="campaign_css" class="css-editor-field" data-additional-css-used="<?php echo $additional_css_used;?>"><?php echo $campaign->additional_css ?
                    trim($campaign->additional_css) :
                    (empty($campaign_css_original[$campaign_template_id]) ?
                        '' :
                        trim($campaign_css_original[$campaign_template_id]));?></textarea>

                <div class="css-editor-reset-value"><?php _e('Return original styles', 'leyka');?></div>

                <input type="hidden" class="css-editor-default-original-value" value="<?php echo esc_attr(trim($campaign_css_original[leyka_options()->opt('donation_form_template')]));?>">

                <?php foreach($campaign_css_original as $template_id => $template_css) {?>
                <input type="hidden" class="css-editor-<?php echo $template_id;?>-original-value" value="<?php echo esc_attr(trim($template_css));?>">
                <?php }?>

            </div>

        </fieldset>

        <fieldset id="campaign-cover-type" class="metabox-field campaign-field persistent-campaign-field">
            <h3 class="field-title">
                <?php _e('The campaign page cover type', 'leyka');?>
            </h3>

            <div class="field-wrapper">
                <label for="hide-cover-type-image" class="field-label">
                    <input type="radio" id="hide-cover-type-image" name="header_cover_type" value="image" <?php echo empty($campaign->header_cover_type) || $campaign->header_cover_type == '"image"' ? 'checked' : '';?>> <?php _e('Background image', 'leyka');?>
                </label>
                <label for="hide-cover-type-color" class="field-label">
                    <input type="radio" id="hide-cover-type-color" name="header_cover_type" value="color" <?php echo $campaign->header_cover_type == 'color' ? 'checked' : '';?>> <?php _e('Solid color', 'leyka');?>
                </label>
            </div>
        </fieldset>

        <fieldset id="campaign-cover-bg-color" class="metabox-field campaign-field persistent-campaign-field">
            <h3 class="field-title">
                <?php _e('The campaign page cover background color', 'leyka');?>
            </h3>

            <div class="field-wrapper">
                <span class="field-component field">
                    <input type="color" name="cover_bg_color" value="<?php echo $campaign->cover_bg_color ? $campaign->cover_bg_color : '#000000';?>">
                </span>
            </div>
        </fieldset>

        <fieldset id="campaign-images" class="metabox-field campaign-field persistent-campaign-field">

            <h3 class="field-title">
                <?php _e('The campaign decoration images', 'leyka');?>
<!--                <span class="field-q" style="display: none;">-->
<!--                    <img src="--><?php //echo LEYKA_PLUGIN_BASE_URL;?><!--img/icon-q.svg" alt="">-->
<!--                    <span class="field-q-tooltip">-->
<!--                        --><?php //esc_html_e('Some text here.', 'leyka');?>
<!--                    </span>-->
<!--                </span>-->
            </h3>

            <div class="upload-photo-complex-field-wrapper margin-top" id="upload-campaign-cover-image">
                <div class="set-page-img-control" data-mission="cover" data-campaign-id="<?php echo $campaign->id;?>">
                	<?php $img_url = $campaign->cover_id ? wp_get_attachment_image_url($campaign->cover_id, 'thumbnail') : null;?>
                	<?php _e('Uploaded cover:', 'leyka');?> <span class="img-value"><?php echo $img_url ? '<img src="'.$img_url.'" />' : __('Default', 'leyka');?></span>
            		<a href="#" class="reset-to-default" <?php echo !$img_url ? 'style="display: none;"' : '';?> title="<?php _e('Reset to default');?>"></a>
            		<?php wp_nonce_field('reset-campaign-attachment', 'reset-campaign-cover-nonce');?>
                    <div class="loading-indicator-wrap" style="display: none;">
                        <div class="loader-wrap"><span class="leyka-loader xs"></span></div>
                    </div>
                </div>
                <div class="upload-photo-field upload-attachment-field field-wrapper flex" data-upload-title="<?php _e('The campaign page cover image', 'leyka');?>" data-field-name="campaign_cover" data-campaign-id="<?php echo $campaign->id;?>" data-ajax-action="leyka_set_campaign_attachment">
                    <span class="field-component field">
                        <input type="file" value="">
                        <input type="button" class="button upload-photo" id="campaign_cover-upload-button" value="<?php _e('Upload the page cover', 'leyka');?>">
                    </span>
                    <span class="upload-field-description">
                        <?php echo sprintf(__('.jpg or .png file, no more than %s sized, recommended width: %s', 'leyka'), leyka_get_upload_max_filesize(), '1920 px');?>
                    </span>
    
                    <?php wp_nonce_field('set-campaign-attachment', 'campaign-cover-nonce');?>
                    <input type="hidden" id="leyka-campaign_cover" name="campaign_cover" value="<?php echo $campaign->cover_id;?>">
    
                    <div class="loading-indicator-wrap" style="display: none;">
                        <div class="loader-wrap"><span class="leyka-loader xs"></span></div>
                    </div>
                </div>
                <div class="field-errors"></div>
            </div>

            <div class="upload-photo-complex-field-wrapper margin-top">
                <div class="set-page-img-control" data-mission="logo" data-campaign-id="<?php echo $campaign->id;?>">
                	<?php $img_url = $campaign->logo_id ? wp_get_attachment_image_url($campaign->logo_id, 'thumbnail') : null;?>
                	<?php _e('Uploaded logo:', 'leyka');?> <span class="img-value"><?php echo $img_url ? '<img src="'.$img_url.'" />' : __('Default', 'leyka');?></span>
            		<a href="#" class="reset-to-default" <?php echo !$img_url ? 'style="display: none;"' : '';?> title="<?php _e('Reset to default');?>"></a>
            		<?php wp_nonce_field('reset-campaign-attachment', 'reset-campaign-logo-nonce');?>
                    <div class="loading-indicator-wrap" style="display: none;">
                        <div class="loader-wrap"><span class="leyka-loader xs"></span></div>
                    </div>
                </div>
                <div class="upload-photo-field upload-attachment-field field-wrapper flex" data-upload-title="<?php _e('Your logo on the campaign page', 'leyka');?>" data-field-name="campaign_logo" data-campaign-id="<?php echo $campaign->id;?>" data-ajax-action="leyka_set_campaign_attachment">
                    <span class="field-component field">
                        <input type="file" value="">
                        <input type="button" class="button upload-photo" id="campaign_logo-upload-button" value="<?php _e('Upload the logo', 'leyka');?>">
                    </span>
                    <span class="upload-field-description">
                        <?php echo sprintf(__('.jpg or .png file, no more than %s sized', 'leyka'), leyka_get_upload_max_filesize());?>
                    </span>
    
                    <?php wp_nonce_field('set-campaign-attachment', 'campaign-logo-nonce');?>
                    <input type="hidden" id="leyka-campaign_logo" name="campaign_logo" value="<?php echo $campaign->logo_id;?>">
    
                    <div class="loading-indicator-wrap" style="display: none;">
                        <div class="loader-wrap"><span class="leyka-loader xs"></span></div>
                    </div>
                </div>
                <div class="field-errors"></div>
            </div>

        </fieldset>

        <fieldset id="campaign-cover-tint" class="metabox-field without-title campaign-field persistent-campaign-field">
            <label for="hide-cover-tint">
                <input type="checkbox" id="hide-cover-tint" name="hide_cover_tint" value="1" <?php echo $campaign->hide_cover_tint ? 'checked' : '';?>> <?php _e('Hide cover tint', 'leyka');?>
            </label>
        </fieldset>

        <fieldset id="campaign-data-setup-howto" class="metabox-field campaign-field info-field">

            <h3 class="field-title"><?php _e('Recommendations', 'leyka');?></h3>
            <div class="field-wrapper">
                <ul>
                    <li><a href="<?php echo '#';?>" target="_blank"><?php _e('How to set up recurring payments support', 'leyka');?></a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=leyka_settings&stage=beneficiary#terms_of_service');?>" target="_blank"><?php _e('I want to change the Terms of service text', 'leyka');?></a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=leyka_settings&stage=beneficiary#terms_of_pd');?>" target="_blank"><?php _e('I want to change the Personal data usage terms text', 'leyka');?></a></li>
                </ul>
            </div>

        </fieldset>

        <?php // If Support packages Extention is active, add the special fields to check if current campaign can be turned off.
        // Also, the check is only for active (published + non-finished) campaigns:
        if( !leyka()->extension_is_active('support_packages') || $campaign->status != 'publish' || $campaign->is_finished ) {
            return;
        }

        /** @var Leyka_Support_Packages_Extension $extension */
        $extension = leyka_get_extension_by_id('support_packages');

        $support_packages_campaign = $extension->get_available_campaign();
        $support_packages_campaign = $support_packages_campaign ? new Leyka_Campaign($support_packages_campaign) : null;

        // No need for checks in current campaign:
        if( !$support_packages_campaign || $support_packages_campaign->id != $campaign->id ) {
            return;
        }?>

        <input type="hidden" id="leyka-campaign-needed-for-support-packages" value="<?php echo $extension->get_available_campaigns_count() === 1 ? 1 : 0;?>">

        <div id="leyka-campaign-needed-modal-content" style="display:none;" title="<?php _e('You are closing the Support packages campaign', 'leyka');?>" data-nonce="<?php echo wp_create_nonce('support-packages-no-campaign-behavior');?>" data-close-button-text="<?php _e('Close', 'leyka');?>" data-submit-button-text="<?php _e('Submit', 'leyka');?>">

            <div id="leyka-support-packages-behavior-fields">

                <?php _e("This campaign is currently used for recurring subscriptions in the Support packages extension, and if we proceed, it won't be available for donations anymore.<br><br>What should we do next?", 'leyka');

                $support_packages_no_campaign_behavior = get_option('leyka_support_packages_no_campaign_behavior');?>

                <ul>

                    <li><label><input type="radio" name="support-packages-campaign-changed" value="content-open" <?php echo $support_packages_no_campaign_behavior === 'content-open' || !$support_packages_no_campaign_behavior ? 'checked="checked"' : '';?>>&nbsp;<?php _e('Make content open', 'leyka');?></label></li>

                    <li><label><input type="radio" name="support-packages-campaign-changed" value="content-closed" <?php echo $support_packages_no_campaign_behavior === 'content-closed' ? 'checked="checked"' : '';?>>&nbsp;<?php _e('Leave content closed', 'leyka');?></label></li>

                <?php $campaigns_available = get_posts(array(
                    'post_type' => Leyka_Campaign_Management::$post_type,
                    'post_status' => 'publish',
                    'meta_query' => array(
                        // Here user may choose from all campaigns, nor just persistent ones:
                        array('key' => 'is_finished', 'value' => 1, 'compare' => '!=', 'type' => 'NUMERIC',),
                    ),
                    'post__not_in' => array($campaign->ID),
                    'posts_per_page' => 10,
                ));

                if($campaigns_available) {?>
                    <li><label><input type="radio" name="support-packages-campaign-changed" value="another-campaign">&nbsp<?php _e('Select another campaign', 'leyka');?></label></li>
                <?php }?>

                </ul>

                <?php if($campaigns_available) {?>

                <div class="new-campaign" style="display: none;">
                    <?php $list_entries = array();
                    foreach($campaigns_available as $campaign) {
                        $list_entries[$campaign->ID] = $campaign->post_title;
                    }

                    leyka_render_select_field('support_packages_campaign', array(
                        'value' => $campaign->id,
                        'list_entries' => $list_entries,
                    ));?>
                </div>

            </div>

            <?php }?>

            <div id="leyka-loading" style="display: none"><?php _e('Please wait while we are saving your choice...', 'leyka');?></div>
            <div id="leyka-message" style="display: none;" data-success-message="<?php _e('Done! Now saving your campaign changes...', 'leyka');?>" data-error-message="<?php _e('Request error encountered. Please check your campaign option in the Support packages settings manually.');?>" data-validation-error-message="<?php _e('Please choose how the Support packages will act, or the extension is going to work incorrectly.', 'leyka');?>"></div>

        </div>

    <?php
    }

    /**
     * @param $campaign WP_Post
     */
    public function additional_fields_meta_box(WP_Post $campaign) {

        $campaign = new Leyka_Campaign($campaign);?>

        <div class="leyka-admin leyka-settings-page">

            <div class="leyka-options-section">

                <?php function leyka_campaign_additional_field_html($is_template = false, $placeholders = array()) {

                    $placeholders = wp_parse_args($placeholders, array(
                        'id' => '',
                        'box_title' => __('New field', 'leyka'),
                        'type' => '-',
                        'title' => '',
                        'is_required' => false,
                        'for_all_campaigns' => false,
                    ));

                    if($is_template) {

                        $additional_fields_library = leyka_options()->opt('additional_donation_form_fields_library');

                        if($additional_fields_library) {

                            $fields_select_values = array('-' => __('Select the field', 'leyka'),);

                            foreach($additional_fields_library as $field_id => $settings) {
                                $fields_select_values[$field_id] = '['.__($settings['type'], 'leyka').'] '
                                    .$settings['title']
                                    .($settings['is_required'] ? '<span class="field-required">*</span>' : '');
                            }

                        }

                        $fields_select_values['+'] = __('+ Create a new field', 'leyka');?>

                        <div id="item-<?php echo leyka_get_random_string(4);?>" class="multi-valued-item-box field-box <?php echo $is_template ? 'item-template' : '';?>" <?php echo $is_template ? 'style="display: none;"' : '';?>>

                            <h3 class="item-box-title ui-sortable-handle">
                                <span class="draggable"></span>
                                <span class="title" data-empty-box-title="<?php _e('New field', 'leyka');?>">
                                    <?php echo esc_html($placeholders['box_title']);?>
                                </span>
                            </h3>

                            <div class="box-content">

                                <div class="single-line">

                                    <div class="option-block type-select">
                                        <div class="leyka-select-field-wrapper">
                                            <?php leyka_render_select_field('campaign_field_add', array(
                                                'title' => __('Fields available', 'leyka'),
                                                'type' => 'select',
                                                'value' => count($fields_select_values) > 1 ? '-' : '+',
                                                'required' => true,
                                                'list_entries' => $fields_select_values,
                                                'hide_title' => true,
                                            ));?>
                                        </div>
                                        <div class="field-errors"></div>
                                    </div>

                                </div>

                                <div class="campaign-new-additional-field" style="display: none;">
                                    <?php leyka_additional_form_field_main_subfields_html(array());?>
                                </div>

                                <ul class="notes-and-errors">
                                    <li class="phone-field-note" <?php echo $placeholders['type'] === 'phone' ? '' : 'style="display: none;"'?>>
                                        <?php _e("Don't forget to put a point for processing telephone numbers to your Personal data usage terms", 'leyka');?>
                                    </li>
                                </ul>

                                <div class="box-footer">
                                    <div class="remove-campaign-additional-field delete-item">
                                        <?php _e('Remove the field from this campaign', 'leyka');?>
                                    </div>
                                </div>

                            </div>

                        </div>

                    <?php } else { // An existing field ?>

                        <div id="<?php echo $placeholders['id'] ? $placeholders['id'] : 'item-'.leyka_get_random_string(4);?>" class="multi-valued-item-box field-box closed">

                            <h3 class="item-box-title ui-sortable-handle">
                                <span class="draggable"></span>
                                <span class="title">

                                    <?php echo '['._x($placeholders['type'], 'Field type title', 'leyka').'] '.esc_html($placeholders['box_title']);

                                    if($placeholders['is_required']) {?>
                                        <span class="field-required">*</span>
                                    <?php }?>

                                </span>
                            </h3>

                            <div class="box-content">

                                <ul class="notes-and-errors">

                                    <li class="edit-field-note">
                                        <?php echo sprintf(__('If you wish to edit the field settings, you may do it in <a href="%s" target="_blank">fields library</a>.', 'leyka'), admin_url('admin.php?page=leyka_settings&stage=view#'.$placeholders['id']));?>
                                    </li>

                                <?php if($placeholders['for_all_campaigns']) {?>
                                    <li class="no-delete-for-all-campaigns-field-note">
                                        <?php echo sprintf(__('The field cannot be removed from the campaign - it is marked "for all campaigns" in the <a href="%s" target="_blank">fields library</a>.', 'leyka'), admin_url('admin.php?page=leyka_settings&stage=view#'.$placeholders['id']));?>
                                    </li>
                                <?php }?>

                                </ul>

                                <?php if( !$placeholders['for_all_campaigns']) {?>
                                <div class="box-footer">
                                    <div class="remove-campaign-additional-field delete-item">
                                        <?php _e('Remove the field from this campaign', 'leyka');?>
                                    </div>
                                </div>
                                <?php }?>

                            </div>

                        </div>

                    <?php }

                }?>

                <div class="leyka-campaign-additional-fields-wrapper multi-valued-items-field-wrapper">

                    <?php $fields_library = leyka_options()->opt('additional_donation_form_fields_library');?>

                    <div class="leyka-main-multi-items leyka-main-additional-fields" data-min-items="0" data-max-items="<?php echo 10;?>" data-item-inputs-names-prefix="leyka_campaign_field_" data-show-new-item-if-empty="0">

                        <?php // Display existing campaign additional fields (the assoc. array keys order is important):
                        foreach($campaign->additional_fields_settings as $field_id) {

                            // Field is in Campaign settings, but not in the Library - mb, it was deleted from there:
                            if(empty($fields_library[$field_id])) {
                                continue;
                            }

                            // Field is in Campaign settings & in the Library,
                            // but the Library doesn't have the current Campaign set for it:
                            if(
                                !$fields_library[$field_id]['for_all_campaigns']
                                && (
                                    !is_array($fields_library[$field_id]['campaigns'])
                                    || !in_array($campaign->id, $fields_library[$field_id]['campaigns'])
                            )) {
                                continue;
                            }

                            if( // Field is "for all Campaigns", but the current Campaign has been excluded for it
                                $fields_library[$field_id]['for_all_campaigns']
                                && $fields_library[$field_id]['campaigns_exceptions']
                                && is_array($fields_library[$field_id]['campaigns_exceptions'])
                                && in_array($campaign->id, $fields_library[$field_id]['campaigns_exceptions'])
                            ) {
                                continue;
                            }

                            leyka_campaign_additional_field_html(false, array(
                                'id' => $field_id,
                                'box_title' => $fields_library[$field_id]['title'],
                                'type' => $fields_library[$field_id]['type'],
                                'title' => $fields_library[$field_id]['title'],
                                'is_required' => $fields_library[$field_id]['is_required'],
                                'for_all_campaigns' => $fields_library[$field_id]['for_all_campaigns'],
                            ));

                        }

                        // Display the fields "for all Campaigns", if they aren't already in the Campaign fields settings:
                        foreach($fields_library as $field_id => $field_settings) {

                            if(
                                empty($field_settings['for_all_campaigns'])
                                || (
                                    $field_settings['campaigns_exceptions']
                                    && is_array($field_settings['campaigns_exceptions'])
                                    && in_array($campaign->id, $field_settings['campaigns_exceptions'])
                                )
                                || in_array($field_id, $campaign->additional_fields_settings)
                            ) {
                                continue;
                            }

                            leyka_campaign_additional_field_html(false, array(
                                'id' => $field_id,
                                'box_title' => $field_settings['title'],
                                'type' => $field_settings['type'],
                                'title' => $field_settings['title'],
                                'is_required' => $field_settings['is_required'],
                                'for_all_campaigns' => $field_settings['for_all_campaigns'],
                            ));

                        }?>

                    </div>

                    <?php leyka_campaign_additional_field_html(true); // Additional field box template ?>

                    <div class="add-field add-item bottom"><?php _e('Add field', 'leyka');?></div>

                    <input type="hidden" class="leyka-items-options" name="leyka_campaign_additional_fields" value="">

                </div>

            </div>
        </div>

    <?php }

    /**
     * @param $campaign WP_Post
     */
    public function statistics_meta_box(WP_Post $campaign) {

        $campaign = new Leyka_Campaign($campaign);?>

        <div class="stats-block">
            <span class="stats-label"><?php _e('Views:', 'leyka');?></span>
            <span class="stats-data"><?php echo $campaign->views_count;?> <?php _e('times', 'leyka');?></span>
        </div>
        <div class="stats-block">
            <span class="stats-label"><?php _e('Donation attempts:', 'leyka');?></span>
            <span class="stats-data"><?php echo $campaign->submits_count;?> <?php _e('times', 'leyka');?></span>
        </div>

    <?php
    }

    /**
     * @param $campaign WP_Post
     */
    public function annotation_meta_box(WP_Post $campaign) {?>

        <label for="excerpt"></label>
        <textarea id="excerpt" name="excerpt" cols="40" rows="1"><?php echo $campaign->post_excerpt;?></textarea>
        <p><?php _e('Annotation is an optional summary of campaign description that can be used in templates.', 'leyka');?></p>

    <?php }

    /**
     * @param $campaign WP_Post
     */
    public function donations_meta_box(WP_Post $campaign) { $campaign = new Leyka_Campaign($campaign);?>

        <div>
            <a class="button" href="<?php echo admin_url('/post-new.php?post_type=leyka_donation&campaign_id='.$campaign->id);?>"><?php _e('Add correctional donation', 'leyka');?></a>
        </div>

        <table id="donations-data-table" class="leyka-data-table">
            <thead>
                <tr>
                    <td><?php _e('ID', 'leyka');?></td>
                    <td><?php _e('Amount', 'leyka');?></td>
                    <td><?php _e('Donor', 'leyka');?></td>
                    <td><?php _e('Method', 'leyka');?></td>
                    <td><?php _e('Date', 'leyka');?></td>
                    <td><?php _e('Status', 'leyka');?></td>
                    <td><?php _e('Payment type', 'leyka');?></td>
                    <td><?php _e('Actions', 'leyka');?></td>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td><?php _e('ID', 'leyka');?></td>
                    <td><?php _e('Amount', 'leyka');?></td>
                    <td><?php _e('Donor', 'leyka');?></td>
                    <td><?php _e('Method', 'leyka');?></td>
                    <td><?php _e('Date', 'leyka');?></td>
                    <td><?php _e('Status', 'leyka');?></td>
                    <td><?php _e('Payment type', 'leyka');?></td>
                    <td><?php _e('Actions', 'leyka');?></td>
            </tr>
            </tfoot>

            <tbody>
            <?php foreach($campaign->get_donations(array('submitted', 'funded', 'refunded', 'failed')) as $donation) {

                $gateway_label = $donation->gateway_id ? $donation->gateway_label : __('Custom payment info', 'leyka');
                $pm_label = $donation->gateway_id ? $donation->pm_label : $donation->pm;
				$amount_css = $donation->sum < 0 ? 'amount-negative' : 'amount';?>

                <tr <?php echo $donation->type == 'correction' ? 'class="leyka-donation-row-correction"' : '';?>>
                    <td><?php echo $donation->id;?></td>
                    <td><?php echo '<span class="'.$amount_css.'">'.$donation->sum.'&nbsp;'.$donation->currency_label.'</span>';?></td>
                    <td><?php echo $donation->donor_name ? $donation->donor_name : __('Anonymous', 'leyka');?></td>
                    <td><?php echo $pm_label.' ('.mb_strtolower($gateway_label).')';?></td>
                    <td><?php echo $donation->date;?></td>
                    <td><?php echo '<i class="'.esc_attr($donation->status).'">'.mb_ucfirst($donation->status_label).'</i>';?></td>
                    <td><?php echo mb_ucfirst($donation->payment_type_label);?></td>
                    <td><a href="<?php echo admin_url("/post.php?post={$donation->id}&action=edit");?>"><?php echo __('Edit', 'leyka');?></a></td>
                </tr>

            <?php }?>
            </tbody>
        </table>
    <?php
    }

	static function get_card_embed_code($campaign_id, $increase_counters = false, $width = 300, $height = 400) {

		$link = get_permalink($campaign_id);
        $link .= (stristr($link, '?') !== false ? '&' : '?').'embed_object=campaign_card'
            .'&increase_counters='.(int)!!$increase_counters;

		return '<iframe width="'.(int)$width.'" height="'.(int)$height.'" src="'.$link.'"></iframe>';

	}

    static function get_campaign_form_shortcode($campaign_id) {
        return '[leyka_campaign_form id="'.$campaign_id.'"]';
    }

    /**
     * @param $campaign_id integer
     * @param $campaign WP_Post
     */
	public function save_data($campaign_id, WP_Post $campaign) {

	    if($campaign->post_type != Leyka_Campaign_Management::$post_type) {
	        return;
        }

		$campaign = new Leyka_Campaign($campaign);

        $meta = array();

        $_REQUEST['daily_rouble_mode_on'] = empty($_REQUEST['daily_rouble_mode_on']) ? 0 : 1;
        if($campaign->daily_rouble_mode != $_REQUEST['daily_rouble_mode_on']) {
            $meta['_leyka_daily_rouble_mode'] = $_REQUEST['daily_rouble_mode_on'];
        }

        if(isset($_REQUEST['daily_rouble_amounts']) && $campaign->daily_rouble_amounts !== $_REQUEST['daily_rouble_amounts'] ) {
            $meta['_leyka_daily_rouble_amount_variants'] = esc_attr($_REQUEST['daily_rouble_amounts']);
        }

        if(isset($_REQUEST['daily_rouble_pm']) && $campaign->daily_rouble_pm_id !== $_REQUEST['daily_rouble_pm']) {
            $meta['_leyka_daily_rouble_pm_id'] = esc_attr($_REQUEST['daily_rouble_pm']);
        }

        if( !empty($_REQUEST['campaign_type']) && $campaign->type !== $_REQUEST['campaign_type'] ) {
            $meta['campaign_type'] = esc_attr($_REQUEST['campaign_type']);
        }

        if(isset($_REQUEST['donations_type']) && $campaign->donations_types_available != $_REQUEST['donations_type']) {
            $meta['donations_type'] = (array)$_REQUEST['donations_type'];
        } else if( !isset($_REQUEST['donations_type']) && !$campaign->donations_types_available ) {
            $meta['donations_type'] = array('single', 'recurring');
        }

        if(
            isset($_REQUEST['donations_type_default'])
            && $campaign->donations_type_default != $_REQUEST['donations_type_default']
        ) {
            $meta['donations_type_default'] = esc_attr($_REQUEST['donations_type_default']);
        }

        if(isset($_REQUEST['campaign_css']) && $campaign->css !== $_REQUEST['campaign_css']) {
            $meta['campaign_css'] = esc_textarea($_REQUEST['campaign_css']);
        }

        if( !empty($_REQUEST['campaign_template']) && $campaign->template != $_REQUEST['campaign_template'] ) {
            $meta['campaign_template'] = trim($_REQUEST['campaign_template']);
        } else if(
            (isset($meta['campaign_type']) && $meta['campaign_type'] === 'persistent')
            || (isset($_REQUEST['campaign_type']) && $_REQUEST['campaign_type'] === 'persistent')
        ) {
            $meta['campaign_template'] = 'star';
        }

        if( !empty($_REQUEST['payment_title']) && $campaign->payment_title !== $_REQUEST['payment_title'] ) {

            $meta['payment_title'] = trim($_REQUEST['payment_title']);
            $meta['payment_title'] = esc_attr(htmlentities($meta['payment_title'], ENT_QUOTES, 'UTF-8'));

        }

        if( !empty($_REQUEST['cover_bg_color']) && $campaign->cover_bg_color !== $_REQUEST['cover_bg_color'] ) {
            $meta['cover_bg_color'] = sanitize_hex_color($_REQUEST['cover_bg_color']);
        }
        
        if( !empty($_REQUEST['header_cover_type']) && $campaign->header_cover_type !== $_REQUEST['header_cover_type'] ) {
            $meta['header_cover_type'] = $_REQUEST['header_cover_type'];
        }
        
        $_REQUEST['is_finished'] = empty($_REQUEST['is_finished']) ? 0 : 1;
        if($_REQUEST['is_finished'] != $campaign->is_finished) {
            $meta['is_finished'] = $_REQUEST['is_finished'];
        }

        $_REQUEST['hide_cover_tint'] = empty($_REQUEST['hide_cover_tint']) ? 0 : 1;
        if($_REQUEST['hide_cover_tint'] != $campaign->hide_cover_tint) {
            $meta['hide_cover_tint'] = $_REQUEST['hide_cover_tint'];
        }

        if(isset($_REQUEST['campaign_target']) && $_REQUEST['campaign_target'] != $campaign->target) {

            $_REQUEST['campaign_target'] = (float)$_REQUEST['campaign_target'];
            $_REQUEST['campaign_target'] = $_REQUEST['campaign_target'] >= 0.0 ? $_REQUEST['campaign_target'] : 0.0;

            update_post_meta($campaign->id, 'campaign_target', $_REQUEST['campaign_target']);

            $campaign->refresh_target_state();

        }

        // Campaign additional form fields settings:
        if(isset($_REQUEST['leyka_campaign_additional_fields'])) {

            $_REQUEST['leyka_campaign_additional_fields'] = json_decode(urldecode($_REQUEST['leyka_campaign_additional_fields']));
            $updated_additional_fields_settings = array();
            $fields_library = leyka_options()->opt('additional_donation_form_fields_library');

            foreach($_REQUEST['leyka_campaign_additional_fields'] as $field) {

                if( !empty($field->add) && $field->add === '+' ) { // Totally new additional field - first add it in the Library

                    if(empty($field->leyka_field_type) || $field->leyka_field_type === '-' || empty($field->leyka_field_title)) {
                        continue;
                    }

                    $field->id = mb_stripos($field->id, 'item-') === false || empty($field->leyka_field_title) ?
                        $field->id :
                        trim(preg_replace('~[^-a-z0-9_]+~u', '-', mb_strtolower(leyka_cyr2lat($field->leyka_field_title))), '-');

                    if( !isset($fields_library[$field->id]) ) {
                        $fields_library[$field->id] = array(
                            'type' => $field->leyka_field_type,
                            'title' => $field->leyka_field_title,
                            'description' => $field->leyka_field_description,
                            'is_required' => !empty($field->leyka_field_is_required),
                            'campaigns' => array($campaign_id), // By default, new field is just for the currectly edited Campaign
                            'for_all_campaigns' => false,
                        );
                    }

                    $field->add = $field->id;

                } else if( // Extisting (in the Library) field is added to the Campaign settings
                    mb_stristr($field->id, 'item-') !== false
                    && $field->add
                    && isset($fields_library[$field->add])
                    && !in_array($campaign_id, $fields_library[$field->add]['campaigns'])
                ) {
                    $fields_library[$field->add]['campaigns'][] = $campaign_id;
                }

                // $field->add or $field->id is a field ID/slug:
                $updated_additional_fields_settings[] = $field->add ? $field->add : $field->id;

            }

            if(leyka_options()->opt('additional_donation_form_fields_library') != $fields_library) {
                leyka_options()->opt('additional_donation_form_fields_library', $fields_library);
            }

            if($updated_additional_fields_settings != $campaign->additional_fields_settings) {

                // If some Additional fields are removed from Campaign, remove the Campaign ID from their settings in the Library:
                $fields_removed = false;
                foreach($campaign->additional_fields_settings as $field_id) {

                    if( !in_array($field_id, $updated_additional_fields_settings) ) { // The field is removed

                        $fields_removed = true;
                        $campaign_key_in_array = array_search($campaign_id, $fields_library[$field_id]['campaigns']);

                        if( !empty($fields_library[$field_id]['campaigns'][$campaign_key_in_array]) ) {
                            unset($fields_library[$field_id]['campaigns'][$campaign_key_in_array]);
                        }

                    }

                }

                if($fields_removed) {
                    leyka_options()->opt('additional_donation_form_fields_library', $fields_library);
                }

                $meta['leyka_campaign_additional_fields_settings'] = $updated_additional_fields_settings;

            }

//            die();

        }
        // Campaign additional form fields settings - END

        foreach($meta as $campaign_key_in_array => $value) {
            update_post_meta($campaign->id, $campaign_key_in_array, $value);
        }

        /* Support packages - the case of "single available campaign ceased to be available" */
        if( !leyka()->extension_is_active('support_packages') ) {
            return;
        }

        // The case when Packages campaign is reactivated, or there is another campaign available for the extension:
        if(
            (
                $campaign_id == leyka_options()->opt('support_packages_campaign')
                && ($_REQUEST['post_status'] === 'publish' || !empty($_REQUEST['publish']))
                && empty($_REQUEST['is_finished'])
                && get_option('leyka_support_packages_no_campaign_behavior')
            ) || leyka_get_extension_by_id('support_packages')->get_available_campaigns_count()
        ) {

            delete_option('leyka_support_packages_no_campaign_behavior');
            return;

        }
        /* Support packages - END */

	}

    /**
     * @param $columns array
     * @return array
     */
    public function manage_columns_names($columns) {

		$unsort = $columns;
		$columns = array();

		if( !empty($unsort['cb']) ) {

			$columns['cb'] = $unsort['cb'];
			unset($unsort['cb']);

		}

		$columns['ID'] = 'ID';

		if(isset($unsort['title'])) {

			$columns['title'] = $unsort['title'];
			unset($unsort['title']);

		}

		$columns['coll_state'] = __('Collection state', 'leyka');
		$columns['target'] = __('Progress', 'leyka');
		$columns['payment_title'] = __('Payment purpose', 'leyka');
        $columns['shortcode'] = __('Campaign shortcode', 'leyka');

		if(isset($unsort['date'])) {

			$columns['date'] = $unsort['date'];
			unset($unsort['date']);

		}

		if($unsort) {
			$columns = array_merge($columns, $unsort);
        }

		return $columns;

	}

    /**
     * @param $column_name string
     * @param $campaign_id integer
     */
    public function manage_columns_content($column_name, $campaign_id) {

		$campaign = new Leyka_Campaign($campaign_id);

		if($column_name === 'ID') {
			echo (int)$campaign->id;
		} else if($column_name === 'payment_title') {
            echo $campaign->payment_title;
        } else if($column_name === 'coll_state') {

			echo $campaign->is_finished == 1 ?
				'<span class="c-closed">'.__('Closed', 'leyka').'</span>' :
				'<span class="c-opened">'.__('Opened', 'leyka').'</span>';

		} else if($column_name === 'target') {

			if($campaign->target_state === 'no_target') {
				leyka_fake_scale_ultra($campaign);			
			} else {
				leyka_scale_ultra($campaign);
			}

			if($campaign->target_state === 'is_reached' && $campaign->date_target_reached) {?>
		    <span class='c-reached'><?php printf(__('Reached at: %s', 'leyka'), '<time>'.$campaign->date_target_reached.'</time>'); ?></span>
		<?php }

		} else if($column_name === 'shortcode') {?>
            <input type="text" class="embed-code read-only campaign-shortcode" value="<?php echo esc_attr(self::get_campaign_form_shortcode($campaign->ID));?>">
        <?php }

	}

}


class Leyka_Campaign {

	protected $_id;
    protected $_post_object;
    protected $_campaign_meta;

	public function __construct($campaign) {

		if(is_object($campaign)) {

            if(is_a($campaign, 'WP_Post')) {

                $this->_id = $campaign->ID;
                $this->_post_object = $campaign;

            } else if(is_a($campaign, 'Leyka_Campaign')) {
                return $campaign;
            }

		} else if(absint($campaign) > 0) {

			$this->_id = absint($campaign);
            $this->_post_object = get_post($this->_id);

		}

        if( !$this->_post_object || $this->_post_object->post_type != Leyka_Campaign_Management::$post_type ) {
            $this->_id = 0; /** @todo throw new Leyka_Exception() */
        }

        if($this->_id && !$this->_campaign_meta) {

            $meta = get_post_meta($this->_id, '', true);
            $meta = is_array($meta) ? $meta : array();

            if(empty($meta['target_state'])) {

                $this->target_state = $this->_get_calculated_target_state();
                $meta['target_state'] = array($this->target_state);

            }

            if(empty($meta['_leyka_target_reaching_mailout_sent'])) {

                $this->target_reaching_mailout_sent = false;
                $meta['_leyka_target_reaching_mailout_sent'] = array(false);

            }

            if(empty($meta['_leyka_target_reaching_mailout_errors'])) {

                $this->target_reaching_mailout_errors = false;
                $meta['_leyka_target_reaching_mailout_errors'] = array(false);

            }

            if( !isset($meta['is_finished']) ) {

                update_post_meta($this->_id, 'is_finished', 0);
                $meta['is_finished'] = array(0);

            }

            if( !isset($meta['hide_cover_tint']) ) {
                
                update_post_meta($this->_id, 'hide_cover_tint', 0);
                $meta['hide_cover_tint'] = array(0);
                
            }
            
            if( !isset($meta['total_funded']) ) { // If campaign total collected amount is not saved, save it

                $sum = 0.0;
                foreach($this->get_donations(array('funded')) as $donation) {

                    $donation_amount = $donation->main_curr_amount ? $donation->main_curr_amount : $donation->amount;
                    if(is_array($donation_amount) && !empty($donation_amount[0]) && (float)$donation_amount[0] >= 0.0) {
                        $donation_amount = $donation_amount[0];
                    }

                    $sum += $donation_amount;

                }

                update_post_meta($this->_id, 'total_funded', $sum);
                $meta['total_funded'][0] = $sum;

            }

            if( !isset($meta['campaign_type']) ) {

                update_post_meta($this->_id, 'campaign_type', 'temporary');
                $meta['campaign_type'] = array('temporary');

            }
            if( !isset($meta['donations_type']) ) {

                update_post_meta($this->_id, 'donations_type', array());
                $meta['donations_type'] = array();

            }
            if( !isset($meta['donations_type_default']) ) {

                update_post_meta($this->_id, 'donations_type_default', 'single');
                $meta['donations_type_default'] = array('single');

            }
            if( !isset($meta['campaign_css']) ) {

                update_post_meta($this->_id, 'campaign_css', '');
                $meta['campaign_css'] = array('');

            }
            if( !isset($meta['campaign_cover']) ) {

                update_post_meta($this->_id, 'campaign_cover', '');
                $meta['campaign_cover'] = array();

            }
            if( !isset($meta['campaign_logo']) ) {

                update_post_meta($this->_id, 'campaign_logo', '');
                $meta['campaign_logo'] = array();

            }

            $ignore_view_settings = empty($meta['ignore_global_template']) ?
                false : $meta['ignore_global_template'][0] > 0;
            $ignore_view_settings = apply_filters(
                'leyka_campaign_ignore_view_settings',
                $ignore_view_settings,
                $this->_id,
                $this->_post_object
            );

            $this->_campaign_meta = array(
                'payment_title' => empty($meta['payment_title']) ?
                    (empty($this->_post_object) ? '' : $this->_post_object->post_title) : $meta['payment_title'][0],
                'campaign_type' => empty($meta['campaign_type']) ? '-' : $meta['campaign_type'][0],
                'donations_type' => empty($meta['donations_type']) ? array() : $meta['donations_type'][0],
                'donations_type_default' => empty($meta['donations_type_default']) ? false : $meta['donations_type_default'][0],
                'campaign_template' => empty($meta['campaign_template']) ? '' : $meta['campaign_template'][0],
                'campaign_css' => empty($meta['campaign_css']) ? '' : $meta['campaign_css'][0],
                'campaign_cover' => empty($meta['campaign_cover']) ? '' : $meta['campaign_cover'][0],
                'campaign_logo' => empty($meta['campaign_logo']) ? '' : $meta['campaign_logo'][0],
                'campaign_target' => empty($meta['campaign_target']) ? 0 : $meta['campaign_target'][0],
                'ignore_global_template' => $ignore_view_settings,
                'is_finished' => $meta['is_finished'] ? $meta['is_finished'][0] > 0 : 0,
                'target_state' => $meta['target_state'][0],
                'target_reaching_mailout_sent' => $meta['_leyka_target_reaching_mailout_sent'][0],
                'target_reaching_mailout_errors' => $meta['_leyka_target_reaching_mailout_errors'][0],
                'date_target_reached' => empty($meta['date_target_reached']) ? 0 : $meta['date_target_reached'][0],
                'count_views' => empty($meta['count_views']) ? 0 : $meta['count_views'][0],
                'count_submits' => empty($meta['count_submits']) ? 0 : $meta['count_submits'][0],
                'total_funded' => empty($meta['total_funded']) ? 0.0 : $meta['total_funded'][0],
                'hide_cover_tint' => $meta['hide_cover_tint'] ? $meta['hide_cover_tint'][0] > 0 : 0,
                'cover_bg_color' => empty($meta['cover_bg_color']) ? '' : $meta['cover_bg_color'][0],
                'header_cover_type' => empty($meta['header_cover_type']) ? '' : $meta['header_cover_type'][0],
                'daily_rouble_mode' => empty($meta['_leyka_daily_rouble_mode']) ?
                    false : !!$meta['_leyka_daily_rouble_mode'][0],
                'daily_rouble_amount_variants' =>
                    empty($meta['_leyka_daily_rouble_amount_variants']) ? '' : $meta['_leyka_daily_rouble_amount_variants'][0],
                'daily_rouble_pm_id' => empty($meta['_leyka_daily_rouble_pm_id']) ?
                    false : $meta['_leyka_daily_rouble_pm_id'][0],
                'additional_fields_settings' => empty($meta['leyka_campaign_additional_fields_settings']) ?
                    array() : maybe_unserialize($meta['leyka_campaign_additional_fields_settings'][0]),
            );

        }

        return $this;

	}

    protected function _get_calculated_target_state() {
        return empty($this->target) ? 'no_target' : ($this->total_funded >= $this->target ? 'is_reached' : 'in_progress');
    }

    /**
     * Get all Campaign additional fields with their settings.
     *
     * @return array Assoc. array of Campaign additional fields (in correct order) in the form of field_id => field_settings.
     */
    public function get_calculated_additional_fields_settings() {

        $additional_fields_library = leyka_options()->opt('additional_donation_form_fields_library');
        $campaign_additional_fields = array();

//        if( !is_array($this->additional_fields_settings) ) {
//            echo '<pre>HERE: '.print_r($this->additional_fields_settings, 1).'</pre>';
//        }

        foreach($this->additional_fields_settings as $field_id) {

            if( !is_string($field_id) || empty($additional_fields_library[$field_id]) ) {
                continue;
            }

            // The field is still in the Campaign fields settings,
            // but in the Library (global) settings the current Campaign is already excluded for it:
            if(
                $additional_fields_library[$field_id]['for_all_campaigns']
                && $additional_fields_library[$field_id]['campaigns_exceptions']
                && is_array($additional_fields_library[$field_id]['campaigns_exceptions'])
                && in_array($this->id, $additional_fields_library[$field_id]['campaigns_exceptions'])
            ) {
                continue;
            }

            $campaign_additional_fields[$field_id] = $additional_fields_library[$field_id];

            unset(
                $campaign_additional_fields[$field_id]['campaigns'],
                $campaign_additional_fields[$field_id]['for_all_campaigns'],
                $campaign_additional_fields[$field_id]['campaigns_exceptions']
            );

        }

        // Include the fields "for all Campaigns", if they aren't already in the Campaign fields settings
        // (and they aren't excluded for the current Campaign in their field settings):
        foreach($additional_fields_library as $field_id => $field_settings) {

            if(
                empty($field_settings['for_all_campaigns'])
                || (
                    $field_settings['campaigns_exceptions']
                    && is_array($field_settings['campaigns_exceptions'])
                    && in_array($this->id, $field_settings['campaigns_exceptions'])
                )
                || !empty($campaign_additional_fields[$field_id])
            ) {
                continue;
            }

            $campaign_additional_fields[$field_id] = $field_settings;

            unset($field_settings['campaigns'], $field_settings['for_all_campaigns'], $field_settings['campaigns_exceptions']);

        }

	    return $campaign_additional_fields;

    }

    public static function get_additional_fields_settings($campaign_id) {
        return (new Leyka_Campaign($campaign_id))->get_calculated_additional_fields_settings();
    }

    /**
     * A special helper just to evade some type-checking notices.
     * @return array
     */
    protected function _get_donations_types_available() {

        $types_available = $this->donations_types;
        if(in_array('recurring', $types_available) && !leyka_is_recurring_supported()) {
            unset( $types_available[array_search('recurring', $types_available)] );
        }

        return $types_available && is_array($types_available) ? $types_available : array();

    }

    public function __get($field) {

        switch($field) {
            case 'id':
            case 'ID':
                return $this->_id;

            case 'title':
            case 'name':
                return $this->_post_object ? $this->_post_object->post_title : '';

            case 'payment_title': return $this->_campaign_meta['payment_title'];

            case 'type':
            case 'campaign_type':
                return empty($this->_campaign_meta['campaign_type']) ? 'temporary' : $this->_campaign_meta['campaign_type'];

            case 'donations_type': // Donation types checked in campaign settings
            case 'donations_types':
                return $this->_campaign_meta['donations_type'] ?
                    maybe_unserialize($this->_campaign_meta['donations_type']) : array('single', 'recurring');

            case 'donations_type_available': // Donation types really available for campaign
            case 'donations_types_available':
                return $this->_get_donations_types_available();

            case 'donations_type_default':
                $types_available = $this->donations_types_available;

                return count($types_available) > 1
                    && $this->_campaign_meta['donations_type_default']
                    && in_array($this->_campaign_meta['donations_type_default'], array_keys(leyka()->get_donation_types())) ?
                        $this->_campaign_meta['donations_type_default'] :
                        ($types_available ? reset($types_available) : 'single');

            case 'template':
            case 'template_id':
            case 'campaign_template':
            case 'campaign_template_id':
                return $this->_campaign_meta['campaign_template'] === 'default' ?
                    leyka_options()->opt('donation_form_template') : $this->_campaign_meta['campaign_template'];

            case 'css':
            case 'campaign_css':
            case 'additional_css':
            case 'additional_campaign_css':
                return $this->_campaign_meta['campaign_css'] ? $this->_campaign_meta['campaign_css'] : '';

            case 'cover':
            case 'cover_id':
            case 'campaign_cover':
            case 'campaign_cover_id':
                return $this->_campaign_meta['campaign_cover'] ? $this->_campaign_meta['campaign_cover'] : '';

            case 'logo':
            case 'logo_id':
            case 'campaign_logo':
            case 'campaign_logo_id':
                return $this->_campaign_meta['campaign_logo'] ? $this->_campaign_meta['campaign_logo'] : '';

            case 'campaign_target':
            case 'target':
                return isset($this->_campaign_meta['campaign_target']) ? $this->_campaign_meta['campaign_target'] : 0;

            case 'content':
            case 'description':
                return $this->_post_object ? $this->_post_object->post_content : '';

            case 'excerpt':
            case 'post_excerpt':
            case 'short_description':
                return $this->_post_object ? $this->_post_object->post_excerpt : '';

            case 'post_name': return $this->_post_object ? $this->_post_object->post_name : '';
            case 'status': return $this->_post_object ? $this->_post_object->post_status : '';

            case 'permalink':
            case 'url':
                return get_permalink($this->_id);

			case 'is_finished':
			case 'is_closed':
				return $this->_campaign_meta['is_finished'];

            case 'ignore_view_settings':
            case 'ignore_global_template':
            case 'ignore_global_template_settings':
				return $this->_campaign_meta['ignore_global_template'];

            case 'target_state':
                return $this->_campaign_meta['target_state'];
            case 'date_reached':
            case 'target_reached_date':
            case 'date_target_reached':
                $date = $this->_campaign_meta['date_target_reached'];
                return $date ? date(get_option('date_format'), $date) : 0;
            case 'target_reaching_mailout_sent': return !!$this->_campaign_meta['target_reaching_mailout_sent'];
            case 'target_reaching_mailout_errors': return !!$this->_campaign_meta['target_reaching_mailout_errors'];

            case 'views':
            case 'count_views':
            case 'views_count':
                return $this->_campaign_meta['count_views'];

            case 'submits':
            case 'count_submits':
            case 'submits_count':
                return $this->_campaign_meta['count_submits'];

            case 'total_funded':
            case 'total_collected':
            case 'total_donations_funded':
                return $this->_campaign_meta['total_funded'];

            case 'hide_cover_tint': return $this->_campaign_meta['hide_cover_tint'];
            case 'cover_bg_color': return $this->_campaign_meta['cover_bg_color'];
            case 'header_cover_type': return $this->_campaign_meta['header_cover_type'];

            case 'daily_rouble_mode':
            case 'daily_rouble_mode_on':
                return $this->_campaign_meta['daily_rouble_mode'];
            case 'daily_rouble_amounts':
            case 'daily_rouble_amount_variants':
                return $this->_campaign_meta['daily_rouble_amount_variants'];
            case 'daily_rouble_pm_id':
            case 'daily_rouble_pm_full_id':
                return $this->_campaign_meta['daily_rouble_pm_id'];
            case 'daily_rouble_pm': return leyka_get_pm_by_id($this->daily_rouble_pm_full_id, true);

            case 'daily_rouble_mode_on_and_valid':
                if( !$this->daily_rouble_mode_on ) {
                    return false;
                }

                $pm = $this->daily_rouble_pm;
                $variants = array_map(
                    function($value){ return absint($value); },
                    explode(',', $this->daily_rouble_amount_variants)
                );

                return $pm && $pm->has_recurring_support() && $pm->is_active && $variants;

            case 'additional_fields_settings':
                return empty($this->_campaign_meta['additional_fields_settings']) ?
                    array() : $this->_campaign_meta['additional_fields_settings'];

            default:
                return apply_filters('leyka_get_unknown_campaign_field', null, $field, $this);
        }

    }

    public function __set($field, $value) {

        switch($field) {
            case 'target_state':
                if( in_array($value, array_keys(leyka()->get_campaign_target_states())) ) {

                    $this->_campaign_meta['target_state'] = $value;
                    update_post_meta($this->_id, 'target_state', $value);

                }
                break;
            case 'target_reaching_mailout_sent':
                $this->_campaign_meta['target_reaching_mailout_sent'] = !!$value;
                update_post_meta($this->_id, '_leyka_target_reaching_mailout_sent', !!$value);
                break;
            case 'target_reaching_mailout_errors':
                $this->_campaign_meta['target_reaching_mailout_errors'] = !!$value;
                update_post_meta($this->_id, '_leyka_target_reaching_mailout_errors', !!$value);
                break;

            case 'template':
            case 'template_id':
                if( array_key_exists($value, leyka()->get_templates(array('include_deprecated' => true))) ) {

                    $this->_campaign_meta['campaign_template'] = $value;
                    update_post_meta($this->_id, 'campaign_template', $value);

                }
                break;

            case 'daily_rouble_mode':
            case 'daily_rouble_mode_on':
                $this->_campaign_meta['daily_rouble_mode'] = !!$value;
                update_post_meta($this->_id, '_leyka_daily_rouble_mode', !!$value);
                break;

            case 'daily_rouble_amounts':
            case 'daily_rouble_amount_variants':
                $variants = array_map( function($amount){ return absint(trim($amount)); }, explode(',', trim($value)) );
                $value = implode(',', $variants);
                $this->_campaign_meta['daily_rouble_amount_variants'] = $value;
                update_post_meta($this->_id, '_leyka_daily_rouble_amount_variants', $value);
                break;

            case 'daily_rouble_pm':
            case 'daily_rouble_pm_id':
                $pm = leyka_get_pm_by_id($value, true);
                if(is_a($pm, 'Leyka_Payment_Method') && $pm->has_recurring_support()) {

                    $this->_campaign_meta['daily_rouble_pm_id'] = $value;
                    update_post_meta($this->_id, '_leyka_daily_rouble_pm_id', $value);

                }
                break;

            case 'additional_fields_settings':
                $this->_campaign_meta['additional_fields_settings'] = $value;
                update_post_meta($this->_id, 'leyka_campaign_additional_fields_settings', $value);
                break;
            default:
        }

    }

    /**
     * Get all donations of the campaign with given statuses.
     * NOTE: This method is to be called after init (1), or else it will return an empty array.
     *
     * @param $status array Of leyka donation statuses.
     * @return array Of Leyka_Donation objects.
     */
    public function get_donations(array $status = array()) {

        if( !did_action('leyka_cpt_registered') || !$this->_id ) { // Leyka PT statuses isn't there yet
            return array();
        }

        $donations = get_posts(array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => $status ? $status : array('submitted', 'funded', 'refunded', 'failed', 'trash',),
            'nopaging' => true,
            'meta_key' => 'leyka_campaign_id',
            'meta_value' => $this->_id,
        ));

        $count = count($donations);
        for($i = 0; $i < $count; $i++) {
            $donations[$i] = new Leyka_Donation($donations[$i]->ID);
        }

        return $donations;

    }

    /**
     * @param $campaign_id integer
     * @return float
     */
    public static function get_campaign_collected_amount($campaign_id) {

        $campaign_id = (int)$campaign_id;
        if($campaign_id <= 0) {
            return false;
        }

        $campaign = new Leyka_Campaign($campaign_id);

        return $campaign->total_funded > 0.0 ? $campaign->total_funded : 0.0;

    }

    /** @deprecated Use $campaign->total_funded instead. */
    public function get_collected_amount() {
        return $this->total_funded > 0.0 ? $this->total_funded : 0.0;
    }

    /** @todo Make the result a campaign meta instead of a method & refresh the meta like "total_amount" */
    public function get_recurring_subscriptions_amount() {

        if( !$this->_id ) {
            return false;
        }

        $recurring_subscriptions = get_posts(array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => 'funded',
            'meta_query' => array(
                array('key' => 'leyka_campaign_id', 'value' => $this->_id,),
                array('key' => 'leyka_payment_type', 'value' => 'rebill',),
                array('key' => '_rebilling_is_active', 'value' => 0, 'compare' => '!='),
            ),
            'post_parent' => 0,
            'nopaging' => true,
        ));

        $monthly_incoming_amount = 0.0;
        foreach($recurring_subscriptions as $subscription) {

            $subscription = new Leyka_Donation($subscription);
            $monthly_incoming_amount += $subscription->amount;

        }

        return $monthly_incoming_amount;

    }

    public function refresh_target_state() {

        if( !$this->target ) {
            return false;
        }

        $new_target_state = $this->_get_calculated_target_state();
        $meta = array();

        if($new_target_state !== $this->target_state) {

            $meta['target_state'] = $new_target_state;

            if($new_target_state === 'is_reached') {
                $meta['date_target_reached'] = current_time('timestamp');
            }

        } elseif($new_target_state === 'is_reached' && !$this->date_target_reached) {
            $meta['date_target_reached'] = current_time('timestamp');
        } elseif($new_target_state !== 'is_reached' && $this->date_target_reached) {
            $meta['date_target_reached'] = 0;
        }

        if( !isset($meta['target_state']) ) {
            $meta['target_state'] = $new_target_state;
        }

        foreach($meta as $key => $value) {
            update_post_meta($this->_id, $key, $value);
        }

        return $meta['target_state'];

    }

    /**
     * @param $state string|false
     * @return string|array|false
     */
	static function get_target_state_label($state = false) {

        $labels = leyka()->get_campaign_target_states();

        if( !$state ) {
            return $labels;
        } else {
            return !empty($labels[$state]) ? $labels[$state] : false;
        }

	}

    public function increase_views_counter() {

        $this->_campaign_meta['count_views'] = empty($this->_campaign_meta['count_views']) ?
            1 : $this->_campaign_meta['count_views'] + 1;

        update_post_meta($this->_id, 'count_views', $this->_campaign_meta['count_views']);

        return $this;

    }

    public function increase_submits_counter() {

        $this->_campaign_meta['count_submits']++;
        update_post_meta($this->_id, 'count_submits', $this->_campaign_meta['count_submits']);

        return $this;

    }

    /**
     * @param $donation Leyka_Donation|integer|false
     * @param $action string
     * @param $old_sum float|false
     * @return $this|false
     */
    public function update_total_funded_amount($donation = false, $action = '', $old_sum = false) {

        if( !$donation ) { // Recalculate total collected amount for a campaign and recache it

            $sum = 0.0;
            foreach($this->get_donations(array('funded')) as $donation) {
                $sum += $donation->sum_total;
            }

            $this->_campaign_meta['total_funded'] = $sum;
            update_post_meta($this->_id, 'total_funded', $this->_campaign_meta['total_funded']);

        } else { // Add/subtract a sum of a donation from it's campaign metadata

            $donation = leyka_get_validated_donation($donation);
            if( !$donation ) {
                return false;
            }

            if($action == 'remove') { // Subtract given donation's sum from campaign's total_funded
                $sum = -$donation->sum_total;
            } else { // Add given donation's sum to campaign's total_funded

                $old_sum = $old_sum && (float)$old_sum ? round($old_sum, 2) : 0.0;
                if($action == 'update_sum' && $old_sum) { // If donation sum was changed, subtract it from total_funded first
                    $this->_campaign_meta['total_funded'] -= (int)$old_sum;
                }

                $sum = ($donation->status != 'funded' || $donation->campaign_id != $this->_id) && $donation->sum_total > 0 ?
                    -$donation->sum_total : $donation->sum_total;
                $sum = $donation->status == 'trash' ? -$sum : $sum;

            }

            $this->_campaign_meta['total_funded'] = isset($this->_campaign_meta['total_funded']) ?
                $this->_campaign_meta['total_funded'] : 0.0;
            $this->_campaign_meta['total_funded'] += $sum;

            update_post_meta($this->_id, 'total_funded', $this->_campaign_meta['total_funded']);

        }

        $this->refresh_target_state();

        return $this;

    }

    public function delete($force = false) {
        wp_delete_post($this->_id, $force);
    }
    
}