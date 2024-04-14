<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa payments cards. */

/** @var $this Leyka_Text_Block A block for which the template is used. */?>

<div class="<?php echo esc_attr( $this->field_type );?> custom-block-payment-cards-icons">

    <div class="gateway-supported-cards">
        <img src="<?php echo esc_attr( LEYKA_PLUGIN_BASE_URL ); ?>img/icon-card-mastercard.svg" alt="">
        <img src="<?php echo esc_attr( LEYKA_PLUGIN_BASE_URL ); ?>img/icon-card-visa.svg" alt="">
        <img src="<?php echo esc_attr( LEYKA_PLUGIN_BASE_URL ); ?>img/icon-card-mir.svg" alt="">
    </div>

    <div class="ui-accordion no-jquery-ui">

        <h2><?php esc_html_e("I'm ready to follow the connection instructions", 'leyka');?></h2>
        <div>

            <h3 class="intro-mini-header"><?php esc_html_e('Before you begin the registration, please, prepare scaned copies of the following documents (jpg/png):', 'leyka');?></h3>

            <ul>
                <li><?php esc_html_e("Organization head's passport - the full main page and the registration page.", 'leyka');?></li>
                <li><?php esc_html_e('The organization state registration certificate.', 'leyka');?></li>
            </ul>

        </div>

        <h2><?php esc_html_e("I already have the ShopID and secret key parameters", 'leyka');?></h2>
        <div class="single-gateway-settings gateway-yandex">

            <?php foreach(leyka_get_gateway_by_id('yandex')->get_options_names() as $option_id) {

            $option_info = leyka_options()->get_info_of($option_id);?>

            <div id="<?php echo esc_attr( $option_id );?>" class="settings-block option-block type-<?php echo esc_attr( $option_info['type'] );?>">
                <?php do_action("leyka_render_{$option_info['type']}", $option_id, $option_info);?>
                <div class="field-errors"></div>
            </div>

            <?php }?>

        </div>

    </div>

</div>