<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

try {
    $extension = Leyka_Extension::get_by_id($_GET['extension']);
} catch(Exception $e) {
    wp_die($e->getMessage());
}?>

<div class="leyka-admin wrap single-settings extension-settings" data-leyka-admin-page-type="extension-settings-page">

    <a href="<?php echo admin_url('/admin.php?page=leyka_extensions');?>" class="back-to-list-link">
        <?php _e('Back to the list', 'leyka');?>
    </a>

    <h1 class="wp-heading-inline"><?php echo $extension->title;?></h1>
    <hr class="wp-header-end">

    <div class="single-settings-header">

        <div class="header-left">

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

            <div class="module-logo extension-logo">
                <img src="<?php echo $extension->logo_url;?>" class="module-logo-pic extension-logo-pic" alt="">
            </div>

            <div class="extension-main-cta"><button class="button"><?php _e('Activate', 'leyka');?></button></div>

        </div>

    </div>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">

            <div id="postbox-container-1" class="postbox-container extension-sidebar">

            <?php if($extension->setup_description) {?>
                <div class="setup-secription"><?php echo $extension->setup_description;?></div>
            <?php }

            if($extension->docs_url) {?>
                <div class="setup-user-manual-link outer-link">
                    <a href="<?php echo $extension->docs_url;?>" target="_blank"><?php _e('Detailed manual', 'leyka');?></a>
                </div>
            <?php }?>

            </div>

            <div id="postbox-container-2" class="postbox-container">

            	<input type="hidden" value="<?php echo $extension->id?>" id="leyka_extension_id">

                <?php do_meta_boxes('extension_settings_page_main_column', 'normal', null);?>

            </div>

        </div>
    </div>

</div>
<br class="clear">