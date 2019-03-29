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
							
							<h2>Личный кабинет</h2>
							
							<p>Мы благодарны вам за оказываемую поддержку!</p>
							
							<div class="list subscribed-campaigns-list">
								<h3 class="list-title">Кампании с ежемесячными пожертвованиями</h3>
								<div class="items">
									<div class="item">
										<span class="campaign-title">Помогите изданию оставаться независимым источником информации</span>
										<span class="amount">300 Р./мес.</span>
									</div>
									<div class="item">
										<span class="campaign-title">На погашение штрафа от Роскомнадзора</span>
										<span class="amount">1800 Р./мес.</span>
									</div>
									<div class="item">
										<span class="campaign-title">Поможем Григорию переехать </span>
										<span class="amount">210 Р./мес.</span>
									</div>
								</div>
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