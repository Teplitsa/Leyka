<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

if(empty($_GET['extension']) || !current_user_can('leyka_manage_options')) {
    wp_die(__("Error: cannot display a page for a given extension.", 'leyka'));
}

try {
    $extension = Leyka_Extension::get_by_id($_GET['extension']);
} catch(Exception $e) {
    wp_die($e->getMessage());
}?>

<div class="wrap" data-leyka-admin-page-type="extension-settings-page">

    <a href="<?php echo admin_url('/admin.php?page=leyka_extensions');?>" class="back-to-list-link"><?php _e('Back to the list', 'leyka');?></a>

    <h1 class="wp-heading-inline"><?php _e('Extensions', 'leyka');?></h1>
    <hr class="wp-header-end">

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-2" class="postbox-container">
            	<input type="hidden" value="<?php echo $extension->id?>" id="leyka_extension_id">
                <?php do_meta_boxes('dashboard_page_leyka_extension_settings', 'normal', null);?>
            </div>
        </div>
    </div>

</div>
<br class="clear">