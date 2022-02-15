<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Star
 * Description: A modern and lightweight form template
 * 
 * $campaign - current campaign
 **/

/** @var $campaign Leyka_Campaign */
$template_data = Leyka_Star_Template_Controller::get_instance()->get_template_data($campaign);

$is_recurring_campaign = false;
if(count($campaign->donations_types_available) > 1) {
    if('recurring' == $campaign->donations_type_default) {
        $is_recurring_campaign = true;
    }
} else if(count($campaign->donations_types_available) == 1) {
    if(in_array('recurring', $campaign->donations_types_available)) {
        $is_recurring_campaign = true;
    }
}

$is_swipe_amount_variants = count($template_data['amount_variants']) + ((int)($template_data['amount_mode'] != 'fixed')) > 8;
$is_swipe_pm_list = count($template_data['pm_list']) > 3;

$another_amount_title = count($template_data['amount_variants']) > 0 ?
    __('Another amount', 'leyka') : __('Enter amount', 'leyka');?>

<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
	<symbol width="12" height="9" viewBox="0 0 12 9" id="icon-checkbox-check">
		<path d="M3.81353 7.10067L0.968732 4.30201L0 5.24832L3.81353 9L12 0.946309L11.0381 0L3.81353 7.10067Z"></path>
	</symbol>
</svg>

<div id="leyka-pf-<?php echo $campaign->id;?>" class="leyka-pf leyka-pf-star" data-form-id="leyka-pf-<?php echo $campaign->id;?>-star-form" data-leyka-ver="<?php echo Leyka_Payment_Form::get_plugin_ver_for_atts();?>" data-card-2column-breakpoint-width="600">

<div class="leyka-payment-form leyka-tpl-star-form" data-template="star">

    <form id="<?php echo leyka_pf_get_form_id($campaign->id).'-star-form';?>" class="leyka-pm-form leyka-no-validation" action="<?php echo Leyka_Payment_Form::get_form_action();?>" method="post" novalidate="novalidate">

        <div class="section section--periodicity <?php if( !in_array('recurring', $campaign->donations_types_available) ) {?>hidden<?php }?>">

            <div class="section__fields periodicity">
                <a href="#" class="<?php echo 'recurring' === $campaign->donations_type_default ? 'active' : '';?> <?php echo !in_array('recurring', $campaign->donations_types_available) ? "invisible" : "";?>" data-periodicity="monthly" role="tab" aria-selected="<?php echo 'recurring' === $campaign->donations_type_default ? 'true' : 'false';?>"><?php esc_html_e($template_data['payments_amounts_tab_titles']['recurring'], 'leyka');?></a>
                <a href="#" class="<?php echo 'single' === $campaign->donations_type_default ? 'active' : '';?> <?php echo !in_array('single', $campaign->donations_types_available) ? "invisible" : "";?>" data-periodicity="once" role="tab" aria-selected="<?php echo 'single' === $campaign->donations_type_default ? 'true' : 'false';?>"><?php esc_html_e($template_data['payments_amounts_tab_titles']['single'], 'leyka');?></a>
            </div>

        </div>

        <?php if(in_array('recurring', $campaign->donations_types_available)) {?>
        <div class="section section--description"><?php echo leyka_options()->opt_template('recurring_donation_benefits_text', 'star');?></div>
        <?php }?>

        <div class="section section--amount">
        	<div class="section-title-container">

                <div class="section-title-line"></div>
                <div class="section-title-text" role="heading" aria-level="3">
                    <?php echo apply_filters('leyka_template_fields_group_header_label', __('Donation amount', 'leyka'), 'star', $campaign, 'amount');?>
                </div>

            </div>

            <div class="section__fields amount">

            <?php echo Leyka_Payment_Form::get_common_hidden_fields($campaign, [
                'leyka_template_id' => 'star',
                'leyka_amount_field_type' => 'custom',
            ]);

            $form_api = new Leyka_Payment_Form();
            echo $form_api->get_hidden_amount_fields();?>
    
                <div class="amount__figure star-swiper <?php if( !$is_swipe_amount_variants ){?>no-swipe<?php }?>">

                    <div class="arrow-gradient left"></div><a class="swiper-arrow swipe-left" href="#"></a>
                    <div class="arrow-gradient right"></div><a class="swiper-arrow swipe-right" href="#"></a>
                    
                    <div class="<?php if($is_swipe_amount_variants){?>swiper-list<?php }else{?>full-list<?php }?>">

                        <?php foreach($template_data['amount_variants']['single'] as $i => $amount_option) {?>

                            <div class="swiper-item <?php echo $i ? '' : 'selected';?>" style="<?php echo 'single' === $campaign->donations_type_default ? '' : 'display: none';?>" data-payment-type="single" data-payment-amount-option-id="<?php echo $i; ?>" data-value="<?php echo absint($amount_option['amount']);?>" role="button" tabindex="0">

                                <div class="swiper-item-inner">

                                    <span class="amount"><?php echo absint($amount_option['amount']);?></span>
                                    <span class="currency"><?php echo $template_data['currency_label'];?></span>

                                </div>

                            </div>

                        <?php }

                        foreach($template_data['amount_variants']['recurring'] as $i => $amount_option) {?>

                            <div class="swiper-item <?php echo $i ? '' : 'selected';?>" style="<?php echo 'recurring' === $campaign->donations_type_default ? '' : 'display: none';?>" data-payment-type="recurring" data-payment-amount-option-id="<?php echo $i; ?>" data-value="<?php echo absint($amount_option['amount']);?>" role="button" tabindex="0">

                                <div class="swiper-item-inner">

                                    <span class="amount"><?php echo absint($amount_option['amount']);?></span>
                                    <span class="currency"><?php echo $template_data['currency_label'];?></span>

                                </div>

                            </div>

                        <?php }

                        if($template_data['amount_mode'] != 'fixed') {?>

                            <div class="swiper-item flex-amount-item <?php echo empty($template_data['amount_variants']) ? 'selected' : '';?>">

                            	<div class="swiper-item-inner">

                                    <label>
                                        <span class="textfield-label"><?php echo $another_amount_title;?>, <span class="currency"><?php echo $template_data['currency_label'];?></span></span>
                                    </label>

                                    <input type="number" title="<?php esc_html_e('Enter your amount', 'leyka');?>" placeholder="<?php esc_html_e('Enter your amount', 'leyka');?>" data-desktop-ph="<?php echo $another_amount_title;?>" data-mobile-ph="<?php esc_html_e('Enter your amount', 'leyka');?>" name="donate_amount_flex" class="donate_amount_flex" value="<?php echo esc_attr($template_data['amount_default']);?>" min="1" max="999999" aria-label="<?php echo $another_amount_title;?>">

                                </div>

                            </div>

                        <?php }?>
                    </div>

                    <input type="hidden" class="leyka_donation_amount" name="leyka_donation_amount" value="">

                </div>
                
                <input type="hidden" class="leyka_donation_currency" name="leyka_donation_currency" data-currency-label="<?php echo $template_data['currency_label'];?>" value="<?php echo leyka_options()->opt('currency_main');?>">
                <input type="hidden" name="leyka_recurring" class="is-recurring-chosen" value="<?php echo $is_recurring_campaign ? '1' : '0';?>">

            </div>

            <div class="section__fields amount-description">
                <?php $all_amount_options = array_merge($template_data['amount_variants']['single'], $template_data['amount_variants']['recurring']);
                $showed_amount_option_id = $campaign->donations_type_default === 'single' ?
                    array_keys($template_data['amount_variants']['single'])[0] :
                    array_keys($template_data['amount_variants']['recurring'])[0];

                foreach($all_amount_options as $i => $amount_option) { ?>
                    <span data-payment-amount-option-id="<?php echo $i; ?>" style="<?php echo $i !== $showed_amount_option_id ? 'display: none' : '';?>"><?php echo $amount_option['description'] ?></span>
                <?php } ?>
            </div>

        </div>

        <?php do_action('leyka_template_after_amount', 'star', $template_data, $campaign);
        do_action('leyka_template_star_after_amount', $template_data, $campaign);?>

        <div class="section section--cards">

        	<div class="section-title-container">

                <div class="section-title-line"></div>
                <div class="section-title-text" role="heading" aria-level="3">
                    <?php echo apply_filters('leyka_template_fields_group_header_label', __('Payment method', 'leyka'), 'star', $campaign, 'payment_method');?>
                </div>

            </div>

            <div class="section__fields payments-grid">
                <div class="star-swiper <?php echo $is_swipe_pm_list ? '' : 'no-swipe';?>">

                    <div class="arrow-gradient left"></div><a class="swiper-arrow swipe-left" href="#"></a>
                    <div class="arrow-gradient right"></div><a class="swiper-arrow swipe-right" href="#"></a>

                	<div class="<?php echo $is_swipe_pm_list ? 'swiper-list' : 'full-list';?>">
    
                    <?php foreach($template_data['pm_list'] as $number => $pm) { /** @var $pm Leyka_Payment_Method */?>

                        <div class="payment-opt swiper-item <?php echo $number ? '' : 'selected';?>">

                            <div class="swiper-item-inner">

                                <label class="payment-opt__button">

                                    <input class="payment-opt__radio" name="leyka_payment_method" value="<?php echo esc_attr($pm->full_id);?>" type="radio" data-processing="<?php echo $pm->processing_type;?>" data-has-recurring="<?php echo $pm->has_recurring_support() ? '1' : '0';?>" data-ajax-without-form-submission="<?php echo $pm->ajax_without_form_submission ? '1' : '0';?>" aria-label="<?php echo $pm->label;?>">

                                    <span class="payment-opt__icon">
                                        <?php foreach($pm->icons ? $pm->icons : [$pm->main_icon_url] as $icon_url) {?>
                                            <img class="pm-icon <?php echo $pm->full_id.' '.basename($icon_url, '.svg');?>" src="<?php echo $icon_url;?>" alt="">
                                        <?php }?>
                                    </span>

                                </label>

                                <span class="payment-opt__label"><?php echo $pm->label;?></span>

                            </div>

                        </div>
                    <?php }?>
            
                    </div>

                </div>
            </div>

        </div>

        <?php foreach($template_data['pm_list'] as $pm) { /** @var $pm Leyka_Payment_Method */
    
            if($pm->processing_type !== 'static') {
                continue;
            }?>

        <div class="section section--static <?php echo $pm->full_id;?>">
            <div class="section__fields static-text"><?php $pm->display_static_data();?></div>
        </div>

        <?php }?>

        <!-- donor data -->
        <div class="section section--person">

            <div class="section-title-container">

                <div class="section-title-line"></div>
                <div class="section-title-text" role="heading" aria-level="3">
                    <?php echo apply_filters('leyka_template_fields_group_header_label', __('Your data', 'leyka'), 'star', $campaign, 'donor_data');?>
                </div>

            </div>
    
            <div class="section__fields donor">

                <?php $field_id = 'leyka-'.wp_rand();?>
                <div class="donor__textfield donor__textfield--email required">

                    <div class="leyka-star-field-frame">

                        <label for="<?php echo $field_id;?>">
                            <span class="donor__textfield-label leyka_donor_name-label">
                                <?php echo apply_filters('leyka_template_field_label', __('Your email', 'leyka'), 'star', $campaign, 'donor_email', 'donor_data');?>
                            </span>
                        </label>
                        <input type="text" id="<?php echo $field_id;?>" name="leyka_donor_email" value="" autocomplete="off">

                    </div>

                    <div class="leyka-star-field-error-frame">
                        <span class="donor__textfield-error leyka_donor_email-error">
                            <?php echo apply_filters('leyka_template_field_error', __('Please, enter a valid email', 'leyka'), 'star', $campaign, 'donor_email', 'donor_data');?>
                        </span>
                    </div>

                </div>

                <?php $field_id = 'leyka-'.wp_rand();?>
                <div class="donor__textfield donor__textfield--name required">

                    <div class="leyka-star-field-frame">

                        <label for="<?php echo $field_id;?>">
                            <span class="donor__textfield-label leyka_donor_name-label">
                                <?php echo apply_filters(
                                    'leyka_template_field_label',
                                    apply_filters(
                                        'leyka_star_donor_name_field_label', __('First and second name', 'leyka'), $campaign
                                    ),
                                    'star',
                                    $campaign,
                                    'donor_name',
                                    'donor_data'
                                );?>
                            </span>
                        </label>
                        <input id="<?php echo $field_id;?>" type="text" name="leyka_donor_name" value="" autocomplete="off">

                    </div>

                    <div class="leyka-star-field-error-frame">
                        <span class="donor__textfield-error leyka_donor_name-error">
                            <?php echo apply_filters('leyka_template_field_error', __('Enter your name', 'leyka'), 'star', $campaign, 'donor_name', 'donor_data');?>
                        </span>
                    </div>

                </div>

                <?php // Additional fields:

                $form_has_phone_field = false;
                foreach($campaign->get_calculated_additional_fields_settings() as $field_slug => $field) {

                    $field_id = 'leyka-'.wp_rand();
                    $form_has_phone_field = $form_has_phone_field || $field['type'] === 'phone';

                    switch($field['type']) {
                        case 'phone': $text_input_type = 'tel'; break;
                        case 'date': $text_input_type = 'text'; break; // type="date" is not universal yet
                        default:
                            $text_input_type = 'text';
                    }?>

                <div class="donor-additional-field donor__textfield donor__textfield--<?php echo $field['type'];?> donor__textfield--<?php echo $field_slug;?> <?php echo empty($field['is_required']) ? '' : 'required';?>">

                    <div class="leyka-star-field-frame">

                        <label for="<?php echo $field_id;?>">
                            <span class="donor__textfield-label donor__<?php echo $field['type'];?>_field-label leyka_<?php echo $field_slug;?>-label"><?php echo $field['title'];?></span>
                        </label>

                        <input type="<?php echo $text_input_type;?>" id="<?php echo $field_id;?>" name="leyka_<?php echo $field_slug;?>" value="" autocomplete="off" <?php echo $field['type'] === 'phone' ? 'data-inputmask="\'mask\': \''.apply_filters('leyka_front_forms_phone_fields_mask', '+9(999)999-99-99').'\'"' : '';?> <?php echo $field['type'] === 'date' ? 'data-inputmask="\'mask\': \''.apply_filters('leyka_front_forms_date_fields_mask', '99.99.9999').'\'"' : '';?>>

                    </div>

                <?php if( !empty($field['description']) ) {?>

                    <div class="leyka-star-field-description-frame donor__<?php echo $field['type'];?>_field-description leyka_<?php echo $field_slug;?>-description">
                        <?php echo $field['description'];?>
                    </div>

                <?php }?>

                    <div class="leyka-star-field-error-frame">
                        <span class="donor__textfield-error donor__<?php echo $field['type'];?>_field-error leyka_<?php echo $field_slug;?>-error"></span>
                    </div>

                </div>

                <?php }

                // For now, we get field settings only for the Mixplat Mobile PM and only for it's Phone field:
                foreach(leyka_get_special_fields_settings() as $pm_full_id => $special_fields) {

                    if($pm_full_id !== 'mixplat-mobile' || $form_has_phone_field) {
                        continue;
                    }

                    foreach($special_fields as $field_settings) {

                        if(empty($field_settings['type']) || $field_settings['type'] !== 'phone') {
                            continue;
                        }

                        /** @todo Something like: $star_template->render_field($field_settings['type'], $field_settings);*/

                        $field_id = 'leyka-'.wp_rand();?>
                        <div class="donor__textfield donor__textfield--phone special-field <?php echo $pm_full_id;?> <?php echo empty($field_settings['required']) ? '' : 'required';?> <?php echo empty($field_settings['classes']) ? '' : implode(' ', $field_settings['classes']);?>" style="display: none;">

                            <div class="leyka-star-field-frame">

                                <label for="<?php echo $field_id;?>">

                                    <?php $phone_field_label = empty($field_settings['title']) ? __('Your phone number in the 7xxxxxxxxxx format', 'leyka') : $field_settings['title'];?>

                                    <span class="donor__textfield-label leyka_donor_phone-label">
                                        <?php echo apply_filters('leyka_template_field_label', $phone_field_label, 'star', $campaign, 'donor_phone', 'donor_data');?>
                                    </span>

                                </label>

                                <input id="<?php echo $field_id;?>" type="text" name="<?php echo empty($field_settings['name']) ? 'leyka_donor_phone' : $field_settings['name'];?>" value="" maxlength="20" autocomplete="off" placeholder="<?php echo empty($field_settings['placeholder']) ? '' : $field_settings['placeholder'];?>">

                            </div>

                            <div class="leyka-star-field-error-frame">
                                <span class="donor__textfield-error leyka_donor_phone-error">
                                    <?php echo apply_filters('leyka_template_field_error', __('Enter your phone number in the 7xxxxxxxxxx format', 'leyka'), 'star', $campaign, 'donor_phone', 'donor_data');?>
                                </span>
                            </div>

                        </div>

                    <?php }

                } // Additional fields - END

                if(leyka_options()->opt_template('show_donation_comment_field', 'star')) {

                    $field_id = 'leyka-'.wp_rand();?>

                <div class="donor__textfield donor__textfield--comment leyka-field">

                    <div class="leyka-star-field-frame">

                        <label for="<?php echo $field_id;?>">
                            <span class="donor__textfield-label leyka_donor_comment-label">
                                <?php echo leyka_options()->opt_template('donation_comment_max_length', 'star') ? sprintf(__('Your comment (<span class="donation-comment-current-length">0</span> / <span class="donation-comment-max-length">%d</span> symbols)', 'leyka'), leyka_options()->opt_template('donation_comment_max_length', 'star')) : __('Your comment', 'leyka');?>
                            </span>
                        </label>

                        <textarea id="<?php echo $field_id;?>" class="leyka-donor-comment" name="leyka_donor_comment" data-max-length="<?php echo leyka_options()->opt_template('donation_comment_max_length', 'star');?>"></textarea>

                    </div>

                    <div class="leyka-star-field-error-frame">
                        <span class="donor__textfield-error leyka_donor_comment-error">
                            <?php echo apply_filters('leyka_template_field_error', __('Entered value is too long', 'leyka'), 'star', $campaign, 'donor_comment', 'donor_data');?>
                        </span>
                    </div>

                </div>

                <?php }?>

                <?php if(leyka_options()->opt('agree_to_terms_needed') || leyka_options()->opt('agree_to_pd_terms_needed')) {?>

                <div class="donor__oferta">
                    <span>

                    <?php if(leyka_options()->opt('agree_to_terms_needed')) {

                        $field_id = 'leyka-'.wp_rand();?>

                        <input type="checkbox" name="leyka_agree" id="<?php echo $field_id;?>" class="required" value="1" <?php echo leyka_options()->opt('terms_agreed_by_default') ? 'checked="checked"' : '';?>>

                        <label for="<?php echo $field_id;?>">
                        	<svg class="svg-icon icon-checkbox-check"><use xlink:href="#icon-checkbox-check"></use></svg>
                        	
                        <?php echo apply_filters('agree_to_terms_text_text_part', leyka_options()->opt('agree_to_terms_text_text_part')).' ';

                        if(leyka_options()->opt('agree_to_terms_link_action') === 'popup') {?>
                            <a href="#" class="leyka-js-oferta-trigger">
                        <?php } else {?>
                            <a target="_blank" href="<?php echo leyka_get_terms_of_service_page_url();?>">
                        <?php }?>
                                <?php echo apply_filters('agree_to_terms_text_link_part', leyka_options()->opt('agree_to_terms_text_link_part'));?>
                            </a>
                        </label>

                    <?php }?>

                    <?php if(leyka_options()->opt('agree_to_pd_terms_needed')) {

                        $field_id = 'leyka-'.wp_rand();?>

                        <input type="checkbox" name="leyka_agree_pd" id="<?php echo $field_id;?>" class="required" value="1" <?php echo leyka_options()->opt('pd_terms_agreed_by_default') ? 'checked="checked"' : '';?>>

                        <label for="<?php echo $field_id;?>">
                        	<svg class="svg-icon icon-checkbox-check"><use xlink:href="#icon-checkbox-check"></use></svg>

                        <?php echo apply_filters('agree_to_pd_terms_text_text_part', leyka_options()->opt('agree_to_pd_terms_text_text_part')).' ';

                        if(leyka_options()->opt('agree_to_pd_terms_link_action') === 'popup') {?>
                            <a href="#" class="leyka-js-pd-trigger">
                        <?php } else {?>
                            <a target="_blank" href="<?php echo leyka_get_terms_of_pd_usage_page_url();?>">
                        <?php }?>
                        <?php echo apply_filters('agree_to_pd_terms_text_link_part', leyka_options()->opt('agree_to_pd_terms_text_link_part'));?>
                            </a>

                        </label>

                    <?php }?>

                    </span>

                </div>

                <?php }?>

                <div class="donor__submit">
                    <?php echo apply_filters('leyka_star_template_final_submit', '<input type="submit" disabled="disabled" class="leyka-default-submit" value="'.leyka_options()->opt_template('donation_submit_text', 'star').'">');?>
                </div>

                <div class="single-pm-icon"></div>

            </div>
                
        </div>

    </form>

    <?php if($template_data['platform_signature_on_form_enabled']) {?>
        <div class="section section--signature">
            <div id="leyka-platform-signature">
                <span id="leyka-signature-icon"></span>
                <span id="leyka-signature-text"><?php echo __('Made with <a href="https://leyka.te-st.ru/" target="_blank">Leyka</a>', 'leyka'); ?></span>
            </div>
        </div>
    <?php } ?>


    <div class="leyka-pf__overlay"></div>
    <?php if(leyka_options()->opt('agree_to_terms_needed')) {?>
    <div class="leyka-pf__agreement oferta">
        <div class="agreement__frame">
            <div class="agreement__flow"><?php echo leyka_get_terms_text();?></div>
        </div>
        <a href="#" class="agreement__close">
            <?php echo leyka_options()->opt('leyka_agree_to_terms_text_text_part').' '.leyka_options()->opt('leyka_agree_to_terms_text_link_part');?>
        </a>
    </div>
    <?php }?>

    <?php if(leyka_options()->opt('agree_to_pd_terms_needed')) {?>
    <div class="leyka-pf__agreement pd">
        <div class="agreement__frame">
            <div class="agreement__flow"><?php echo leyka_get_pd_terms_text();?></div>
        </div>
        <a href="#" class="agreement__close">
            <?php echo leyka_options()->opt('agree_to_pd_terms_text_text_part').' '.leyka_options()->opt('agree_to_pd_terms_text_link_part');?>
        </a>
    </div>
    <?php }?>

</div>

<div id="leyka-submit-errors" class="leyka-submit-errors" style="display:none">
</div>

<div class="leyka-pf__redirect">
    <div class="waiting">
        <div class="waiting__card">
            <div class="loading">
            	<?php echo leyka_get_ajax_indicator();?>
            </div>
            <div class="waiting__card-text"><?php echo apply_filters('leyka_short_gateway_redirect_message', __('Awaiting for the safe payment page redirection...', 'leyka'));?></div>
        </div>
    </div>
</div>

</div>