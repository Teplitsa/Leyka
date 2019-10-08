<?php if( !defined('WPINC') ) die;
/** Admin Donation info page template */

/** @var $this Leyka_Admin_Setup */

if(empty($_GET['donation']) || !current_user_can('leyka_manage_options')) {
    wp_die(__("Error: cannot display a page for a given donor.", 'leyka'));
}

try {
    $donation = Leyka_Donations::get_instance()->get_donation($_GET['donation']);
} catch(Exception $e) {
    wp_die($e->getMessage());
}?>

<div class="wrap" data-leyka-admin-page-type="donation-info-page">

    <a href="<?php echo admin_url('/admin.php?page=leyka_donations');?>" class="back-to-list-link"><?php _e('Back to the list', 'leyka');?></a>

    <h1 class="wp-heading-inline"><?php _e('Donation profile', 'leyka');?></h1>
    <hr class="wp-header-end">

    <form name="post" action="<?php echo admin_url('?page=leyka_donation_info&donation='.$donation->id.'&action=edit');?>" method="post" id="post">

        <?php wp_nonce_field('edit-donation');?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <?php do_meta_boxes('dashboard_page_leyka_donation_info', 'side', null);?>
                </div>
                <div id="postbox-container-2" class="postbox-container">
                    <input type="hidden" value="<?php echo $donation->id?>" id="leyka_donation_id">
                    <?php do_meta_boxes('dashboard_page_leyka_donation_info', 'normal', null);?>
                </div>
            </div>
        </div>

    </form>

</div>
<br class="clear">