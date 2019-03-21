<?php
/**
 * The template for displaying leyka persistent campaign
 *
 * @link https://leyka.te-st.ru/campaign/demo-kampaniya/
 *
 * @package Leyka
 * @since 1.0.0
 * 
 */

$campaign_id = null;
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

$custom_css = get_post_meta($campaign_id, 'campaign_css', true);
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
