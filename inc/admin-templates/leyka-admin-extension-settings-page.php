<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

try {
    $extension = Leyka_Extension::get_by_id($_GET['extension']);
} catch(Exception $e) {
    wp_die($e->getMessage());
}

if( !$extension) {
    wp_die(sprintf(__('The extension is not found: %s', 'leyka'), $_GET['extension']));
}?>

<div class="leyka-admin wrap single-settings extension-settings" data-leyka-admin-page-type="extension-settings-page">

    <a href="<?php echo admin_url('/admin.php?page=leyka_extensions');?>" class="back-to-list-link">
        <?php _e('Back to the list', 'leyka');?>
    </a>
<!--    <hr class="wp-header-end">-->
    <div class="single-settings-header">

        <div class="header-left">

            <h1 class="wp-heading-inline"><?php echo $extension->title;?></h1>

            <div class="meta-data">

                <div class="item activation-status">
                    <span class="item-name"><?php _e('Status:', 'leyka');?></span>
                    <span class="item-value status-label <?php echo $extension->activation_status;?>">
                        <?php echo mb_strtolower($extension->activation_status_label);?>
                    </span>
                </div>
                <div class="item extension-version">
                    <span class="item-name"><?php _e('Extension version:', 'leyka');?></span>
                    <span class="item-value"><?php echo $extension->version;?></span>
                </div>
                <div class="item leyka-version">
                    <span class="item-name"><?php _e('Leyka version:', 'leyka');?></span>
                    <span class="item-value"><?php echo LEYKA_VERSION;?></span>
                </div>
                <div class="item author">

                    <span class="item-name"><?php _e('Author:', 'leyka');?></span>
                    <span class="item-value">
                        <?php if($extension->author_url) {?>
                            <a href="<?php echo $extension->author_url;?>" target="_blank" class="outer-link">
                            <?php echo $extension->author_name;?>
                        </a>
                        <?php } else {
                            echo __('Author:', 'leyka').' '.$extension->author_name;
                        }?>
                    </span>

                </div>

            </div>

            <div class="extension-description"><?php echo $extension->settings_description;?></div>

        </div>

        <div class="header-right">

            <div class="module-logo-wrapper">
                <div class="module-logo extension-logo">
                    <img src="<?php echo $extension->logo_url;?>" class="module-logo-pic extension-logo-pic" alt="">
                </div>
            </div>

            <div class="extension-main-cta">
                <a class="button <?php echo $extension->activation_status === 'active' ? 'button-secondary' : 'button-primary';?> activation-button <?php echo $extension->activation_status;?> <?php echo $extension->has_wizard ? 'wizard-available' : '';?>" href="#"><?php echo leyka_get_extension_activation_button_label($extension);?></a>
            </div>

        </div>

    </div>

    <div id="poststuff">
        <div class="metabox-holder columns-2">

            <div class="postbox-container column-main">

                <input type="hidden" value="<?php echo $extension->id;?>" id="leyka_extension_id">

                <?php try {

                    Leyka_Settings_Factory::get_instance()
                        ->get_render('options')
                        ->set_controller(
                            Leyka_Settings_Factory::get_instance()
                                ->get_controller('options')
                                ->set_options_data($extension->get_options_data())
                        )
                        ->render_content();

                } catch(Exception $ex) {
                    echo '<pre>'.sprintf(__('Settings display error (code %s): %s', 'leyka'), $ex->getCode(), $ex->getMessage()).'</pre>';
                }?>

            </div>

            <div class="postbox-container column-sidebar">

            <?php if($extension->setup_description) {?>
                <div class="setup-description"><?php echo $extension->setup_description;?></div>
            <?php }

            if($extension->docs_url) {?>
                <div class="setup-user-manual-link">
                    <a class="outer-link" href="<?php echo $extension->docs_url;?>" target="_blank">
                        <?php _e('Detailed manual', 'leyka');?>
                    </a>
                </div>
            <?php }?>

            </div>

        </div>
    </div>

    <div class="single-settings-footer">

        <a href="#" class="delete-extension-link"><?php _e('Delete the extension', 'leyka');?></a>

        <span class="buttons">
            <a class="button button-primary button-small save-settings" href="#"><?php _e('Save', 'leyka');?></a>
            <a class="button <?php echo $extension->activation_status === 'active' ? 'button-secondary' : 'button-primary';?> activation-button <?php echo $extension->activation_status;?>" href="#"><?php echo leyka_get_extension_activation_button_label($extension);?></a>
        </span>

    </div>

</div>
<br class="clear">