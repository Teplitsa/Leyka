<?php if( !defined('WPINC') ) die;?>

<div class="main-area-top">

    <div class="filter-area leyka-modules-filter show">

        <div class="filter-toggle">
            <img class="show-filter" src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-gateway-filter-off.svg" alt="">
            <img class="hide-filter" src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-gateway-filter-on.svg" alt="">
        </div>

        <div class="filter-categories">
        <?php foreach(leyka_get_gateways_filter_categories_list() as $category_slug => $category_label) {?>
            <a class="filter-category-item" data-category="<?php echo $category_slug;?>" href="#">
                <?php echo leyka_get_filter_category_label($category_slug);?>
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

<div class="main-area all-extensions-settings all-gateways-settings">
    
    <div class="modules-cards-list">
        
    <?php foreach(leyka()->get_gateways(['orderby' => 'activation_status',]) as $gateway) { /** @var $gateway Leyka_Gateway */

        $gateway_activation_status = $gateway->get_activation_status();?>

        <div class="module-card gateway-card <?php echo implode(' ', $gateway->get_filter_categories());?> <?php echo $gateway_activation_status;?>">

            <div class="module-card-header">

                <div class="module-card-icon"><?php leyka_show_gateway_logo($gateway, true);?></div>

                <div>
                    <div class="module-card-title gateway-card-title">
                        <a class="module-settings-link" href="<?php echo admin_url('admin.php?page=leyka_settings&stage=payment&gateway='.$gateway->id);?>">
                            <?php echo $gateway->title;?>
                        </a>
                    </div>
                    <div class="module-card-status gateway-card-status <?php echo $gateway_activation_status;?>">
                        <?php echo leyka_get_gateway_activation_status_label($gateway_activation_status);?>
                    </div>
                </div>

            </div>
            
            <div class="module-card-params">
                <?php leyka_gateway_details_html($gateway);?>                
            </div>

            <div class="gateway-card-supported-pm-list">

                <div class="pm-icons-scroll">
                    <div class="pm-icons-wrapper">
                        <div class="pm-icons">

                        <?php $icons = leyka_get_gateway_icons_list($gateway);
                        foreach($icons as $icon_url) {?>
                            <img class="pm-icon <?php echo $gateway->id.' '.basename($icon_url, '.svg');?>" src="<?php echo $icon_url;?>" alt="">
                        <?php }?>

                        </div>
                    </div>
                </div>

                <img class="scroll-arrow left" src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-scroll-arrow-left.svg" alt="">
                <img class="scroll-arrow right" src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-scroll-arrow-right.svg" alt="">

            </div>

            <div class="module-card-action">

                <a class="button <?php echo'button-primary';?> activation-button <?php echo 'leyka-card-'.$gateway_activation_status;?>" href="<?php echo leyka_get_gateway_settings_url($gateway);?>">

                <?php if($gateway->has_wizard && in_array($gateway_activation_status, ['inactive', 'activating'])) {?>
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-wizard-stick-only.svg" class="wizard-available" alt="">
                <?php } else {?>
                	<img src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/icon-gear.svg';?>" alt="">
                <?php }

                echo leyka_get_gateway_activation_button_label($gateway);?>

                </a>

            </div>

        </div>
        
    <?php }?>
    
    </div>
    
</div>