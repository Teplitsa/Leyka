<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Successful donation page block.
 * Description: A template for the interactive actions block shown on the successful donation page.
 **/

if( !leyka_options()->opt_template('show_success_widget_on_success') ) {
    return;
}

$donation_id = leyka_remembered_data('donation_id');

if( !$donation_id ) {
    return;
}

$donation = Leyka_Donations::get_instance()->get_donation($donation_id);
$campaign = $donation->campaign;

$template_id = $campaign ? $campaign->template_id : leyka_remembered_data('template_id');?>

<div id="content" class="site-content leyka-campaign-content">

<section id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="entry-content">
            <div id="leyka-pf-" class="leyka-pf leyka-pf-star">

                <div class="leyka-pf__final-screen">

                    <div class="leyka-screen-form leyka-screen-thankyou <?php echo $template_id === 'need-help' ? 'leyka-need-help-thankyou' : '';?>">

                        <h1><?php _e('Thank you for your donation!', 'leyka');?></h1>
                        <p><?php _e('We will be happy with a small but monthly assistance, this gives us confidence in the future and the opportunity to plan our activities.', 'leyka');?></p>

                        <div class="leyka-pf__final-thankyou">

                            <div class="leyka-final-subscribe-form">
                                <h2><?php _e("Let's stay in touch.", 'leyka');?></h2>

                                <form action="#" class=" leyka-success-form" method="post" novalidate="novalidate" <?php echo empty($donation_id) ? 'style="display: none;"' : '';?>>

                                    <input type="hidden" name="leyka_donation_id" value="<?php echo $donation_id;?>">
                                    <input type="hidden" name="action" value="leyka_donor_subscription">
                                    <?php wp_nonce_field('leyka_donor_subscription');?>

                                    <div class="section section--person">
                                        <div class="section__fields donor">
                                            <?php $field_id = 'leyka-'.wp_rand();?>
                                            <div class="donor__textfield donor__textfield--email required focus">
                                                <div class="leyka-star-field-frame">
                                                    <label for="<?php echo $field_id;?>">
                                                        <span class="donor__textfield-label leyka_donor_name-label"><?php _e('Email', 'leyka');?></span>
                                                    </label>
                                                    <input type="email" id="<?php echo $field_id;?>" name="leyka_donor_email" value="<?php echo leyka_remembered_data('donor_email');?>" autocomplete="off" placeholder="<?php _e('Your email', 'leyka');?>">
                                                </div>
                                                <div class="leyka-star-field-error-frame">
                                                    <span class="donor__textfield-error leyka_donor_email-error">
                                                        <?php _e('Enter an email in the some@email.com format', 'leyka');?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="thankyou-email-me-button">
                                        <input type="submit" class="leyka-success-submit" name="leyka_success_submit" value="<?php _e('Subscribe on news', 'leyka');?>">
                                    </div>

                                    <div class="leyka-star-submit">
                                        <a href="<?php echo home_url();?>" class="leyka-star-btn leyka-js-no-subscribe"><?php _e('No, thank you', 'leyka');?></a>
                                    </div>

                                </form>
                            </div>

                            <div class="informyou-redirect-text">
                                <?php _e('Redirecting in <span class="leyka-redirect-countdown">5</span> seconds...', 'leyka');?>
                            </div>
                        </div>

                        <div class="leyka-pf__final-screen leyka-pf__final-error-message"></div>

                        <div class="leyka-pf__final-screen leyka-pf__final-informyou">
                            <div class="text"><div><?php echo leyka()->opt('revo_thankyou_email_result_text');?></div></div>
                            <div class="informyou-redirect-text">
                                <div><?php _e('Redirecting in <span class="leyka-redirect-countdown">5</span> seconds...', 'leyka');?></div>
                            </div>
                            <div class="leyka-logo"> </div>
                        </div>

                    </div>

                </div>

            </div>


        </div>
    </main>
</section>
</div>