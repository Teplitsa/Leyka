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
if(is_singular(Leyka_Campaign_Management::$post_type)) {
    $campaign_id = get_the_ID();
} else if(is_page(leyka()->opt('success_page')) || is_page(leyka()->opt('failure_page'))) {
    $campaign_id = leyka_campaign_id_from_query_arg();
}
 
$cover_url = null;
$cover_att_id = get_post_meta($campaign_id, 'campaign_cover', true);
if($cover_att_id) {
    $cover_url = wp_get_attachment_url($cover_att_id);
}

$logo_url = null;
$logo_att_id = get_post_meta($campaign_id, 'campaign_logo', true);
if($logo_att_id) {
    $logo_url = wp_get_attachment_url($logo_att_id);
}

$custom_css = get_post_meta($campaign_id, 'campaign_css', true);?>

<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head();

	if($custom_css) {?>
    <style type="text/css">
        <?php echo $custom_css;?>
    </style>
	<?php }?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site leyka-persistant-campaign">
	<a class="skip-link screen-reader-text" href="#content"><?php _e('Skip to content', 'leyka');?></a>

		<header id="masthead" class="leyka-campaign-header" style="<?php if($cover_url):?>background-image:url('<?php echo $cover_url;?>');<?php endif;?>">
            <div class="header-tint">
                <a href="#" class="leyka-campaign-logo" style="<?php if($logo_url):?>background-image:url('<?php echo $logo_url;?>');<?php endif;?>"></a>
                <h1><?php echo get_the_title();?></h1>
            </div>
        </header>

	<div id="content" class="site-content leyka-campaign-content">

        <section id="primary" class="content-area">
            <main id="main" class="site-main">

            <?php while ( have_posts() ) {

                the_post();?>

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <div class="entry-content">
                        <?php the_content(
                            sprintf(
                                wp_kses(
                                    _x('Continue reading<span class="screen-reader-text"> "%s"</span>', '%s is a current post title. Only visible to screen readers', 'leyka'),
                                    array('span' => array('class' => array(),),)
                                ),
                                get_the_title()
                            )
                        );?>
                    </div><!-- .entry-content -->

                </article><!-- #post-${ID} -->

                <?php }?>

            </main><!-- #main -->
        </section><!-- #primary -->

	</div><!-- #content -->

<?php get_footer(); ?>