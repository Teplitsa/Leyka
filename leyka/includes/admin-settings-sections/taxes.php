<?php
/**
 * @package Leyka
 * @subpackage Settings -> Taxes tab modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

// Changes in Settings->Taxes admin section: taxes tab is temp. removed
function leyka_taxes_settings($settings){
    return array();
}
add_filter('edd_settings_taxes', 'leyka_taxes_settings');