<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Successful donation page block.
 * Description: A template for the interactive actions block shown on the successful donation page.
 **/

if(
    !leyka_options()->opt('revo_template_ask_donor_data') == 'success-page' &&
    !leyka_options()->opt('show_subscription_on_success')
) {
    exit();
}

$donation_id = leyka_remembered_data('donation_id');?>

<div id="leyka-pf-" class="leyka-pf">

    <div class="leyka-pf__final-screen leyka-pf__final-thankyou">

        <div class="icon"> </div>
        <div class="text"><div><?php echo get_the_content();?></div></div>

        <div class="leyka-final-subscribe-form">

            <form action="#" class="leyka-success-form" method="post" novalidate="novalidate" <?php //echo empty($donation_id) ? 'style="display: none;"' : '';?>>

                <input type="hidden" name="leyka_donation_id" value="<?php echo $donation_id;?>">
                <input type="hidden" name="action" value="leyka_donor_subscription">
                <?php wp_create_nonce('leyka_donor_subscription');?>

                <?php if(leyka_options()->opt('revo_template_ask_donor_data') == 'success-page') {?>

                    <div class="thankyou-email-field">
                        <div class="donor__textfield">
                            <input type="text" name="leyka_donor_name" placeholder="<?php _e('Your name', 'leyka');?>" value="<?php echo leyka_remembered_data('donor_name');?>">
                        </div>
                        <div class="donor__textfield">
                            <input type="text" name="leyka_donor_email" placeholder="<?php _e('Your email', 'leyka');?>" value="<?php echo leyka_remembered_data('donor_email');?>">
                        </div>
                    </div>

                <?php } else if(leyka_options()->opt('show_subscription_on_success')) {?>

                    <div class="thankyou-email-field">
                        <div class="donor__textfield">
                            <input type="text" name="leyka_donor_email" placeholder="<?php _e('Your email', 'leyka');?>" value="<?php echo leyka_remembered_data('donor_email');?>">
                        </div>
                    </div>

                <?php }?>

                <div class="thankyou-email-me-button">
<!--                    <a href="#">--><?php //_e('Yes, keep me in touch', 'leyka');?><!--</a>-->
                    <input type="submit" class="leyka-success-submit" name="leyka_success_submit" value="<?php _e('Yes, keep me in touch', 'leyka');?>">
                </div>
                <div class="thankyou-no-email">
                    <a href="<?php echo home_url('/');?>" class="leyka-js-no-subscribe"><?php _e('No, thank you', 'leyka');?></a>
                </div>

            </form>

        </div>

        <div class="informyou-redirect-text"><?php _e('Redirect to site home page in <span class="leyka-redirect-countdown">5</span> seconds...', 'leyka');?></div>

    </div>

    <div class="leyka-pf__final-screen leyka-pf__final-informyou">
        <div class="icon"> </div>
        <div class="text"><div><?php echo leyka_options()->opt('revo_thankyou_email_result_text');?></div></div>
        <div class="informyou-redirect-text"><div><?php _e('Redirect to site home page in <span class="leyka-redirect-countdown">5</span> seconds...', 'leyka');?></div></div>
        <div class="leyka-logo"> </div>
    </div>

</div>