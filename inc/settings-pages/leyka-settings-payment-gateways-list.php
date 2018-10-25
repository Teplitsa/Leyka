<?php if( !defined('WPINC') ) die; // If this file is called directly, abort

$gateways = leyka()->get_gateways();
$gateways_categories = leyka_get_gateways_filter_categories_list();

?>

<div class="main-area-top">
    
    <div class="filter-area leyka-gateways-filter">
        
        <div class="filter-toggle">
            <img class="show-filter" src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-gateway-filter-off.svg" />
            <img class="hide-filter" src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-gateway-filter-on.svg" />
        </div>

        <div class="filter-categories">
            <?php foreach($gateways_categories as $category_slug => $category_label) { ?>
                <a class="filter-category-item" data-category="<?php echo $category_slug;?>" href="#"><?php echo leyka_get_filter_category_label($category_slug);?></a>
            <?php }?>
        </div>
        
        <a class="filter-action filter-category-show-filter" href="#">Отфильтровать</a>
        <a class="filter-action filter-category-reset-filter" href="#">Очистить фильтр</a>
        
    </div>
    
</div>

<div class="main-area all-gateways-settings">
    
    <div class="gateways-cards-list">
        
    <?php foreach($gateways as $i => $gateway) {
            $gateway_activation_status = $gateway->get_activation_status();
        ?>
    
        <div class="leyka-admin-gateway-card gateway-card <?php echo implode(" ", $gateway->get_filter_categories());?>">
            
            <div class="gateway-card-header">
                
                <div class="gateway-card-icon">
                    <?php leyka_show_gateway_logo($gateway, true);?>
                </div>
                
                <div>
                    <div class="gateway-card-title"><?php echo $gateway->title;?></div>
                    <div class="gateway-card-status <?php echo $gateway_activation_status;?>"><?php echo leyka_get_gateway_activation_status_label($gateway_activation_status);?></div>
                </div>
                
            </div>
            
            <div class="gateway-card-params">
                <?php leyka_gateway_details_html($gateway);?>                
            </div>

            <div class="gateway-card-supported-pm-list">
                
                <div class="pm-icons-scroll">
                    <div class="pm-icons-wrapper">
                        <div class="pm-icons">
                        <?php
                            $icons = leyka_get_gateway_icons_list($gateway);
                            foreach($icons as $icon_url) {?>
                                <img class="pm-icon" src="<?php echo $icon_url;?>">
                            <?php }
                        ?>
                        </div>
                    </div>
                    <img class="scroll-arrow left" src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-scroll-arrow-left.svg" />
                    <img class="scroll-arrow right" src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-scroll-arrow-right.svg" />
                </div>
            
            </div>
            
            <div class="gateway-card-action">
                <a class="button <?php echo $gateway_activation_status == 'active' ? 'button-secondary' : 'button-primary';?> activation-button <?php echo $gateway_activation_status;?> <?php echo leyka_gateway_setup_wizard($gateway) ? "wizard-available" : "";?>" href="<?php echo leyka_get_gateway_settings_url($gateway);?>"><?php echo leyka_get_gateway_activation_button_label($gateway);?></a>
            </div>
            
        </div>
        
    <?php }?>
    
    </div>
    
</div>