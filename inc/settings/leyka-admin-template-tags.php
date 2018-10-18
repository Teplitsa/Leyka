<?php if( !defined('WPINC') ) die;

if( !function_exists('leyka_show_wizard_captioned_screenshot')) {
    function leyka_show_wizard_captioned_screenshot($img_path, $img_path_full = false) {

        if( !$img_path_full) {
            $img_path_full = $img_path;
        } ?>

        <div class="captioned-screen">
            <div class="screen-wrapper">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL; ?>img/<?php echo $img_path; ?>" class="leyka-instructions-screen">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL; ?>img/icon-zoom-screen.svg" class="zoom-screen">
            </div>
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL ?>img/<?php echo $img_path_full; ?>" class="leyka-instructions-screen-full">
        </div>
        <?php
    }
}

if( !function_exists('leyka_show_gateway_logo')) {
    function leyka_show_gateway_logo($gateway, $show_gateway_info = true, $wrapper_classes = array()) {

        if(is_string($gateway)) {

            $gateway = leyka_get_gateway_by_id($gateway);
            if( !$gateway) {
                return;
                /** @todo throw new Exception(esc_attr__(sprintf('Unknown gateway ID: %s', $gateway), 'leyka')); */
            }

        } else if( !is_a($gateway, 'Leyka_Gateway')) {
            return;
            /** @todo throw new Exception(esc_attr__(sprintf('Unknown gateway', $gateway), 'leyka')); */
        } ?>

        <div class="<?php echo is_array($wrapper_classes) ? implode(' ', $wrapper_classes) : $wrapper_classes; ?> gateway-logo">

            <img class="gateway-logo-pic" src="<?php echo $gateway->icon_url; ?>">

            <?php if( ! !$show_gateway_info) { ?>
                <a href="#" class="gateway-description-icon" data-gateway-info="<?php echo esc_attr($gateway->description); ?>">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL; ?>img/icon-info.svg">
                </a>
            <?php } ?>
        </div>

    <?php }
}