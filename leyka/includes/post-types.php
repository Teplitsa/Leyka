<?php
/**
 * @package Leyka
 * @subpackage Leyka post types modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

// Change some Download post type labels to the Donate labels:
function leyka_donate_labels($labels){
    return array(
        'name' 				 => __('Donates', 'leyka'),
        'singular_name' 	 => __('Donate', 'leyka'),
        'add_new' 			 => __('Add new', 'leyka'),
        'add_new_item' 		 => __('Add new %1$s', 'leyka'),
        'edit_item' 		 => __('Edit %1$s', 'leyka'),
        'new_item' 			 => __('New %1$s', 'leyka'),
        'all_items' 		 => __('All %2$s', 'leyka'),
        'view_item' 		 => __('View %1$s', 'leyka'),
        'search_items' 		 => __('Search %2$s', 'leyka'),
        'not_found' 		 => __('No %2$s found', 'leyka'),
        'not_found_in_trash' => __('No %2$s found in Trash', 'leyka'),
        'parent_item_colon'  => '',
        'menu_name' 		 => __('%2$s', 'leyka')
    );
}
add_filter('edd_download_labels', 'leyka_donate_labels');

// Register donor recall post type:
$recall_labels = array(
    'name' 				=> _x('User recalls', 'post type general name', 'leyka'),
    'singular_name' 	=> _x('Recall', 'post type singular name', 'leyka'),
    'add_new' 			=> __('Add New', 'leyka'),
    'add_new_item' 		=> __('Add New Recall', 'leyka'),
    'edit_item' 		=> __('Edit Recall', 'leyka'),
    'new_item' 			=> __('New Recall', 'leyka'),
    'all_items' 		=> __('All Recalls', 'leyka'),
    'view_item' 		=> __('View Recall', 'leyka'),
    'search_items' 		=> __('Search Recalls', 'leyka'),
    'not_found' 		=> __('No Recalls found', 'leyka'),
    'not_found_in_trash'=> __('No Recalls found in Trash', 'leyka'),
    'parent_item_colon' => '',
    'menu_name' 		=> __('Recall History', 'leyka')
);

$recall_args = array(
    'labels' 			=> apply_filters('leyka_recall_labels', $recall_labels),
    'public' 			=> false,
    'query_var' 		=> false,
    'rewrite' 			=> false,
    'capability_type' 	=> 'post',
    'supports' 			=> array('title', /*'editor', 'author'*/),
    'can_export'		=> false,
    'hierarchical'      => false,
);
register_post_type('leyka_recall', $recall_args);