<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Revo
 * Description: The most recent te-st.ru design work, the modern and lightweight step-by-step form template.
 **/

//$active_pm = apply_filters('leyka_form_pm_order', leyka_get_pm_list(true));
//$supported_curr = leyka_get_active_currencies();
//$mode = leyka_options()->opt('donation_sum_field_type'); // fixed/flexible/mixed
//
//global $leyka_current_pm; /** @todo Make it a Leyka_Payment_Form class singleton */
//
//leyka_pf_submission_errors();
//
////add option if we need thumb
//$thumb_url = get_the_post_thumbnail_url($campaign_id, 'post-thumbnail');

//ob_start();

//$currency = "<span class='curr-mark'>&#8381;</span>";
//$currency = "<span class='curr-mark'>РУБ.</span>";
$campaign_id;

$supported_curr = leyka_get_active_currencies();
$amount_default = $supported_curr['rur']['amount_settings']['flexible'];
$amount_min = $supported_curr['rur']['bottom'];
$amount_max = $supported_curr['rur']['top'];
$currency_label = $supported_curr['rur']['label'];

$pm_list = leyka_get_pm_list(true);?>

<form action="#" method="post" novalidate="novalidate" id="<?php echo leyka_pf_get_form_id($campaign_id);?>">

	<!-- Step 1: amount -->
    <div class="step step--amount step--active">

        <div class="step__title step__title--amount"><?php _e('Donation amount', 'leyka');?></div>

        <div class="step__fields amount">
            <!-- @todo Refactor Leyka_Payment_Form so it could work without $payment_method set. Then output the following fields with Leyka_Payment_Form class means -->
            <input type="hidden" class="leyka_donation_currency" name="leyka_donation_currency" data-currency-label="<?php echo $currency_label;?>" value="rur">
            <input type="hidden" name="top_rur" value="<?php echo $amount_max;?>">
            <input type="hidden" name="bottom_rur" value="<?php echo $amount_min;?>">

            <div class="amount__figure">
                <input type="text" name="leyka_donation_amount" value="<?php echo $amount_default;?>" autocomplete="off" placeholder="<?php echo apply_filters('leyka_form_free_amount_placeholder', $amount_default);?>">
                <span class="curr-mark"><?php echo $currency_label;?></span>
            </div>

            <input type="hidden" name="monthly" value="0"><!-- @todo Check if this field is needed -->

            <div class="amount__icon">
                <svg class="svg-icon icon-money-size3"><use xlink:href="#icon-money-size3" /></svg>
                <div class="leyka_donation_amount-error field-error amount__error"></div>
            </div>

            <div class="amount_range">
                <input name="amount-range" type="range" min="<?php echo $amount_min;?>" max="<?php echo $amount_max;?>" step="200" value="<?php echo $amount_default;?>">
                <!-- @todo step also shoud be calculated -->
            </div>

        </div>

        <div class="step__action step__action--amount">
            <?php if(leyka_is_recurring_supported()) {?>

                <a href="cards" class="leyka-js-amount"><?php _e('Support once-only', 'leyka');?></a>
                <a href="person" class="leyka-js-amount monthly">
                    <svg class="svg-icon icon-card"><use xlink:href="#icon-card"></svg><?php _e('Support monthly', 'leyka');?>
                </a>

            <?php } else {?>
                <a href="cards" class="leyka-js-amount"><?php _e('Proceed', 'leyka');?></a>
            <?php }?>
        </div>
    </div>

    <!-- step pm -->
    <div class="step step--cards">

        <div class="step__selection">
            <a href="amount" class="leyka-js-another-step">
                <span class="remembered-amount">#SUM#</span>&nbsp;<span class='curr-mark'><?php echo leyka_options()->opt('main_currency');?></span>
                <span class="remembered-monthly">#IS_RECURRING#</span>
            </a>
        </div>

        <div class="step__title"><?php _e('Payment method', 'leyka');?></div>

        <div class="step__fields payments-grid">
            <!-- hidden field to store choice ? -->
            <?php foreach($pm_list as $pm) {?>

                <div class="payment-opt">
                    <label class="payment-opt__button">
                        <input class="payment-opt__radio" name="payment_option" value="<?php echo esc_attr($pm->full_id);?>" type="radio">
                        <span class="payment-opt__icon">
                            <svg class="svg-icon <?php echo esc_attr($pm->main_icon);?>"><use xlink:href="#<?php echo esc_attr($pm->main_icon);?>"/></svg>
                        </span>
                    </label>
                    <span class="payment-opt__label"><?php echo $pm->label;?></span>
                </div>

            <?php }?>
        </div>

    </div>

    <?php if(leyka_options()->opt('revo_template_ask_donor_data') == 'during-donation') {?>
    <!-- step data -->
    <div class="step step--person">

        <div class="step__selection">
            <a href="amount" class="leyka-js-another-step">
                <span class="remembered-amount">#SUM#</span>&nbsp;<span class='curr-mark'><?php echo leyka_options()->opt('main_currency');?></span>
                <span class="remembered-monthly">#IS_RECURRING#</span>
            </a>
            <a href="cards" class="leyka-js-another-step"><span class="remembered-payment">#PM_LABEL#</span></a>
        </div>

        <div class="step__border">
            <div class="step__title"><?php _e('Who should we thank?', 'leyka');?><!--Кого нам благодарить?--></div>
            <div class="step__fields donor">

                <div class="donor__textfield donor__textfield--name ">
                    <label for="leyka_donor_name">
                        <span class="donor__textfield-label leyka_donor_name-label"><?php _e('Your name', 'leyka');?></span>
                        <span class="donor__textfield-error leyka_donor_name-error">
                            <?php _e('Enter your name', 'leyka');?>
                        </span>
                    </label>
                    <input type="text" name="leyka_donor_name" value="" autocomplete="off">
                </div>

                <div class="donor__textfield donor__textfield--email">
                    <label for="leyka_donor_email">
                        <span class="donor__textfield-label leyka_donor_name-label"><?php _e('Your email', 'leyka');?></span>
                        <span class="donor__textfield-error leyka_donor_email-error">
                            <?php _e('Enter an email in the some@email.com format', 'leyka');?>
                        </span>
                    </label>
                    <input type="email" name="leyka_donor_email" value="" autocomplete="off">
                </div>

                <div class="donor__submit">
                    <input type="submit" value="<?php echo leyka_options()->opt_safe('donation_submit_text');?>">
                </div>

                <?php if(leyka_options()->opt('agree_to_terms_needed')) {?>
                    <div class="donor__oferta">
                        <span><input type="checkbox" name="leyka_agree" value="1" checked="checked">
                        <label for="leyka_agree">
                        <?php echo apply_filters('agree_to_terms_text_text_part', leyka_options()->opt('agree_to_terms_text_text_part')).' ';?>
                            <a href="#" class="leyka-js-oferta-trigger"><?php echo apply_filters('agree_to_terms_text_link_part', leyka_options()->opt('agree_to_terms_text_link_part'));?></a></label></span>
                        <div class="donor__oferta-error leyka_agree-error">
                            <?php _e('Enter an email in the some@email.com format', 'leyka');?>
                        </div>
                    </div>
                <?php }?>
            </div>
        </div>

        <div class="step__note">
<!--            <p><a href="http://www.consultant.ru/document/cons_doc_LAW_162595/" target="_blank">110-ФЗ от 5 мая 2014 года</a> обязывает нас спрашивать имя и почту.</p>-->
        </div>

    </div>
    <?php }?>
</form>