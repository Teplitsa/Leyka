<?php if( !defined('WPINC') ) die; // If this file is called directly, abort

$gateways = leyka()->get_gateways();
    
?>

<div class="main-area-top">
    Gateways list filter here
</div>

<div class="main-area all-gateways-settings">
    
    <div class="gateways-cards-list">
        
    <?php foreach($gateways as $i => $gateway) {?>
    
        <div class="leyka-admin-gateway-card gateway-card">
            
            <div class="gateway-card-header">
                
                <div class="gateway-card-icon">
                    <?php leyka_show_gateway_logo($gateway, false);?>
                    
                    <span class="field-q">
                        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-i.svg">
                        <span class="field-q-tooltip">Описание платежной системы</span>
                    </span>
                </div>
                
                <div class="gateway-card-title"><?php echo $gateway->title;?></div>
                <div class="gateway-card-status active not-active activating">В процессе подключения</div>
                
            </div>
            
            <div class="gateway-card-params">
                
            </div>

            <div class="gateway-card-supported-pm-list">
                
            </div>
            
            <div class="gateway-card-action">
                <input type="button" class="button-primary" value="Продолжить"/>
            </div>
            
        </div>
        
    <?php }?>
    
    </div>
    
</div>