<?php if( !defined('WPINC') ) die;
/**
 * The template for displaying a recurring subscription cancelling.
 *
 * @link https://leyka.te-st.ru/campaign/demo-kampaniya/
 *
 * @package Leyka
 * @since 1.0.0
 */

try {
    $donor = new Leyka_Donor(wp_get_current_user());
} catch(Exception $e) {
	wp_die(__("Error: cannot display a page for a given donor.", 'leyka'));
}

$recurring_subscriptions = $donor->get_init_recurring_donations(true, false);

include(LEYKA_PLUGIN_DIR . 'templates/account/header.php');?>

<div id="content" class="site-content leyka-campaign-content">
    
    <section id="primary" class="content-area">
        <main id="main" class="site-main">
            <div class="entry-content">

                <div id="leyka-pf-" class="leyka-pf leyka-pf-star">
                    <div class="leyka-account-form leyka-unsubscribe-campains-forms">
        
                        <form class="leyka-screen-form leyka-unsubscribe-campains-form">

                            <?php if($recurring_subscriptions) {?>
                            
                            <h2><?php _e('Which campaign you want to unsubscibe from?', 'leyka');?></h2>
                            
                            <div class="list">
                                <div class="items">

                                	<?php foreach($recurring_subscriptions as $init_donation) {

                                	    $donation_campaign = new Leyka_Campaign($init_donation->campaign_id);?>

                                    <div class="item">

										<div class="subscription-details">

    										<div class="campaign-title">
                                                <?php echo $init_donation->campaign_title;?>
                                            </div>

                                            <div class="subscription-payment-details">

        										<div class="amount">
                                                    <?php echo $init_donation->amount.' '.$init_donation->currency_label;?>/<?php echo _x('month', 'Recurring interval, as in "[XX Rub in] month"', 'leyka');?>
                                                </div>

                                                <div class="donation-gateway-pm">
                                                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/star-icon-info-small.svg" alt="">
                                                    <span class="gateway"><?php echo $init_donation->gateway_label;?></span> /
                                                    <span class="pm"><?php echo $init_donation->pm_label;?></span>
                                                </div>

                                            </div>

										</div>

                                        <div data-campaign-id="<?php echo $init_donation->campaign_id;?>" data-donation-id="<?php echo $init_donation->id;?>" data-campaign-page-public-url="<?php echo $donation_campaign->permalink;?>" class="action-disconnect"><?php _e('Disable', 'leyka');?></div>

                                    </div>

                                	<?php }?>

                                </div>
                            </div>

                            <?php } else {?>
                            <h2><?php _e('You have no active recurring subscriptions.', 'leyka');?></h2>
                            <?php } ?>

                            <div class="leyka-star-submit">
                                <a href="<?php echo site_url('/donor-account/');?>" class="leyka-star-single-link">
                                    <?php _e('To main' , 'leyka');?>
                                </a>
                            </div>

                        </form>

                        <form class="leyka-screen-form leyka-cancel-subscription-form">
                            
                            <h2><?php _e('We will be grateful if you share why you decided to cancel the subscription?', 'leyka');?></h2>

                            <div class="limit-width">
                                <div class="leyka-cancel-subscription-reason">
                                	<?php foreach(leyka_get_recurring_subscription_cancelling_reasons() as $reason_value => $reason_text) {?>
                                    <span>
                                        <input type="checkbox" name="leyka_cancel_subscription_reason[]" id="leyka_cancel_subscription_reason_<?php echo $reason_value;?>" class="required" value="<?php echo $reason_value;?>">
                                        <label for="leyka_cancel_subscription_reason_<?php echo $reason_value;?>">
                                        	<svg class="svg-icon icon-checkbox-check"><use xlink:href="#icon-checkbox-check"></use></svg>
                                        	<?php echo $reason_text;?>
                                        </label>
                                    </span>
                                	<?php }?>
                                </div>

                                <div class="section unsubscribe-comment">
                                    <div class="section__fields donor">

                                        <?php $field_id = 'leyka-'.wp_rand();?>
                                        <div class="donor__textfield donor__textfield--comment">
                                            <div class="leyka-star-field-frame">
                                                <label for="<?php echo $field_id;?>">
                                                    <span class="donor__textfield-label leyka_donor_custom_reason-label">
                                                        <?php _e('Your reason', 'leyka');?>
                                                    </span>
                                                </label>
                                                <textarea id="<?php echo $field_id;?>" class="leyka-donor-comment" name="leyka_donor_custom_reason"></textarea>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="leyka-hidden-controls">
                                	<input type="hidden" name="leyka_campaign_id" value="">
                                	<input type="hidden" name="leyka_donation_id" value="">
                                	<input type="hidden" name="leyka_campaign_permalink" value="">
                                	<?php wp_nonce_field('leyka_cancel_subscription');?>
                                </div>

                                <div class="leyka-star-field-error-frame">
                                    <span class="donor__textfield-error choose-reason">
                                        <?php _e('Choose unsubscription reason, please', 'leyka');?>
                                    </span>
                                    <span class="donor__textfield-error give-details">
                                        <?php _e('Give some details about your reason', 'leyka');?>
                                    </span>
                                </div>

                                <div class="leyka-star-submit double">

                                    <a href="<?php echo site_url('/donor-account/');?>" class="leyka-star-btn leyka-do-not-unsubscribe">
                                        <?php _e('Do not unsubscribe', 'leyka');?>
                                    </a>

                                    <input type="submit" name="unsubscribe" class="leyka-star-btn secondary last" value="<?php _e('Continue', 'leyka');?>">

                                </div>

                            </div>

                        </form>

                        <form class="leyka-screen-form leyka-confirm-unsubscribe-request-form">

                            <h2><?php _e('Disable subscription?', 'leyka');?></h2>

                            <div class="form-controls">

                                <p><?php _e('We were able to do a lot with the help of your donations. Without your support, it will be harder for us to achieve results. It is a pity that you unsubscribe!', 'leyka');?></p>
                                
                                <div class="form-message"></div>

                                <div class="leyka-star-submit double confirm-unsubscribe-submit">

                                    <a href="#" class="leyka-star-btn leyka-do-not-unsubscribe">
                                        <?php _e('Do not cancel', 'leyka');?>
                                    </a>

                                    <input type="submit" name="unsubscribe" class="leyka-star-btn secondary last" value="<?php _e('Cancel subscription', 'leyka');?>">

                                </div>

                            </div>
                        
                            <div class="leyka-form-spinner"><?php echo leyka_get_ajax_indicator();?></div>

                        </form>

                        <form class="leyka-screen-form leyka-confirm-go-resubscribe-form">

                            <h2><?php _e('Canceling subscription', 'leyka');?></h2>
                            
                            <div class="form-controls">
                                <p><?php _e('Now we will cancel the current subscription and then you can, if you wish, subscribe again to a more convenient amount or method of donation.', 'leyka');?></p>
                                
                                <div class="form-message"></div>

                                <div class="leyka-star-submit double confirm-unsubscribe-submit">
                                    <a href="#" class="leyka-star-btn leyka-do-not-unsubscribe">
                                        <?php _e('Do not cancel', 'leyka');?>
                                    </a>
                                    <input type="submit" name="unsubscribe" class="leyka-star-btn secondary last" value="<?php _e('Cancel subscription', 'leyka');?>">
                                </div>
                            </div>

                            <div class="leyka-form-spinner">
                            	<?php echo leyka_get_ajax_indicator();?>
                            </div>
                        
                        </form>

                        <form class="leyka-screen-form leyka-back-to-account">

                            <div class="form-message"></div>

                            <div class="leyka-star-submit">
                            	<a href="<?php echo site_url('/donor-account/');?>" class="leyka-star-single-link">
                                    <?php _e('Back to the Account' , 'leyka');?>
                                </a>
                            </div>

                        </form>

                    </div>
                </div>
                
            </div>

        </main>
    </section>

</div>

<?php get_footer(); ?>