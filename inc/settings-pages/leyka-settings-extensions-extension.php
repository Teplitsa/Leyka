<?php if( !defined('WPINC') ) die;
/** Admin Extension settings page template */

/** @var $this Leyka_Admin_Setup */

try {
    $extension = Leyka_Extension::get_by_id($_GET['extension']);
} catch(Exception $ex) {
    wp_die(wp_kses_post($ex->getMessage()));
}

if( !$extension ) {
    wp_die(sprintf(esc_html__('Error: the extension "%s" is not found', 'leyka'), esc_html($_GET['extension'])));
}?>

<div class="leyka-admin wrap single-settings extension-settings" data-leyka-admin-page-type="extension-settings-page" data-leyka-extension-id="<?php echo esc_attr( $extension->id );?>">

    <a href="<?php echo esc_url(admin_url('/admin.php?page=leyka_settings&stage=extensions'));?>" class="settings-return-link">
        <?php esc_html_e('Back to the list', 'leyka');?>
    </a>

    <?php try {

        Leyka_Settings_Factory::get_instance()
            ->get_render('extension')
            ->set_controller(
                Leyka_Settings_Factory::get_instance()->get_controller('extension', ['extension' => $extension,])
            )
            ->render_content();

    } catch(Exception $ex) {
        echo '<pre>'.sprintf(esc_html__('Settings display error (code %s): %s', 'leyka'), esc_html($ex->getCode()), esc_html($ex->getMessage())).'</pre>';
    }?>

</div>
<br class="clear">