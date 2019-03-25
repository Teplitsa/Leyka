<?php
/**
 * The template for displaying leyka account screens
 *
 * @link https://leyka.te-st.ru/campaign/demo-kampaniya/
 *
 * @package Leyka
 * @since 1.0.0
 *
 * $leyka_account_page_title
 * 
 */

$leyka_account_cover_url = '';
$leyka_account_logo_url = '';

?>

<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site leyka-persistant-campaign">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'twentynineteen' ); ?></a>

    <header id="masthead" class="leyka-campaign-header" style="<?php if(!empty($leyka_account_cover_url)):?>background-image:url('<?php echo $leyka_account_cover_url;?>');<?php endif;?>">
        <div class="header-tint">
            <a href="#" class="leyka-campaign-logo" style="<?php if(!empty($leyka_account_logo_url)):?>background-image:url('<?php echo $leyka_account_logo_url;?>');<?php endif;?>"></a>
            <h1><?php echo !empty($leyka_account_page_title) ? $leyka_account_page_title : esc_html__('Leyka account', 'leyka');?></h1>
        </div>
    </header><!-- #masthead -->
