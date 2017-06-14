<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Successful donation page block.
 * Description: A template for the interactive actions block shown on the successful donation page.
 **/

if(
//    !leyka_options()->opt('revo_template_ask_donor_data') == 'success-page' &&
    !leyka_options()->opt('show_success_widget_on_success')
) {
    exit();
}

$donation_id = leyka_remembered_data('donation_id');?>

<div id="leyka-pf-" class="leyka-pf">
    <?php include(LEYKA_PLUGIN_DIR.'assets/svg/svg.svg');?>

    <div class="leyka-pf__final-screen leyka-pf__final-thankyou">

        <svg class="svg-icon icon"><use xlink:href="#pic-heart"></svg>
        <div class="text"><div><?php echo leyka_options()->opt('revo_thankyou_text');?></div></div>

        <div class="leyka-final-subscribe-form">

            <form action="#" class="leyka-success-form" method="post" novalidate="novalidate" <?php echo empty($donation_id) ? 'style="display: none;"' : '';?>>

                <input type="hidden" name="leyka_donation_id" value="<?php echo $donation_id;?>">
                <input type="hidden" name="action" value="leyka_donor_subscription">
                <?php wp_nonce_field('leyka_donor_subscription');?>

                <?php /*if(leyka_options()->opt('revo_template_ask_donor_data') == 'success-page') {?>

                    <div class="thankyou-email-field">
                        <div class="donor__textfield">
                            <input type="text" name="leyka_donor_name" class="required" placeholder="<?php _e('Your name', 'leyka');?>" value="<?php echo leyka_remembered_data('donor_name');?>">
                        </div>
                        <div class="donor__textfield">
                            <input type="email" name="leyka_donor_email" class="required" placeholder="<?php _e('Your email', 'leyka');?>" value="<?php echo leyka_remembered_data('donor_email');?>">
                        </div>
                    </div>

                <?php } else*/ if(leyka_options()->opt('show_success_widget_on_success')) {?>

                    <div class="thankyou-email-field">
                        <div class="donor__textfield">
                            <input type="email" name="leyka_donor_email" class="required" placeholder="<?php _e('Your email', 'leyka');?>" value="<?php echo leyka_remembered_data('donor_email');?>">
                            <span class="donor__textfield-error leyka_donor_email-error">
                                <?php _e('Enter an email in the some@email.com format', 'leyka');?>
                            </span>
                        </div>
                    </div>

                <?php }?>

                <div class="thankyou-email-me-button">
                    <input type="submit" class="leyka-success-submit" name="leyka_success_submit" value="<?php _e('Yes, keep me in touch', 'leyka');?>">
                </div>
                <div class="thankyou-no-email">
                    <a href="<?php echo home_url('/');?>" class="leyka-js-no-subscribe"><?php _e('No, thank you', 'leyka');?></a>
                </div>

            </form>

        </div>

        <div class="informyou-redirect-text"><?php _e('Redirecting in <span class="leyka-redirect-countdown">5</span> seconds...', 'leyka');?></div>

    </div>

    <div class="leyka-pf__final-screen leyka-pf__final-error-message"></div>

    <div class="leyka-pf__final-screen leyka-pf__final-informyou">
        <svg class="svg-icon icon"><use xlink:href="#pic-check-mark"></svg>
        <div class="text"><div><?php echo leyka_options()->opt('revo_thankyou_email_result_text');?></div></div>
        <div class="informyou-redirect-text"><div><?php _e('Redirecting in <span class="leyka-redirect-countdown">5</span> seconds...', 'leyka');?></div></div>
        <div class="leyka-logo"> </div>
    </div>

</div>