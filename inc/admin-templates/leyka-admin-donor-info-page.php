<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

if(empty($_GET['donor']) || !current_user_can('leyka_manage_options')) {

    wp_redirect(admin_url('admin.php?page=leyka'));
    exit;

}

/** @todo Create a Leyka_Donor class to incapsulate all donor data interactions */

$donor_user = get_user_by('id', $_GET['donor']);
if( !$donor_user) {

    wp_redirect(admin_url('admin.php?page=leyka'));
    exit;

}?>

<div class="wrap">

    <a href=""><?php _e('Back to the list', 'leyka');?></a>
    <br class="clear">

    <h1 class="wp-heading-inline"><?php _e('Donors', 'leyka');?></h1>
    <hr class="wp-header-end">

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-2" class="postbox-container">

            </div>
        </div>
    </div>

</div>