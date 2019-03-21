<?php
/**
 * The template for displaying leyka persistent campaign
 *
 * @link https://leyka.te-st.ru/campaign/demo-kampaniya/
 *
 * @package Leyka
 * @since 1.0.0
 */

$campaign_id = null;
if( is_singular(Leyka_Campaign_Management::$post_type) ) {
    $campaign_id = get_the_ID();
}
elseif(is_page(leyka_options()->opt('success_page')) || is_page(leyka_options()->opt('failure_page'))) {
    $campaign_id = leyka_campaign_id_from_query_arg();
}
 
$cover_url = null;
$cover_att_id = get_post_meta($campaign_id, 'campaign_cover', true);
if($cover_att_id) {
    $cover_url = wp_get_attachment_url( $cover_att_id );
}

$logo_url = null;
$logo_att_id = get_post_meta($campaign_id, 'campaign_logo', true);
if($logo_att_id) {
    $logo_url = wp_get_attachment_url( $logo_att_id );
}

include(LEYKA_PLUGIN_DIR . 'templates/cabinet/header.php'); ?>

	<div id="content" class="site-content leyka-campaign-content">
        
	<section id="primary" class="content-area">
		<main id="main" class="site-main">
            <h1>my donations</h1>

<div id="leyka-pf-<?php echo $campaign_id;?>" class="leyka-pf leyka-pf-star" data-form-id="leyka-pf-<?php echo $campaign_id;?>-star-form">
<div class="leyka-payment-form leyka-tpl-star-form" data-template="star">

            <form class="leyka-screen-form">
                
                <h2>История пожертвований</h2>
                
                <p>Мы благодарны вам за оказываемую поддержку!</p>
                
                <div class="leyka-star-history">
                    <div class="item break">
                        <h2>Отключение</h2>
                        <span class="date">12.01.2019</span>
                        <p>«Помогите изданию оставаться независимым источником информации»</p>
                    </div>
                    <div class="item no-pay">
                        <h2>300 Р.</h2>
                        <span class="date">12.01.2019</span>
                        <p>«Помогите изданию оставаться независимым источником информации»</p>
                    </div>
                    <div class="item error">
                        <h2>300 Р.</h2>
                        <span class="date">12.01.2019</span>
                        <p>«Помогите изданию оставаться независимым источником информации»</p>
                    </div>
                    <div class="item pay">
                        <h2>300 Р.</h2>
                        <span class="date">12.01.2019</span>
                        <p>«Помогите изданию оставаться независимым источником информации»</p>
                    </div>
                    <div class="item break">
                        <h2>Отключение</h2>
                        <span class="date">12.01.2019</span>
                        <p>«Помогите изданию оставаться независимым источником информации»</p>
                    </div>
                </div>
            
                <div class="leyka-star-submit">
                    <a href="#" class="leyka-star-btn">Загрузить еще</a>
                </div>
                
                <p class="leyka-we-need-you">Вы всегда можете <a href="?leyka-screen=cancel-subscription">отключить ваше ежемесячное пожертвование.</a><br />Но нам будет без вас трудно.</p>
                
            </form>
</div>
</div>
            
		</main><!-- #main -->
	</section><!-- #primary -->

	</div><!-- #content -->

<?php get_footer(); ?>