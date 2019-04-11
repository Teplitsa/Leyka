<?php
/**
 * The template for displaying leyka persistent campaign
 *
 * @link https://leyka.te-st.ru/campaign/demo-kampaniya/
 *
 * @package Leyka
 * @since 1.0.0
 */

$leyka_account_page_title = esc_html__('Personal account', 'leyka');

include(LEYKA_PLUGIN_DIR . 'templates/account/header.php');

$donor_id = get_current_user_id();?>

<div id="content" class="site-content leyka-campaign-content">
        
	<section id="primary" class="content-area">
		<main id="main" class="site-main">
			<div class="entry-content">
			
				<div class="leyka-pf leyka-pf-star">
					<div class="leyka-account-form">
				
						<form class="leyka-screen-form">
							
							<h2><?php _e('Personal account', 'leyka'); // Личный кабинет?></h2>
							
							<p><?php _e('We are grateful for your support!', 'leyka'); // Мы благодарны вам за оказываемую поддержку!?></p>

							<div class="list subscribed-campaigns-list">
								<h3 class="list-title"><?php _e('Recurring donations campaigns', 'leyka'); // Кампании с ежемесячными пожертвованиями?></h3>
                                <?php $recurring_subscriptions = leyka_get_init_recurring_donations($donor_id, false);

                                if($recurring_subscriptions) {?>

                                <div class="items">

                                    <?php foreach($recurring_subscriptions as $init_donation) {?>
									<div class="item <?php if($init_donation->cancel_recurring_requested) {?>subscription-canceling<?php }?>">
										<div class="subscription-details">
    										<div class="campaign-title">
                                                <?php echo $init_donation->campaign_payment_title;?>
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
										<div class="subscription-status">
    										<span class="status">
                                                <?php echo $init_donation->cancel_recurring_requested ? esc_html__('Canceling', 'leyka') : esc_html__('Active', 'leyka');?>
                                            </span>
										</div>
									</div>
                                    <?php }?>

								</div>

                                <?php } else {?>
                                <div class="donations-history-empty"><?php _e('There are no active recurring subscriptions.', 'leyka');?></div>
                                <?php }?>
							</div>

							<div class="list leyka-star-history">

								<h3 class="list-title"><?php _e('Donations history', 'leyka') // История пожертвований?></h3>

                                <?php $donations = leyka_get_donor_donations($donor_id);
                                $donor_donations_count = leyka_get_donor_donations_count($donor_id);

                                $donations_list_pages_count = $donor_donations_count/LEYKA_DONOR_ACCOUNT_DONATIONS_PER_PAGE;
                                if($donations_list_pages_count > (int)$donations_list_pages_count) {
                                    $donations_list_pages_count = (int)$donations_list_pages_count + 1;
                                }

                                if($donations) {?>

                                <div class="donations-history items" data-donations-total-pages="<?php echo $donations_list_pages_count;?>" data-donations-current-page="1" data-donor-id="<?php echo $donor_id;?>">

                                <?php foreach($donations as $donation) {
                                    echo leyka_get_donor_account_donations_list_item_html(false, $donation)."\n";
                                }?>

                                </div>

                                <?php } else {?>
                                    <div class="donations-history-empty">
                                        <?php _e('There are no donations yet.', 'leyka');?>
                                    </div>
                                <?php }

                                if($donor_donations_count > LEYKA_DONOR_ACCOUNT_DONATIONS_PER_PAGE) {?>
                                    <div class="leyka-star-submit">

                                        <a href="#" class="leyka-star-single-link internal donations-history-more">
                                            <?php _e('Load more', 'leyka');?>
                                        </a>

                                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('leyka_get_donor_donations_history');?>">

                                		<?php echo leyka_get_ajax_indicator();?>

                                    </div>
                                <?php }?>

							</div>

							<p class="leyka-we-need-you">
                                <?php echo sprintf(__('You can always <a href="%s">cancel your recurring donations</a>.<br>But we will struggle without your support.', 'leyka'), home_url('/donor-account/cancel-subscription/')); // Вы всегда можете <a href="?leyka-screen=cancel-subscription">отключить ваше ежемесячное пожертвование.</a><br />Но нам будет без вас трудно.?>
                            </p>

						</form>

					</div>
				</div>

			</div>
		</main><!-- #main -->
	</section><!-- #primary -->


</div><!-- #content -->

<?php get_footer(); ?>