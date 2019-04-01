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
                                <div class="no-recurring"><?php _e('There are no active recurring subscriptions.', 'leyka');?></div>
                                <?php }?>
							</div>
							
							<div class="list leyka-star-history">
								<h3 class="list-title">История пожертвований</h3>
								<div class="items">
									<div class="item break">
										<h4 class="item-title">
											<span class="field-q"><span class="field-q-tooltip">Описание операции, представленной значком</span></span>
											Отключение
										</h4>
										<span class="date">12.01.2019</span>
										<p>«Помогите изданию оставаться независимым источником информации»</p>
									</div>
									<div class="item no-pay">
										<h4 class="item-title">
											<span class="field-q"><span class="field-q-tooltip">Описание операции, представленной значком</span></span>
											300 Р.
										</h4>
										<span class="date">12.01.2019</span>
										<p>«Помогите изданию оставаться независимым источником информации»</p>
									</div>
									<div class="item error">
										<h4 class="item-title">
											<span class="field-q"><span class="field-q-tooltip">Описание операции, представленной значком</span></span>
											300 Р.
										</h4>
										<span class="date">12.01.2019</span>
										<p>«Помогите изданию оставаться независимым источником информации»</p>
									</div>
									<div class="item pay">
										<h4 class="item-title">
											<span class="field-q"><span class="field-q-tooltip">Описание операции, представленной значком</span></span>
											300 Р.
										</h4>
										<span class="date">12.01.2019</span>
										<p>«Помогите изданию оставаться независимым источником информации»</p>
									</div>
									<div class="item break">
										<h4 class="item-title">
											<span class="field-q"><span class="field-q-tooltip">Описание операции, представленной значком</span></span>
											Отключение
										</h4>
										<span class="date">12.01.2019</span>
										<p>«Помогите изданию оставаться независимым источником информации»</p>
									</div>
									<div class="item refund">
										<h4 class="item-title">
											<span class="field-q"><span class="field-q-tooltip">Описание операции, представленной значком</span></span>
											Отключение
										</h4>
										<span class="date">12.01.2019</span>
										<p>«Помогите изданию оставаться независимым источником информации»</p>
									</div>
								</div>
								
								<div class="leyka-star-submit">
									<a href="#" class="leyka-star-single-link internal">Загрузить еще</a>
								</div>
							</div>
						
							<p class="leyka-we-need-you">Вы всегда можете <a href="?leyka-screen=cancel-subscription">отключить ваше ежемесячное пожертвование.</a><br />Но нам будет без вас трудно.</p>
							
						</form>
						
					</div>
				</div>
			
			</div>
		</main><!-- #main -->
	</section><!-- #primary -->


</div><!-- #content -->

<?php get_footer(); ?>