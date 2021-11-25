<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

if(empty($_GET['donor']) || !current_user_can('leyka_manage_options')) {
    wp_die(__('Error: cannot display the page for the given donor.', 'leyka'));
}

try {
    $donor = new Leyka_Donor(absint($_GET['donor']));
} catch(Exception $e) {
    wp_die($e->getMessage());
}?>

<div class="leyka-admin wrap single-settings donor-settings" data-leyka-admin-page-type="donor-info-page">

    <a href="<?php echo admin_url('/admin.php?page=leyka_donors');?>" class="back-to-list-link">
        <?php _e('Back to the list', 'leyka');?>
    </a>

    <h1 class="wp-heading-inline"><?php _e('Donors', 'leyka');?></h1>
    <hr class="wp-header-end">

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-2" class="postbox-container">

            	<input type="hidden" value="<?php echo $donor->id;?>" id="leyka_donor_id">

                <?php $metaboxes_area_id = 'dashboard_page_leyka_donor_info';?>
                <input type="hidden" class="leyka-support-metabox-area" value="<?php echo $metaboxes_area_id;?>">

                <?php do_meta_boxes($metaboxes_area_id, 'normal', null);?>

            </div>
        </div>
    </div>

</div>
<br class="clear">