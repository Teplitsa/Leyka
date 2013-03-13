<?php
/**
 * @package Leyka
 * @subpackage Admin donations history page modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

/**
 * Extended "payment history" page - now it's donations history.
 * It uses Leyka_Donations_History_Table class instead of native EDD class to render page content.
 */
function leyka_donations_history_page(){
    global $edd_options;

    if(isset($_GET['edd-action']) && $_GET['edd-action'] == 'edit-payment') {
        include_once(LEYKA_PLUGIN_DIR.'/includes/admin-edit-payment-form.php');
    } else {
        include_once(LEYKA_PLUGIN_DIR.'/includes/classes/payments-table.php');
        $donations_table = new Leyka_Donations_History_Table();
        $donations_table->prepare_items();?>
    <div class="wrap">
        <h2><?php _e('Donations history', 'leyka');?></h2>
        <?php do_action('edd_payments_page_top');?>
        <form id="edd-payments-filter" method="get" action="<?php echo admin_url('edit.php?post_type=download&page=edd-payment-history');?>">
            <?php $donations_table->search_box(__('Search', 'leyka'), 'edd-payments');?>

            <input type="hidden" name="post_type" value="download" />
            <input type="hidden" name="page" value="edd-payment-history" />
            <?php
            $donations_table->views();
            $donations_table->display();
            ?>
        </form>
        <?php do_action('edd_payments_page_bottom');?>
    </div>
    <?php
    }
}