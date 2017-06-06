<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Successful donation page block.
 * Description: A template for the interactive actions block shown on the successful donation page.
 **/

$donation_id = leyka_remembered_data('donation_id');
//$campaign = null;
//$campaign_id = null;
//
//if($donation_id) {
//
//    $donation = new Leyka_Donation($donation_id);
//    $campaign_id = $donation ? $donation->campaign_id : null;
//    $campaign = new Leyka_Campaign($campaign_id);
//
//}?>

<div id="leyka-pf-" class="leyka-pf">

<?php //if(isset($error) && $error) {?>
<!---->
<!--<div class="leyka-pf__final-screen leyka-pf__final-error">-->
<!--    <div class="icon"> </div>-->
<!--    <div class="text"><div class="leyka-js-error-text">--><?php //_e('Payment error', 'leyka');?><!--</div></div>-->
<!--    <div class="error-text"><div>--><?php //_e('Perhaps there are problems in the Internet connection, in the operation of the payment system or an internal system error.', 'leyka');?><!--</div></div>-->
<!--    <div class="error-text leyka-js-try-again-block" data-campaign-url="--><?php //echo $campaign ? $campaign->url : '';?><!--"><div>--><?php //_e('Please try <a href="#" class="leyka-js-try-again">again</a> and if the error recurs, use another payment method or contact <a href="#">technical support</a>.', 'leyka');?><!--</div></div>-->
<!--    <div class="leyka-logo"> </div>-->
<!--</div>-->
<!---->
<?php //} else {?>

    <div class="leyka-pf__final-screen leyka-pf__final-thankyou">

        <div class="icon"> </div>
        <div class="text"><div><?php echo $content;?></div></div>

        <div class="leyka-final-subscribe-form">
            <div class="thankyou-email-field">
                <div class="donor__textfield">
                    <input type="text" name="leyka_donor_email" value="<?php echo leyka_remembered_data('donor_email');?>" autocomplete="off">
                </div>
            </div>
            <div class="thankyou-email-me-button">
                <a href="#"><?php _e('Yes, keep me in touch please', 'leyka');?></a>
            </div>
            <div class="thankyou-no-email">
                <a href="#" class="leyka-js-no-subscribe"><?php _e('No, thank you', 'leyka');?></a>
            </div>
        </div>

        <div class="informyou-redirect-text"><div><?php _e('Redirect to site home page in <span class="leyka-redirect-countdown">5</span> seconds...', 'leyka');?></div></div>

    </div>

<?php //}?>

    <div class="leyka-pf__final-screen leyka-pf__final-informyou">
        <div class="icon"> </div>
        <div class="text"><div><?php echo leyka_options()->opt('revo_thankyou_email_result_text');?></div></div>
        <div class="informyou-redirect-text"><div><?php _e('Redirect to site home page in <span class="leyka-redirect-countdown">5</span> seconds...', 'leyka');?></div></div>
        <div class="leyka-logo"> </div>
    </div>

</div>