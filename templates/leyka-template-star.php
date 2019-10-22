<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Star
 * Description: A modern and lightweight form template
 * 
 * $campaign - current campaign
 * 
 **/

$template_data = Leyka_Star_Template_Controller::get_instance()->get_template_data($campaign);

$is_recurring_campaign = false;
if(count($campaign->donations_types_available) > 1) {
    if('recurring' == $campaign->donations_type_default) {
        $is_recurring_campaign = true;
    }
} elseif(count($campaign->donations_types_available) == 1) {
    if(in_array('recurring', $campaign->donations_types_available)) {
        $is_recurring_campaign = true;
    }
}

$is_swipe_amount_variants = count($template_data['amount_variants']) + ((int)($template_data['amount_mode'] != 'fixed')) > 8;
$is_swipe_pm_list = count($template_data['pm_list']) > 3;

$another_amount_title = count($template_data['amount_variants']) > 0 ?
    __('Another amount', 'leyka') : esc_html__('Enter amount', 'leyka');?>

<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
	<symbol width="12" height="9" viewBox="0 0 12 9" id="icon-checkbox-check">
		<path d="M3.81353 7.10067L0.968732 4.30201L0 5.24832L3.81353 9L12 0.946309L11.0381 0L3.81353 7.10067Z"></path>
	</symbol>
</svg>

<div id="leyka-pf-<?php echo $campaign->id;?>" class="leyka-pf leyka-pf-star" data-form-id="leyka-pf-<?php echo $campaign->id;?>-star-form">

<div class="leyka-payment-form leyka-tpl-star-form" data-template="star">

    <form id="<?php echo leyka_pf_get_form_id($campaign->id).'-star-form';?>" class="leyka-pm-form leyka-no-validation" action="<?php echo Leyka_Payment_Form::get_form_action();?>" method="post" novalidate="novalidate">

        <div class="section section--periodicity <?php if(!in_array('recurring', $campaign->donations_types_available)){?>hidden<?php }?>">

            <div class="section__fields periodicity">
                <a href="#" class="<?php echo 'recurring' === $campaign->donations_type_default ? 'active' : '';?> <?php echo !in_array('recurring', $campaign->donations_types_available) ? "invisible" : "";?>" data-periodicity="monthly"><?php esc_html_e('Monthly', 'leyka');?></a>
                <a href="#" class="<?php echo 'single' === $campaign->donations_type_default ? 'active' : '';?> <?php echo !in_array('single', $campaign->donations_types_available) ? "invisible" : "";?>" data-periodicity="once"><?php esc_html_e('Once', 'leyka');?></a>
            </div>

        </div>
        
        <div class="section section--description">
        	<?php esc_html_e('We will be happy with a small but monthly help, this gives us confidence in the future and the ability to plan our activities.', 'leyka');?>
        </div>

        <div class="section section--amount">
        	<div class="section-title-container"><div class="section-title-line"></div><div class="section-title-text"><?php esc_html_e('Donation amount', 'leyka');?></div></div>

            <div class="section__fields amount">

            <?php echo Leyka_Payment_Form::get_common_hidden_fields($campaign, array(
                'leyka_template_id' => 'star',
                'leyka_amount_field_type' => 'custom',
            ));

            $form_api = new Leyka_Payment_Form();
            echo $form_api->get_hidden_amount_fields();?>
    
                <div class="amount__figure star-swiper <?php if(!$is_swipe_amount_variants){?>no-swipe<?php }?>">
                    <div class="arrow-gradient left"></div><a class="swiper-arrow swipe-left" href="#"></a>
                    <div class="arrow-gradient right"></div><a class="swiper-arrow swipe-right" href="#"></a>
                    
                    <div class="<?php if($is_swipe_amount_variants){?>swiper-list<?php }else{?>full-list<?php }?>">

                        <?php foreach($template_data['amount_variants'] as $i => $amount) {?>
                            <div class="swiper-item <?php echo $i ? "" : "selected";?>" data-value="<?php echo (int)$amount;?>"><div class="swiper-item-inner"><span class="amount"><?php echo (int)$amount;?></span><span class="currency"><?php echo $template_data['currency_label'];?></span></div></div>
                        <?php }?>
        
                        <?php if($template_data['amount_mode'] != 'fixed') {?>
                            <div class="swiper-item flex-amount-item <?php if(!count($template_data['amount_variants'])):?>selected<?php endif;?>">
                            	<div class="swiper-item-inner">
                                <label for="leyka-flex-amount">
                                    <span class="textfield-label"><?php echo $another_amount_title;?>, <span class="currency"><?php echo $template_data['currency_label'];?></span></span>
                                </label>
                                <input type="number" title="<?php esc_html_e('Enter your amount', 'leyka');?>" placeholder="<?php esc_html_e('Enter your amount', 'leyka');?>" data-desktop-ph="<?php echo $another_amount_title;?>" data-mobile-ph="<?php esc_html_e('Enter your amount', 'leyka');?>" name="donate_amount_flex" class="donate_amount_flex" value="<?php echo esc_attr($template_data['amount_default']);?>" min="1" max="999999">
                                </div>
                            </div>
                        <?php }?>
                    </div>
                    <input type="hidden" class="leyka_donation_amount" name="leyka_donation_amount" value="">
                </div>
                
                <input type="hidden" class="leyka_donation_currency" name="leyka_donation_currency" data-currency-label="<?php echo $template_data['currency_label'];?>" value="<?php echo leyka_options()->opt('main_currency');?>">
                <input type="hidden" name="leyka_recurring" class="is-recurring-chosen" value="<?php echo $is_recurring_campaign ? "1" : "0";?>">
            </div>
    
        </div>
        
    
        <div class="section section--cards">
        	<div class="section-title-container"><div class="section-title-line"></div><div class="section-title-text"><?php esc_html_e('Payment method', 'leyka');?></div></div>
    
            <div class="section__fields payments-grid">
                <div class="star-swiper  <?php if(!$is_swipe_pm_list){?>no-swipe<?php }?>">
                    <div class="arrow-gradient left"></div><a class="swiper-arrow swipe-left" href="#"></a>
                    <div class="arrow-gradient right"></div><a class="swiper-arrow swipe-right" href="#"></a>
                    
                	<div class="<?php if($is_swipe_pm_list){?>swiper-list<?php }else{?>full-list<?php }?>">
    
                    <?php foreach($template_data['pm_list'] as $number => $pm) { /** @var $pm Leyka_Payment_Method */?>
            
                        <div class="payment-opt swiper-item <?php echo $number ? "" : "selected";?>">
                        <div class="swiper-item-inner">
                            <label class="payment-opt__button">
                                <input class="payment-opt__radio" name="leyka_payment_method" value="<?php echo esc_attr($pm->full_id);?>" type="radio" data-processing="<?php echo $pm->processing_type;?>" data-has-recurring="<?php echo $pm->has_recurring_support() ? '1' : '0';?>" data-ajax-without-form-submission="<?php echo $pm->ajax_without_form_submission ? '1' : '0';?>">
                                <span class="payment-opt__icon">
                                    <?php foreach($pm->icons ? $pm->icons : array($pm->main_icon_url) as $icon_url) {?>
                                        <img class="pm-icon" src="<?php echo $icon_url;?>" alt="">
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
    
            <div class="section__fields static-text">
                <?php $pm->display_static_data();?>
            </div>
    
        </div>
    
        <?php }?>

        <!-- donor data -->
        <div class="section section--person">
        	<div class="section-title-container"><div class="section-title-line"></div><div class="section-title-text"><?php esc_html_e('Your data', 'leyka');?></div></div>
    
            <div class="section__fields donor">

                <?php $field_id = 'leyka-'.wp_rand();?>
                <div class="donor__textfield donor__textfield--email required">
                    <div class="leyka-star-field-frame">
                        <label for="<?php echo $field_id;?>">
                            <span class="donor__textfield-label leyka_donor_name-label"><?php _e('Your email', 'leyka');?></span>
                        </label>
                        <input type="email" id="<?php echo $field_id;?>" name="leyka_donor_email" value="" autocomplete="off">
                    </div>
                    <div class="leyka-star-field-error-frame">
                        <span class="donor__textfield-error leyka_donor_email-error">
                            <?php _e('Enter an email in the some@email.com format', 'leyka');?>
                        </span>
                    </div>
                </div>

                <?php $field_id = 'leyka-'.wp_rand();?>
                <div class="donor__textfield donor__textfield--name required">
                    <div class="leyka-star-field-frame">
                        <label for="<?php echo $field_id;?>">
                            <span class="donor__textfield-label leyka_donor_name-label">
                                <?php echo apply_filters('leyka_star_donor_name_field_label', __('First and second name', 'leyka'), $campaign);?>
                            </span>
                        </label>
                        <input id="<?php echo $field_id;?>" type="text" name="leyka_donor_name" value="" autocomplete="off">
                    </div>
                    <div class="leyka-star-field-error-frame">
                        <span class="donor__textfield-error leyka_donor_name-error">
                            <?php _e('Enter your name', 'leyka');?>
                        </span>
                    </div>
                </div>

                <?php // For now, we get field settings only for the Mixplat Mobile PM and only for it's Phone field:
                foreach(leyka_get_special_fields_settings() as $pm_full_id => $special_fields) {

                    if($pm_full_id !== 'mixplat-mobile') {
                        return;
                    }

                    foreach($special_fields as $field_settings) {

                        if(empty($field_settings['type']) || $field_settings['type'] !== 'phone') {
                            continue;
                        }

                        /** @todo Something like such: $star_template->render_field($field_settings['type'], $field_settings);*/

                        $field_id = 'leyka-'.wp_rand();?>
                        <div class="donor__textfield donor__textfield--phone special-field <?php echo $pm_full_id;?> <?php echo empty($field_settings['required']) ? '' : 'required';?> <?php echo empty($field_settings['classes']) ? '' : implode(' ', $field_settings['classes']);?>" style="display: none;">

                            <div class="leyka-star-field-frame">

                                <label for="<?php echo $field_id;?>">
                                    <span class="donor__textfield-label leyka_donor_phone-label">
                                        <?php echo empty($field_settings['title']) ? __('Your phone number in the 7xxxxxxxxxx format', 'leyka') : $field_settings['title'];?>
                                    </span>
                                </label>

                                <input id="<?php echo $field_id;?>" type="text" name="<?php echo empty($field_settings['name']) ? 'leyka_donor_phone' : $field_settings['name'];?>" value="" maxlength="20" autocomplete="off" placeholder="<?php echo empty($field_settings['placeholder']) ? '' : $field_settings['placeholder'];?>">

                            </div>

                            <div class="leyka-star-field-error-frame">
                                <span class="donor__textfield-error leyka_donor_phone-error">
                                    <?php _e('Enter your phone number in the 7xxxxxxxxxx format', 'leyka');?>
                                </span>
                            </div>

                        </div>

                    <?php }

                }

                if(leyka_options()->opt_template('show_donation_comment_field')) {

                    $field_id = 'leyka-'.wp_rand();?>

                <div class="donor__textfield donor__textfield--comment leyka-field">
                    <div class="leyka-star-field-frame">
                        <label for="<?php echo $field_id;?>">
                            <span class="donor__textfield-label leyka_donor_comment-label"><?php echo leyka_options()->opt_template('donation_comment_max_length') ? sprintf(__('Your comment (<span class="donation-comment-current-length">0</span> / <span class="donation-comment-max-length">%d</span> symbols)', 'leyka'), leyka_options()->opt_template('donation_comment_max_length')) : __('Your comment', 'leyka');?></span>
                        </label>
                        <textarea id="<?php echo $field_id;?>" class="leyka-donor-comment" name="leyka_donor_comment" data-max-length="<?php echo leyka_options()->opt_template('donation_comment_max_length');?>"></textarea>
                    </div>
                    <div class="leyka-star-field-error-frame">
                        <span class="donor__textfield-error leyka_donor_comment-error"><?php _e('Entered value is too long', 'leyka');?></span>
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

                    <?php if(leyka_options()->opt('agree_to_pd_terms_needed')) {

                        $field_id = 'leyka-'.wp_rand();?>

                        <input type="checkbox" name="leyka_agree_pd" id="<?php echo $field_id;?>" class="required" value="1" <?php echo leyka_options()->opt('pd_terms_agreed_by_default') ? 'checked="checked"' : '';?>>

                        <label for="<?php echo $field_id;?>">
                        	<svg class="svg-icon icon-checkbox-check"><use xlink:href="#icon-checkbox-check"></use></svg>
                        	
                        <?php echo apply_filters('agree_to_pd_terms_text_text_part', leyka_options()->opt('agree_to_pd_terms_text_text_part')).' ';?>
                            <a href="#" class="leyka-js-pd-trigger">
                                <?php echo apply_filters('agree_to_pd_terms_text_link_part', leyka_options()->opt('agree_to_pd_terms_text_link_part'));?>
                            </a>
                        </label>

                    <?php }?>

                    </span>
                    <?php }?>

                </div>

                <?php }?>

                <div class="donor__submit">
                    <?php echo apply_filters('leyka_star_template_final_submit', '<input type="submit" disabled="disabled" class="leyka-default-submit" value="'.leyka_options()->opt_template('donation_submit_text').'">');?>
                </div>

            </div>
                
        </div>
    </form>

    <div class="leyka-pf__overlay"></div>
    <?php if(leyka_options()->opt('agree_to_terms_needed')) {?>
    <div class="leyka-pf__agreement oferta">
        <div class="agreement__frame">
            <div class="agreement__flow">
                <?php echo apply_filters('leyka_terms_of_service_text', do_shortcode(leyka_options()->opt('terms_of_service_text')));?>
            </div>
        </div>
        <a href="#" class="agreement__close">
            <?php echo leyka_options()->opt('leyka_agree_to_terms_text_text_part').' '.leyka_options()->opt('leyka_agree_to_terms_text_link_part');?>
        </a>
    </div>
    <?php }?>

    <?php if(leyka_options()->opt('agree_to_pd_terms_needed')) {?>
    <div class="leyka-pf__agreement pd">
        <div class="agreement__frame">
            <div class="agreement__flow">
                <?php echo apply_filters('leyka_terms_of_pd_usage_text', do_shortcode(leyka_options()->opt('pd_terms_text')));?>
            </div>
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