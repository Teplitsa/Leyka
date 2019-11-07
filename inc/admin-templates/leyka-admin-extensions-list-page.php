<?php if( !defined('WPINC') ) die;
/** Admin Donors list page template */

/** @var $this Leyka_Admin_Setup */

$extensions = leyka()->get_extensions();
$extensions_categories = Leyka_Extension::get_filter_categories_list();

//echo '<pre>'.print_r($extensions, 1).'</pre>';
//echo '<pre>'.print_r($extensions_categories, 1).'</pre>';?>

<div class="wrap leyka-admin leyka-settings-page" data-leyka-admin-page-type="extensions-list-page">

    <h1 class="wp-heading-inline"><?php _e('Extensions', 'leyka');?></h1>

    <div class="main-area-top">

        <div class="filter-area leyka-modules-filter show"> <!-- class="filter-area leyka-gateways-filter show" -->

            <div class="filter-toggle">
                <img class="show-filter" src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-gateway-filter-off.svg" alt="">
                <img class="hide-filter" src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-gateway-filter-on.svg" alt="">
            </div>

            <div class="filter-categories">
                <?php foreach($extensions_categories as $category_id => $category_label) {?>
                    <a class="filter-category-item" data-category="<?php echo $category_id;?>" href="#">
                        <?php echo Leyka_Extension::get_filter_category_label($category_id);?>
                    </a>
                <?php }?>
            </div>

            <a class="filter-action filter-category-show-filter" href="#">
                <?php esc_html_x('Filter', 'An imperative verb (like "filter [something]")', 'leyka');?>
            </a>
            <a class="filter-action filter-category-reset-filter" href="#">
                <?php esc_html_e('Clear the filter', 'leyka');?>
            </a>

        </div>

    </div>

    <div class="main-area all-modules-settings all-extensions-settings">

        <div class="modules-cards-list">

            <?php foreach($extensions as $extension_id => $extension) { /** @var $extension Leyka_Extension */

                $activation_status = $extension->get_activation_status();?>

                <div class="module-card extension-card <?php echo implode(' ', $extension->get_filter_categories());?>">

                    <div class="module-card-header">

                        <div class="module-card-icon"><?php leyka_show_extension_logo($extension, true);?></div>

                        <div>
                            <div class="module-card-title">
                                <a class="module-settings-link" href="<?php echo admin_url('admin.php?page=leyka_extension_settings&extension='.$extension->id);?>">
                                    <?php echo $extension->title;?>
                                </a>
                            </div>
                            <div class="module-card-status <?php echo $activation_status;?>">
                                <?php echo Leyka_Extension::get_activation_status_label($activation_status);?>
                            </div>
                        </div>

                    </div>

                    <div class="module-card-params extension-description"><?php echo $extension->description;?></div>

                    <div class="module-card-action">
                        <a class="button <?php echo $activation_status === 'active' ? 'button-secondary' : 'button-primary';?> activation-button <?php echo $activation_status;?>" href="<?php echo $extension->get_settings_url();?>">
                            <?php echo leyka_get_extension_activation_button_label($extension);?>
                        </a>
                    </div>

                </div>

            <?php }?>

        </div>

    </div>

</div>