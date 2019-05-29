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
$leyka_account_logo_url = '';?>

<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
	<symbol width="12" height="9" viewBox="0 0 12 9" id="icon-checkbox-check">
		<path d="M3.81353 7.10067L0.968732 4.30201L0 5.24832L3.81353 9L12 0.946309L11.0381 0L3.81353 7.10067Z"></path>
	</symbol>
</svg>

<div id="page" class="site leyka-persistant-campaign leyka-account">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'twentynineteen' ); ?></a>

    <header id="masthead" class="leyka-campaign-header cover-type-image">
        <div class="header-tint">
            <div class="leyka-campaign-no-logo"></div>
            <h1><?php echo !empty($leyka_account_page_title) ? $leyka_account_page_title : __('Leyka account', 'leyka');?></h1>
            <a href="<?php echo wp_logout_url( home_url() );?>" class="leyka-logout" title="<?php _e('Logout', 'leyka');?>">
                <span><?php esc_html_e('Logout', 'leyka');?></span>
            </a>
        </div>
    </header>