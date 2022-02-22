<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Revo
 * Description: A modern and lightweight step-by-step form template
 * Deprecated: true
 *
 * $campaign - current campaign
 * 
 **/

/** @var $campaign Leyka_Campaign */
$template_data = Leyka_Revo_Template_Controller::get_instance()->get_template_data($campaign);?>

<form id="<?php echo leyka_pf_get_form_id($campaign->id).'-revo-form';?>" class="leyka-inline-campaign-form leyka-revo-form" data-template="revo" action="<?php echo Leyka_Payment_Form::get_form_action();?>" method="post" novalidate="novalidate">

	<!-- Step 1: amount -->
    <div class="step step--amount step--active">

        <div class="step__title step__title--amount"><?php _e('Donation amount', 'leyka');?></div>

        <div class="step__fields amount">

        <?php echo Leyka_Payment_Form::get_common_hidden_fields($campaign, [
            'leyka_template_id' => 'revo',
            'leyka_amount_field_type' => 'custom',
        ]);

        $form_api = new Leyka_Payment_Form();
        echo $form_api->get_hidden_amount_fields();?>

            <div class="amount__figure">

                <input type="text" class="leyka_donation_amount" name="leyka_donation_amount" value="<?php echo $template_data['amount_default'];?>" autocomplete="off" placeholder="<?php echo apply_filters('leyka_form_free_amount_placeholder', $template_data['amount_default']);?>" data-default-value="<?php echo $template_data['amount_default'];?>" data-min-value="<?php echo $template_data['amount_min'];?>" data-max-value="<?php echo $template_data['amount_max_total'];?>">
                <span class="curr-mark"><?php echo $template_data['currency_label'];?></span>

                <input type="hidden" class="leyka_donation_currency" name="leyka_donation_currency" data-currency-label="<?php echo $template_data['currency_label'];?>" value="<?php echo leyka_options()->opt('currency_main');?>">

            </div>

            <input type="hidden" name="leyka_recurring" class="is-recurring-chosen" value="0">

            <div class="amount__icon">
                <svg class="svg-icon icon-money-size3"><use xlink:href="#icon-money-size3" /></svg>
                <div class="leyka_donation_amount-error field-error amount__error"><?php printf(__('Please enter valid amount <br />from %s to %s %s', 'leyka'), $template_data['amount_min'], $template_data['amount_max_total'], $template_data['currency_label'] )?></div>
            </div>

            <div class="amount__range_wrapper">
                <div class="amount__range_custom">
                    <svg class="svg-icon range-bg"><use xlink:href="#icon-input-range-gray" /></svg>
                    <div class="range-color-wrapper">
                    	<svg class="svg-icon range-color"><use xlink:href="#icon-input-range-green" /></svg>
                    </div>
                    <svg class="svg-icon range-circle"><use xlink:href="#pic-input-range-circle" /></svg>
                </div>
                <div class="amount__range_overlay"></div>

                <div class="amount_range">
                    <input name="amount-range" type="range" min="<?php echo $template_data['amount_min'];?>" max="<?php echo $template_data['amount_max'];?>" step="10" data-default-value="<?php echo $template_data['amount_default'];?>" value="<?php echo $template_data['amount_default'];?>">
                </div>
            </div>

        </div>

        <div class="step__action step__action--amount">
        <?php if(leyka_is_recurring_supported()) {?>

            <a href="cards" class="leyka-js-amount"><?php _e('Support once', 'leyka');?></a>
            <a href="person" class="leyka-js-amount monthly">
                <svg class="svg-icon icon-card"><use xlink:href="#icon-card"></svg><?php _e('Support monthly', 'leyka');?>
            </a>

        <?php } else {?>
            <a href="cards" class="leyka-js-amount"><?php _e('Proceed', 'leyka');?></a>
        <?php }?>
        </div>
    </div>

    <!-- Step 2: PM -->
    <div class="step step--cards">

        <div class="step__selection">
            <a href="amount" class="leyka-js-another-step">
                <span class="remembered-amount">#SUM#</span><span class="curr-mark"><?php echo $template_data['currency_label'];?></span><span class="remembered-monthly"><?php _e('monthly', 'leyka');?></span>
            </a>
        </div>

        <div class="step__title"><?php _e('Payment method', 'leyka');?></div>

        <div class="step__fields payments-grid">

        <?php $max_pm_number = leyka_options()->opt_template('show_donation_comment_field') ? 6 : 4;
        foreach($template_data['pm_list'] as $number => $pm) { /** @var $pm Leyka_Payment_Method */

            // Max. 4 PM blocks for forms without comment field, or max. 6 PM blocks otherwise:
            if($number > $max_pm_number) {
                break;
            }?>

            <div class="payment-opt">

                <label class="payment-opt__button">

                    <input class="payment-opt__radio" name="leyka_payment_method" value="<?php echo esc_attr($pm->full_id);?>" type="radio" data-processing="<?php echo $pm->processing_type;?>" data-has-recurring="<?php echo $pm->has_recurring_support() ? '1' : '0';?>" data-ajax-without-form-submission="<?php echo $pm->ajax_without_form_submission ? '1' : '0';?>">

                    <span class="payment-opt__icon">
                        <?php if(leyka_url_exists($pm->main_icon_url)) {?>
                        <img src="<?php echo esc_attr($pm->main_icon_url);?>" alt="<?php echo esc_attr($pm->label);?>">
                        <?php } else {
                            echo esc_html($pm->label);
                        }?>
                    </span>

                </label>

                <?php if(leyka_url_exists($pm->main_icon_url)) {?>
                <span class="payment-opt__label"><?php echo $pm->label;?></span>
                <?php }?>

            </div>
        <?php }?>

        </div>

    </div>

    <?php foreach($template_data['pm_list'] as $pm) { /** @var $pm Leyka_Payment_Method */

        if($pm->processing_type !== 'static') {
            continue;
        }?>
    <div class="step step--static <?php echo $pm->full_id;?>">
        <div class="step__selection">
            <a href="amount" class="leyka-js-another-step">
                <span class="remembered-amount">#SUM#</span><span class="curr-mark"><?php echo $template_data['currency_label'];?></span><span class="remembered-monthly"><?php _e('monthly', 'leyka');?></span>
            </a>
            <a href="cards" class="leyka-js-another-step"><span class="remembered-payment">#PM_LABEL#</span></a>
        </div>

        <div class="step__border">

        	<div class="step__fields static-text">
        		<?php $pm->display_static_data();?>

                <div class="static__complete-donation">
                    <input class="leyka-js-complete-donation" value="<?php echo leyka_options()->opt_safe('revo_donation_complete_button_text');?>">
                </div>

        	</div>

    	</div>
    </div>

    <?php }?>

    <!-- Maybe, step 3: donor data -->
    <div class="step step--person">

        <div class="step__selection">
            <a href="amount" class="leyka-js-another-step">
                <span class="remembered-amount">#SUM#</span><span class="curr-mark"><?php echo $template_data['currency_label'];?></span><span class="remembered-monthly"><?php _e('monthly', 'leyka');?></span>
            </a>
            <a href="cards" class="leyka-js-another-step"><span class="remembered-payment">#PM_LABEL#</span></a>
        </div>

        <div class="step__border">
            <div class="step__title"><?php _e('Whom should we thank?', 'leyka');?></div>
            <div class="step__fields donor">

                <?php $field_id = 'leyka-'.wp_rand();?>
                <div class="donor__textfield donor__textfield--name ">
                    <label for="<?php echo $field_id;?>">
                        <span class="donor__textfield-label leyka_donor_name-label">
                            <?php echo apply_filters('leyka_revo_donor_name_field_label', __('Your name', 'leyka'), $campaign);?>
                        </span>
                        <span class="donor__textfield-error leyka_donor_name-error">
                            <?php _e('Enter your name', 'leyka');?>
                        </span>
                    </label>
                    <input id="<?php echo $field_id;?>" type="text" name="leyka_donor_name" value="" autocomplete="off">
                </div>

                <?php $field_id = 'leyka-'.wp_rand();?>
                <div class="donor__textfield donor__textfield--email">
                    <label for="<?php echo $field_id;?>">
                        <span class="donor__textfield-label leyka_donor_name-label"><?php _e('Your email', 'leyka');?></span>
                        <span class="donor__textfield-error leyka_donor_email-error">
                            <?php _e('Enter an email in the some@email.com format', 'leyka');?>
                        </span>
                    </label>
                    <input type="email" id="<?php echo $field_id;?>" name="leyka_donor_email" value="" autocomplete="off">
                </div>

                <?php if(leyka_options()->opt_template('show_donation_comment_field')) { $field_id = 'leyka-'.wp_rand();?>
                <div class="donor__textfield donor__textfield--comment leyka-field">
                    <label for="<?php echo $field_id;?>">
                        <span class="donor__textfield-label leyka_donor_comment-label"><?php echo leyka_options()->opt_template('donation_comment_max_length') ? sprintf(__('Your comment (<span class="donation-comment-current-length">0</span> / <span class="donation-comment-max-length">%d</span> symbols)', 'leyka'), leyka_options()->opt_template('donation_comment_max_length')) : __('Your comment', 'leyka');?></span>
                        <span class="donor__textfield-error leyka_donor_comment-error"><?php _e('Entered value is too long', 'leyka');?></span>
                    </label>
                    <textarea id="<?php echo $field_id;?>" class="leyka-donor-comment" name="leyka_donor_comment" data-max-length="<?php echo leyka_options()->opt_template('donation_comment_max_length');?>"></textarea>
                </div>
                <?php }?>

                <div class="donor__submit">
                    <?php echo apply_filters('leyka_revo_template_final_submit', '<input type="submit" class="leyka-default-submit" value="'.leyka_options()->opt_template('donation_submit_text').'">');?>
                </div>

                <?php if(leyka_options()->opt('agree_to_terms_needed') || leyka_options()->opt('agree_to_pd_terms_needed')) {?>

                <div class="donor__oferta">
                    <span>

                    <?php if(leyka_options()->opt('agree_to_terms_needed')) {

                        $field_id = 'leyka-'.wp_rand();?>

                        <input type="checkbox" name="leyka_agree" id="<?php echo $field_id;?>" class="required" value="1" <?php echo leyka_options()->opt('terms_agreed_by_default') ? 'checked="checked"' : ''; ?>>

                        <label for="<?php echo $field_id;?>">
                        <?php echo apply_filters('agree_to_terms_text_text_part', leyka_options()->opt('agree_to_terms_text_text_part')).' ';

                        if(leyka_options()->opt('agree_to_terms_link_action') === 'popup') { ?>
                            <a href="#" class="leyka-js-oferta-trigger">
                        <?php } else {?>
                            <a target="_blank" href="<?php echo leyka_get_terms_of_service_page_url(); ?>">
                        <?php }

                        echo apply_filters('agree_to_terms_text_link_part', leyka_options()->opt('agree_to_terms_text_link_part'));?>
                            </a>
                        </label>

                    <?php }

                    if(leyka_options()->opt('agree_to_pd_terms_needed')) {

                        $field_id = 'leyka-'.wp_rand();?>

                        <input type="checkbox" name="leyka_agree_pd" id="<?php echo $field_id;?>" class="required" value="1" <?php echo leyka_options()->opt('pd_terms_agreed_by_default') ? 'checked="checked"' : '';?>>

                        <label for="<?php echo $field_id;?>">
                        <?php echo apply_filters('agree_to_pd_terms_text_text_part', leyka_options()->opt('agree_to_pd_terms_text_text_part')).' ';?>
                            <a href="#" class="leyka-js-pd-trigger">
                                <?php echo apply_filters('agree_to_pd_terms_text_link_part', leyka_options()->opt('agree_to_pd_terms_text_link_part'));?>
                            </a>
                        </label>

                    <?php }?>

                    </span>

                    <div class="donor__oferta-error leyka_agree-error leyka_agree_pd-error">
                        <?php _e('You should check out and accept the Terms to donate', 'leyka');?>
                    </div>

                </div>
                <?php }?>

            </div>
        </div>

        <div class="step__note">
			<p><?php _e('We will send the donation success notice to this address', 'leyka');?></p>
        </div>

    </div>

</form>