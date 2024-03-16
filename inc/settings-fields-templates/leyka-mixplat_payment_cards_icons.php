<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa payments cards. */

/** @var $this Leyka_Text_Block A block for which the template is used. */?>

<div class="<?php echo esc_attr( $this->field_type );?> custom-block-payment-cards-icons">

    <div class="gateway-supported-cards">
        <img src="<?php echo esc_attr( LEYKA_PLUGIN_BASE_URL ); ?>img/pm-icons/card-mastercard.svg" alt="">
        <img src="<?php echo esc_attr( LEYKA_PLUGIN_BASE_URL ); ?>img/pm-icons/card-visa.svg" alt="">
        <img src="<?php echo esc_attr( LEYKA_PLUGIN_BASE_URL ); ?>img/pm-icons/card-mir.svg" alt="">
        <img src="<?php echo esc_attr( LEYKA_PLUGIN_BASE_URL ); ?>img/pm-icons/card-unionpay.svg" alt="">
        <img src="<?php echo esc_attr( LEYKA_PLUGIN_BASE_URL ); ?>img/pm-icons/sbp.svg" alt="">
    </div>

    <div class="ui-accordion no-jquery-ui">

        <h2><?php _e("I'm ready to follow the connection instructions", 'leyka');?></h2>
        <div>
            <? _e("<h3 class='intro-mini-header'>Before starting registration with the payment operator, please prepare scanned copies of the documents:</h3><ul><li>Certificate of registration of your organization</li><li>Tax registration certificate (TIN)</li></ul>","leyka"); ?>
        </div>
        <h2><? _e("I'm already connected to the Mixplat", 'leyka') ;?></h2>
        <div class="single-gateway-settings gateway-yandex">

            <?php foreach(leyka_get_gateway_by_id('mixplat')->get_options_names() as $option_id) {
                $option_info = leyka_options()->get_info_of($option_id);
                if(
                    $option_id == "mixplat_service_id" || 
                    $option_id == "mixplat_widget_key" || 
                    $option_id == "mixplat_secret_key" 
                ){
                    ?>
                    <div id="<?php echo esc_attr( $option_id );?>" class="settings-block option-block type-<?php echo esc_attr( $option_info['type'] );?>">
                        <?php do_action("leyka_render_{$option_info['type']}", $option_id, $option_info);?>
                        <div class="field-errors"></div>
                    </div>
            <?php
                }
            }
            ?>

        </div>

    </div>

</div>