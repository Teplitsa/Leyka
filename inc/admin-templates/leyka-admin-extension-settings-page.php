<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

//leyka_options()->opt('extensions_active', array('support_packages')); // 4 TEST

try {
    $extension = Leyka_Extension::get_by_id($_GET['extension']);
} catch(Exception $ex) {
    wp_die($ex->getMessage());
}

if( !$extension ) {
    wp_die(sprintf(__('Error: the extension "%s" is not found', 'leyka'), $_GET['extension']));
}?>

<div class="leyka-admin wrap single-settings extension-settings" data-leyka-admin-page-type="extension-settings-page" data-leyka-extension-id="<?php echo $extension->id;?>">

    <a href="<?php echo admin_url('/admin.php?page=leyka_extensions');?>" class="back-to-list-link">
        <?php _e('Back to the list', 'leyka');?>
    </a>

    <?php try {

        Leyka_Settings_Factory::get_instance()
            ->get_render('extension')
            ->set_controller(
                Leyka_Settings_Factory::get_instance()->get_controller('extension', array('extension' => $extension,))
            )
            ->render_content();

    } catch(Exception $ex) {
        echo '<pre>'.sprintf(__('Settings display error (code %s): %s', 'leyka'), $ex->getCode(), $ex->getMessage()).'</pre>';
    }?>

</div>
<br class="clear">