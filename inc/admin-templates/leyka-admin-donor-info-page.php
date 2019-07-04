<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

if(empty($_GET['donor']) || !current_user_can('leyka_manage_options')) {
    wp_die(__("Error: cannot display a page for a given donor.", 'leyka'));
}

try {
    $donor = new Leyka_Donor(absint($_GET['donor']));
} catch(Exception $e) {
    wp_die($e->getMessage());
}?>

<div class="wrap" data-leyka-admin-page-type="donor-info-page">

    <a href=""><?php _e('Back to the list', 'leyka');?></a>
    <br class="clear">

    <h1 class="wp-heading-inline"><?php _e('Donors', 'leyka');?></h1>
    <hr class="wp-header-end">

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-2" class="postbox-container">
                <?php do_meta_boxes('dashboard_page_leyka_donor_info', 'normal', null);?>
            </div>
        </div>
    </div>

</div>
<br class="clear">