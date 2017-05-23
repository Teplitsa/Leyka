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

$currency = "<span class='curr-mark'>&#8381;</span>";
//$currency = "<span class='curr-mark'>РУБ.</span>";

echo '<pre>' . print_r('HERE are REVO form', 1) . '</pre>';