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

include(LEYKA_PLUGIN_DIR . 'templates/account/header.php'); ?>

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
                                <?php $recurring_subscriptions = leyka_get_init_recurring_donations();

                                if($recurring_subscriptions) {?>

                                <div class="items">

                                    <?php foreach($recurring_subscriptions as $init_donation) {?>
									<div class="item">
										<span class="campaign-title">
                                            <?php echo $init_donation->campaign_payment_title;?>
                                        </span>
										<span class="amount">
                                            <?php echo $init_donation->amount.' '.$init_donation->currency_label;?>/<?php echo _x('month', 'Recurring interval, as in "[XX Rub in] month"', 'leyka');?>.
                                        </span>
									</div>
                                    <?php }?>

								</div>

                                <?php } else {?>
                                <div class="donations-history-empty"><?php _e('There are no active recurring subscriptions.', 'leyka');?></div>
                                <?php }?>
							</div>

							<div class="list leyka-star-history">

								<h3 class="list-title"><?php _e('Donations history', 'leyka') // История пожертвований?></h3>

                                <?php $donations = leyka_get_donor_donations();
                                $donor_donations_count = leyka_get_donor_donations_count();

                                $donations_list_pages_count = $donor_donations_count/LEYKA_DONOR_ACCOUNT_DONATIONS_PER_PAGE;
                                if($donations_list_pages_count > (int)$donations_list_pages_count) {
                                    $donations_list_pages_count = (int)$donations_list_pages_count + 1;
                                }

                                if($donations) {?>

                                <div class="donations-history items" data-donations-total-pages="<?php echo $donations_list_pages_count;?>" data-donations-current-page="1">

                                <?php foreach($donations as $donation) {

                                    if($donation->status === 'failed') { $item_class = 'error'; $tooltip_class = 'error'; }
                                    else if($donation->status === 'refunded') { $item_class = 'refund'; $tooltip_class = 'notice'; }
                                    else if($donation->type === 'single') { $item_class = 'no-pay'; $tooltip_class = 'funded'; }
                                    else if($donation->type === 'rebill') { $item_class = 'pay'; $tooltip_class = 'funded'; }?>

                                    <div class="item <?php echo $donation->status;?> <?php echo $donation->type;?> <?php echo $item_class;?>">
                                        <h4 class="item-title">
                                            <span class="field-q"><span class="field-q-tooltip <?php echo 'status-'.$donation->status;?> <?php echo 'type-'.$donation->type;?> <?php echo $tooltip_class;?>">
                                                <?php echo $donation->type_description;?>
                                                <br><br>
                                                <?php echo $donation->status_description;?>
                                            </span></span>
                                            <?php echo $donation->amount.' '.$donation->currency_label;?>
                                        </h4>
                                        <span class="date"><?php echo $donation->date;?></span>
                                        <p><?php echo '«'.$donation->campaign_title.'»';?></p>

                                        <div class="donation-gateway-pm">
                                            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-info.svg" alt="">
                                            <span class="gateway"><?php echo $donation->gateway_label;?></span> /
                                            <span class="pm"><?php echo $donation->pm_label;?></span>
                                        </div>

                                    </div>
                                <?php }?>

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

                                        <div class="form-ajax-indicator" style="display: none;">
                                            <div class="loading">
                                                <div class="spinner">
                                                    <div class="bounce1"></div>
                                                    <div class="bounce2"></div>
                                                    <div class="bounce3"></div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                <?php }?>

							</div>
						
							<p class="leyka-we-need-you">
                                <?php echo sprintf(__('You can always <a href="%s">cancel your recurring donations</a>.<br>But we will struggle without your support.', 'leyka'), home_url('/donor-account/?leyka-screen=cancel-subscription')); // Вы всегда можете <a href="?leyka-screen=cancel-subscription">отключить ваше ежемесячное пожертвование.</a><br />Но нам будет без вас трудно.?>
                            </p>
							
						</form>
						
					</div>
				</div>
			
			</div>
		</main><!-- #main -->
	</section><!-- #primary -->


</div><!-- #content -->

<?php get_footer(); ?>