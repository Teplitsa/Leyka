<?php if( !defined('WPINC') ) die;
/** Admin Donors list page template */

/** @var $this Leyka_Admin_Setup */

$addons = leyka()->get_addons();
$addons_categories = Leyka_Addon::get_filter_categories_list();

echo '<pre>'.print_r($addons, 1).'</pre>';
echo '<pre>'.print_r($addons_categories, 1).'</pre>';?>

<div class="wrap" data-leyka-admin-page-type="addons-list-page">

    <h1 class="wp-heading-inline"><?php _e('Addons', 'leyka');?></h1>

    <div class="main-area-top">

        <div class="filter-area leyka-gateways-filter show">

            <div class="filter-toggle">
                <img class="show-filter" src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-gateway-filter-off.svg" alt="">
                <img class="hide-filter" src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-gateway-filter-on.svg" alt="">
            </div>

            <div class="filter-categories">
                <?php foreach($addons_categories as $category_id => $category_label) {?>
                    <a class="filter-category-item" data-category="<?php echo $category_id;?>" href="#">
                        <?php echo Leyka_Addon::get_filter_category_label($category_id);?>
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

    <div class="main-area all-addons-settings">

        <div class="addons-cards-list">

            <?php foreach($addons as $addon_id => $addon) { /** @var $gateway Leyka_Gateway */

                $activation_status = $addon->get_activation_status(); // $gateway->get_activation_status()?>

                <div class="leyka-admin-addon-card admin-card <?php echo implode(' ', $addon->get_filter_categories());?> <?php echo $activation_status;?>">

                    <div class="card-header">

                        <div class="card-icon"><?php leyka_show_addon_logo($addon, true); // leyka_show_gateway_logo()?></div>

                        <div>
                            <div class="card-title">
                                <a class="settings-link" href="<?php echo admin_url('admin.php?page=leyka_addons&addon='.$addon->id);?>">
                                    <?php echo $addon->title;?>
                                </a>
                            </div>
                            <div class="card-status <?php echo $activation_status;?>">
                                <?php echo leyka_get_addon_activation_status_label($activation_status); // leyka_get_gateway_activation_status_label()?>
                            </div>
                        </div>

                    </div>

                    <div class="card-params">
                        <?php leyka_addon_details_html($gateway); // leyka_gateway_details_html()?>
                    </div>

                    <div class="card-action">
                        <a class="button <?php echo $activation_status == 'active' ? 'button-secondary' : 'button-primary';?> activation-button <?php echo $activation_status;?>" href="<?php echo leyka_get_addon_settings_url($addon); // leyka_get_gateway_settings_url()?>">
                            <?php echo leyka_get_addon_activation_button_label($addon); // leyka_get_gateway_activation_button_label()?>
                        </a>
                    </div>

                </div>

            <?php }?>

        </div>

    </div>

</div>