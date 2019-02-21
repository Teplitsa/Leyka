<?php if( !defined('WPINC') ) die;

if( !function_exists('leyka_show_wizard_captioned_screenshot')) {
    function leyka_show_wizard_captioned_screenshot($img_path, $img_path_full = false) {

        if( !$img_path_full) {
            $img_path_full = $img_path;
        }?>

        <div class="captioned-screen">
            <div class="screen-wrapper">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/<?php echo $img_path;?>" class="leyka-instructions-screen" alt="">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-zoom-screen.svg" class="zoom-screen" alt="">
            </div>
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/<?php echo $img_path_full;?>" class="leyka-instructions-screen-full" alt="">
        </div>

    <?php }
}

if( !function_exists('leyka_show_gateway_logo')) {
    function leyka_show_gateway_logo($gateway, $show_gateway_info = true, $wrapper_classes = array(), $use_paceholders = false) {

        $use_paceholders = !!$use_paceholders;

        if( !$use_paceholders ) {
            if(is_string($gateway)) {

                $gateway = leyka_get_gateway_by_id($gateway);
                if( !$gateway) {
                    return;
                    /** @todo throw new Exception(esc_attr__(sprintf('Unknown gateway ID: %s', $gateway), 'leyka')); */
                }

            } else if( !is_a($gateway, 'Leyka_Gateway') ) {
                return;
                /** @todo throw new Exception(esc_attr__(sprintf('Unknown gateway', $gateway), 'leyka')); */
            }
        }?>

        <div class="<?php echo is_array($wrapper_classes) ? implode(' ', $wrapper_classes) : $wrapper_classes; ?> gateway-logo">

            <img class="gateway-logo-pic" src="<?php echo $use_paceholders ? '#GATEWAY_LOGO_URL#' : $gateway->icon_url;?>" alt="">

            <?php if( !!$show_gateway_info && ($use_paceholders || $gateway->description) ) {?>
            <span class="field-q">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-info.svg" alt="">
                <span class="field-q-tooltip"><?php echo $use_paceholders ? '#GATEWAY_DESCRIPTION#' : $gateway->description;?></span>
            </span>
            <?php }?>
        </div>

    <?php }
}

if( !function_exists('leyka_pm_sortable_option_html_new') ) {
    function leyka_pm_sortable_option_html_new($is_hidden = false, $pm_full_id = '#FID#', $pm_label = '#L#') {

        $is_hidden = !!$is_hidden;

        $pm = leyka_get_pm_by_id($pm_full_id, true);
        $gateway = $pm ? $pm->gateway : false;?>

        <li class="pm-order" data-pm-id="<?php echo $pm_full_id;?>" <?php echo $is_hidden ? 'style="display:none;"' : '';?>>

            <div class="gateway-logo-wrapper"><?php leyka_show_gateway_logo($gateway, false);?></div>

            <div class="pm-info">

                <div class="pm-icons">
                <?php if($pm) {
                    if($pm->icons) {
                        foreach($pm->icons as $icon_url) {?>
                            <img class="pm-icon" src="<?php echo $icon_url;?>" alt="">
                        <?php }
                    } else if($pm->main_icon) {?>
                        <img class="pm-icon" src="<?php echo $pm->main_icon_url;?>" alt="">
                    <?php }
                }?>
                </div>

                <div class="pm-label-wrapper">

                    <span class="pm-label" id="pm-label-<?php echo $pm_full_id;?>"><?php echo $pm_label;?></span>

                    <span class="pm-label-fields" style="display:none;">

                        <input type="text" id="pm_labels[<?php echo $pm_full_id;?>]" class="pm-label-input-field" value="<?php echo $pm_label;?>" placeholder="<?php esc_html_e('Enter some title for this payment method', 'leyka');?>">
                        <input type="hidden" class="pm-label-field <?php echo $is_hidden ? '' : 'submitable';?>" name="leyka_<?php echo $pm_full_id;?>_label" value="<?php echo $pm_label;?>">
                        <span class="new-pm-label-control new-pm-label-ok dashicons dashicons-yes"></span>
                        <span class="new-pm-label-control new-pm-label-cancel dashicons dashicons-no"></span>

                    </span>

                    <img class="pm-control pm-change-label" data-pm-id="<?php echo $pm_full_id;?>" src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-edit-circled.svg" title="<?php esc_attr_e('Edit the payment method label', 'leyka');?>" alt="">
                    <img class="pm-control pm-deactivate" data-pm-id="<?php echo $pm_full_id;?>" src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-delete-circled.svg" title="<?php esc_attr_e('Deactivate the payment method', 'leyka');?>" alt="">

                </div>

            </div>

        </li>

    <?php }
}

if( !function_exists('leyka_gateway_details_html') ) {
    function leyka_gateway_details_html($gateway) {?>
    
        <div class="gateway-details">

            <div class="details-element gateway-commission field-q">

                <div class="details-pic">
                <?php if($gateway->min_commission && $gateway->min_commission > 0.0) {?>
                    <?php esc_html_e('from', 'leyka');?>&nbsp;<span class="commission-size"><?php echo $gateway->min_commission;?>%</span>
                <?php } else {?>
                    <span class="commission-size">?</span>
                <?php }?>
                </div>

                <div class="details-label"><?php esc_html_e('commission', 'leyka');?></div>
                
                <span class="field-q-tooltip">
                <?php if($gateway->min_commission && $gateway->min_commission > 0.0) {
                    printf(esc_html__('Commission from %s%%', 'leyka'), $gateway->min_commission);
                } else {
                    esc_html_e('Commission is unknown', 'leyka');
                }?>
                </span>
            </div>

            <div class="details-element gateway-has-recurring field-q">
                <div class="details-pic has-recurring-icon">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-recurring<?php echo $gateway->has_recurring ? '' : '-no';?>.svg" alt="">
                </div>
                <div class="details-label">
                    <?php echo mb_strtolower(esc_html_x('recurring', 'a "recurring donations" in one word (like "recurrings")', 'leyka'));?>
                </div>
                <span class="field-q-tooltip">
                    <?php echo $gateway->has_recurring ?
                        esc_attr__('Recurring supported.', 'leyka') : esc_attr__('Recurring not supported.', 'leyka');?>
                </span>
            </div>

            <?php if($gateway->receiver_types) {?>
            
            <div class="details-element gateway-receiver-types field-q">
                <div class="details-pic receiver-type-icon">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-receiver-type-<?php echo count($gateway->receiver_types) > 1 ? 'all' : $gateway->receiver_types[0];?>.svg" alt="">
                </div>
                <div class="details-label"><?php esc_html_e('receiver', 'leyka');?></div>
                <span class="field-q-tooltip"><?php echo leyka_get_receiver_description($gateway->receiver_types);?></span>
            </div>
            
            <?php }?>

        </div>
    
    <?php }
}