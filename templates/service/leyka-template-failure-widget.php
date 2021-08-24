<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Failed donation page block.
 * Description: A template for the interactive actions block shown on the failed donation page.
 **/

$donation_id = leyka_remembered_data('donation_id');
$campaign = null;
$campaign_id = null;

if($donation_id) {

    $donation = Leyka_Donations::get_instance()->get_donation($donation_id);
    $campaign_id = $donation ? $donation->campaign_id : null;
    $campaign = new Leyka_Campaign($campaign_id);

}?>

<div id="leyka-pf-" class="leyka-pf">
    <?php include(LEYKA_PLUGIN_DIR.'assets/svg/svg.svg');?>

    <div class="leyka-pf__final-screen leyka-pf__final-error">

        <svg class="svg-icon icon"><use xlink:href="#pic-red-cross"></svg>
        <div class="text"><div class="leyka-js-error-text"><?php _e('Payment error', 'leyka');?></div></div>
        <div class="error-text"><div><?php _e('Perhaps there are problems in the Internet connection, in the operation of the payment system or an internal system error. The money will return to your account.', 'leyka');?></div></div>

        <div class="error-text"><div>
            <?php $support_email = leyka_get_website_tech_support_email();
           if($support_email) {
               printf(__("We've received the error report and are working to fix it. Please try to <a href='%s' class='leyka-js-try-again'>donate again</a>. If the error continues to occur, please use another payment method or <a href='mailto:%s'>contact our technical support</a>.", 'leyka'), $campaign ? $campaign->url : home_url('/'), $support_email);
           } else {
               printf(__("We've received the error report and are working to fix it. Please try to <a href='%s' class='leyka-js-try-again'>donate again</a>. If the error continues to occur, please use another payment method or try donating again later.", 'leyka'), $campaign ? $campaign->url : home_url('/'));
           }?></div></div>
        <div class="leyka-logo"> </div>

    </div>

</div>