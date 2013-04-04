<?php
/**
 * @package Leyka
 * @subpackage Admin recalls list page
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

/**
 * User recalls list page.
 * It uses Leyka_Recalls_Table class instead of native EDD class to render page content.
 */
function leyka_recalls_page(){
    include_once(LEYKA_PLUGIN_DIR.'/includes/classes/recalls-table.php');
    $recalls_table = new Leyka_Recalls_Table();
    $recalls_table->prepare_items();?>

<div class="wrap">
    <h2><?php _e('Donor recalls', 'leyka');?></h2>
    <?php do_action('leyka_recalls_page_top');?>
    <form id="leyka-recalls-filter" method="get" action="<?php echo admin_url('edit.php?post_type=download&page=leyka-recalls');?>">
        <?php $recalls_table->search_box(__('Search', 'leyka'), 'leyka-recalls');?>

        <input type="hidden" name="post_type" value="download" />
        <input type="hidden" name="page" value="leyka-recalls" />
        <?php
        $recalls_table->views();
        $recalls_table->display();
        ?>
    </form>
    <?php do_action('leyka_recalls_page_bottom');?>
</div>
<?php
}