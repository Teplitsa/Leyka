<?php
/**
 * The template for displaying leyka persistent campaign
 *
 * @link https://leyka.te-st.ru/campaign/demo-kampaniya/
 *
 * @package Leyka
 * @since 1.0.0
 */

$cover_url = null;
$cover_att_id = get_post_meta(get_the_ID() , 'campaign_cover', true);
if($cover_att_id) {
    $cover_url = wp_get_attachment_url( $cover_att_id );
}

$logo_url = null;
$logo_att_id = get_post_meta(get_the_ID() , 'campaign_logo', true);
if($logo_att_id) {
    $logo_url = wp_get_attachment_url( $logo_att_id );
}

$custom_css = get_post_meta(get_the_ID() , 'campaign_css', true);
?>

<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
	
	<?php if($custom_css):?>
		<style type="text/css">
			<?php echo $custom_css;?>
		</style>
	<?php endif;?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site leyka-persistant-campaign">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'twentynineteen' ); ?></a>

		<header id="masthead" class="leyka-campaign-header" style="<?php if($cover_url):?>background-image:url('<?php echo $cover_url;?>');<?php endif;?>">
            <a href="#" class="leyka-campaign-logo" style="<?php if($logo_url):?>background-image:url('<?php echo $logo_url;?>');<?php endif;?>"></a>
            <h1><?php echo get_the_title();?></h1>
        </header><!-- #masthead -->

	<div id="content" class="site-content leyka-campaign-content">
        
	<section id="primary" class="content-area">
		<main id="main" class="site-main">

			<?php

			/* Start the Loop */
			while ( have_posts() ) :
				the_post();
            ?>
            
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            
                <div class="entry-content">
                    <?php
                    the_content(
                        sprintf(
                            wp_kses(
                                /* translators: %s: Name of current post. Only visible to screen readers */
                                __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'twentynineteen' ),
                                array(
                                    'span' => array(
                                        'class' => array(),
                                    ),
                                )
                            ),
                            get_the_title()
                        )
                    );
                    ?>
                </div><!-- .entry-content -->
            
            </article><!-- #post-${ID} -->
            
            <?php
			endwhile; // End of the loop.
			?>

		</main><!-- #main -->
	</section><!-- #primary -->

	</div><!-- #content -->

	<footer class="site-footer leyka-campaign-footer">
		<div class="site-info">
            <div class="leyka-footer-links">
                <a href="#">О проекте</a>
                <a href="#">Реклама</a>
            </div>
            <div class="leyka-footer-info">
                <p>Свидетельство о регистрации СМИ ЭЛ № ФС77-64494 от 31.12.2015 года.</p>
                <p>Выдано Федеральной службой по надзору в сфере связи, информационных технологий и массовых коммуникаций.</p>
                <p>Учредитель ЗАО "Проектное финансирование"</p>
                <p><b>18+</b></p>
            </div>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
