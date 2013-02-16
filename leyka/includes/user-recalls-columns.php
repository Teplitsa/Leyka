<?php
/**
 * @package Leyka
 * @subpackage Recalls columns
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** User recalls table columns. Defines the custom columns and their order. */
function leyka_edit_recall_columns($recall_columns){
    $recall_columns = array(
        'cb' => '<input type="checkbox"/>',
        'text' => __('Recall text', 'leyka'),
        'date' => __('Date', 'leyka')
    );
    return $recall_columns;
}
add_filter('manage_edit-recall_columns', 'leyka_edit_recall_columns');