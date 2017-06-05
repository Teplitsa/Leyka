<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Revo final thanl you page
 * Description: Screen with heart icon
 **/
?>

<div id="leyka-pf-" class="leyka-pf">

<?php if(isset($error) && $error):?>

<div class="leyka-pf__final-screen leyka-pf__final-error">
    <div class="icon"> </div>
    <div class="text"><div class="leyka-js-error-text"><?php _e('Payment error', 'leyka');?></div></div>
    <div class="error-text"><div><?php _e('Perhaps there are problems in the Internet connection, in the operation of the payment system or an internal system error.', 'leyka');?></div></div>
    <div class="error-text"><div><?php _e('Please try <a href="cards" class="leyka-js-another-step">again</a> and if the error recurs, use another payment method or contact <a href="#">technical support</a>.', 'leyka');?></div></div>
    <div class="leyka-logo"> </div>
</div>

<?php else: ?>

<div class="leyka-pf__final-screen leyka-pf__final-thankyou">
<div class="icon"> </div>
<div class="text"><div><?php echo $content;?></div></div>

<?php if(get_post()->ID != leyka_options()->opt('success_page') ||
    !leyka_options()->opt('show_subscription_on_success')): ?>
    
    <!-- empty case -->
    
<?php else: ?>
    
    <div class="leyka-final-subscribe-form">
        <div class="thankyou-email-field">
            <div class="donor__textfield">
                <input type="text" name="leyka_inform_email" value="<?php echo leyka_remembered_data('donor_email');?>" autocomplete="off">
            </div>
        </div>
        <div class="thankyou-email-me-button">
            <a href="#"><?php _e('Yes, keep me in touch please', 'leyka');?></a>
        </div>
        <div class="thankyou-no-email">
            <a href="#" class="leyka-js-no-subscribe"><?php _e('No, thank you', 'leyka');?></a>
        </div>
    </div>
    
<?php endif;?>

</div>

<div class="leyka-pf__final-screen leyka-pf__final-informyou">
    <div class="icon"> </div>
    <div class="text"><div><?php echo leyka_options()->opt('revo_thankyou_email_result_text');?></div></div>
    <div class="informyou-redirect-text"><div><?php _e('Redirect to campaigns page in 5 seconds...', 'leyka');?></div></div>
    <div class="leyka-logo"> </div>
</div>

<?php endif?>

</div>
