<?php if( !defined('WPINC') ) die;
/** Admin Recurring Subscription Info page template */

/** @var $this Leyka_Admin_Setup */

if( !current_user_can('leyka_manage_donations') ) {
    wp_die(__('Error: cannot display the page for the given donation.', 'leyka'));
}

try {

    $donation = Leyka_Donations::get_instance()->get_donation($_GET['donation']);
    $donation_id = $donation->id;

} catch(Exception $e) {
    wp_die($e->getMessage());
}?>

<div class="wrap" data-leyka-admin-page-type="recurring-subscription-info"> <!-- leyka-admin wrap single-settings donation-info -->

    <a href="<?php echo admin_url('/admin.php?page=leyka_recurring_subscriptions');?>" class="back-to-list-link">
        <?php _e('Back to the list', 'leyka');?>
    </a>
    <br class="clear">

    <div class="wp-heading-inline">
        <h1><?php _e('Subscription profile', 'leyka');?></h1>
        <div class="leyka-subscription-status leyka-subscription-<?php echo $donation->recurring_subscription_status; ?>">
            <?php _ex(mb_ucfirst($donation->recurring_subscription_status), 'Recurring subscription status, singular (like [subscription is] "Active/Non-active/Problematic")', 'leyka'); /** @todo Fix this ambiguous l10n string formulation! */?>
        </div>
    </div>

    <hr class="wp-header-end">

    <?php if( !empty($_SESSION['leyka_new_donation_error']) && is_wp_error($_SESSION['leyka_new_donation_error']) ) {

        /** @var $error WP_Error */
        $error = $_SESSION['leyka_new_donation_error'];
        unset($_SESSION['leyka_new_donation_error']);?>

        <div class="error"><?php echo $error->get_error_message();?></div>

    <?php } else if(isset($_GET['msg']) && $_GET['msg'] === 'ok') {?>
        <div id="message" class="updated notice notice-success"><p><?php _e('Donation added.', 'leyka');?></p></div>
    <?php }?>

    <form name="post" action="<?php echo admin_url('admin.php?page=leyka_donation_info&donation='.$donation_id);?>" method="post" id="post">

        <?php wp_nonce_field('edit-donation');?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">

                <?php $metaboxes_area_id = 'dashboard_page_leyka_donation_info';?>
                <input type="hidden" class="leyka-support-metabox-area" value="<?php echo $metaboxes_area_id;?>">

                <div id="postbox-container-1" class="postbox-container">
                    <?php do_meta_boxes($metaboxes_area_id, 'side', null);?>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <?php do_meta_boxes($metaboxes_area_id, 'normal', null);?>
                </div>

            </div>
        </div>

    </form>

</div>
<br class="clear">