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
    function leyka_show_gateway_logo($gateway, $show_gateway_info = true, $wrapper_classes = [], $use_paceholders = false) {

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

        <div class="<?php echo is_array($wrapper_classes) ? implode(' ', $wrapper_classes) : $wrapper_classes; ?> module-logo gateway-logo">

            <img class="module-logo-pic gateway-logo-pic" src="<?php echo $use_paceholders ? '#GATEWAY_LOGO_URL#' : $gateway->icon_url;?>" alt="">

            <?php if( !!$show_gateway_info && ($use_paceholders || $gateway->description) ) {?>
            <span class="field-q">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-info.svg" alt="">
                <span class="field-q-tooltip"><?php echo $use_paceholders ? '#GATEWAY_DESCRIPTION#' : $gateway->description;?></span>
            </span>
            <?php }?>
        </div>

    <?php }
}

if( !function_exists('leyka_show_extension_logo')) {
    function leyka_show_extension_logo($extension, $show_info = true, $wrapper_classes = [], $use_paceholders = false) {

        $use_paceholders = !!$use_paceholders;

        if( !$use_paceholders ) {
            if(is_string($extension)) {

                $extension = Leyka_Extension::get_by_id($extension);
                if( !$extension) {
                    return;
                    /** @todo throw new Exception(esc_attr__(sprintf('Unknown gateway ID: %s', $gateway), 'leyka')); */
                }

            } else if( !is_a($extension, 'Leyka_Extension') ) {
                return;
                /** @todo throw new Exception(esc_attr__(sprintf('Unknown gateway', $gateway), 'leyka')); */
            }
        }?>

        <div class="<?php echo is_array($wrapper_classes) ? implode(' ', $wrapper_classes) : $wrapper_classes; ?> module-logo extension-logo">

            <img class="module-logo-pic extension-logo-pic" src="<?php echo $use_paceholders ? '#EXTENSION_LOGO_URL#' : $extension->icon_url;?>" alt="">

            <?php if( !!$show_info && ($use_paceholders || $extension->description || $extension->full_description) ) {?>
            <span class="field-q">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-info.svg" alt="">
                <span class="field-q-tooltip"><?php echo $use_paceholders ? '#EXTENSION_DESCRIPTION#' : ($extension->full_description ? $extension->full_description : $extension->description);?></span>
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

            <div class="module-logo-wrapper"><?php leyka_show_gateway_logo($gateway, false);?></div>

            <div class="pm-info">

                <div class="pm-icons">
                <?php if($pm) {
                    if($pm->icons) {
                        foreach($pm->icons as $icon_url) {?>
                            <img class="pm-icon <?php echo $pm->full_id.' '.basename($icon_url, '.svg');?>" src="<?php echo $icon_url;?>" alt="">
                        <?php }
                    } else if($pm->main_icon) {?>
                        <img class="pm-icon <?php echo $pm->full_id.' '.basename($pm->main_icon_url, '.svg');?>" src="<?php echo $pm->main_icon_url;?>" alt="">
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

/**
 * Get current activation button label from the given gateway.
 *
 * @param $gateway Leyka_Gateway
 * @return string|false
 * @todo If we will manage to add a common Leyka_Module class for both Leyka_Gateway & Leyka_Extension, merge this function with leyka_get_extension_activation_button_label().
 */
function leyka_get_gateway_activation_button_label(Leyka_Gateway $gateway) {

    $activation_status = $gateway->get_activation_status();

    $activation_status_labels = [
        'active' => esc_attr_x('Settings', '[of the extension]', 'leyka'),
        'inactive' => esc_attr_x('Step-by-step setup', '[of the extension]', 'leyka'),
        'activating' => esc_attr_x('Continue', '[the extension step-by-step setup]', 'leyka'),
    ];

    if($activation_status !== 'active' && !leyka_gateway_setup_wizard($gateway)) {
        $label = esc_attr_x('Setup', '[the extension]', 'leyka');
    } else {
        $label = $activation_status && !empty($activation_status_labels[$activation_status]) ?
            $activation_status_labels[$activation_status] : false;
    }

    return $label;

}

/**
 * Get current activation button label from the given extension.
 *
 * @param $extension Leyka_Extension
 * @return string|false
 * @todo If we will manage to add a common Leyka_Module class for both Leyka_Gateway & Leyka_Extension, merge this function with leyka_get_gateway_activation_button_label().
 */
function leyka_get_extension_activation_button_label(Leyka_Extension $extension) {

    $activation_status = $extension->get_activation_status();

    $activation_status_labels = [
        'active' => __('Deactivate'),
        'inactive' => __('Activate'),
        'activating' => _x('Continue the setup', '[the extension step-by-step setup]', 'leyka'),
    ];

    if($activation_status !== 'active' && !$extension->wizard_id) {
        $label = __('Activate');
    } else {
        $label = $activation_status && !empty($activation_status_labels[$activation_status]) ?
            $activation_status_labels[$activation_status] : false;
    }

    return $label;

}

if( !function_exists('leyka_is_settings_step_valid') ) {
    function leyka_is_settings_step_valid($step_id) {

        $options_to_validate = [];

        if($step_id === 'receiver_type') {
            $options_to_validate[] = 'receiver_legal_type';
        } else if($step_id === 'receiver_data') {
            if(leyka()->opt('receiver_legal_type') === 'legal') {
                array_push($options_to_validate, 'org_full_name', 'org_short_name', 'org_face_position', 'org_face_fio_ip', 'org_address', 'org_state_reg_number', 'org_kpp', 'org_inn', 'org_contact_person_name', 'tech_support_email');
            } else {
                array_push($options_to_validate, 'person_full_name', 'tech_support_email', 'person_address', 'person_inn');
            }
        } else if($step_id === 'receiver_bank_essentials') {
            if(leyka()->opt('receiver_legal_type') === 'legal') {
                array_push($options_to_validate, 'org_bank_name', 'org_bank_account', 'org_bank_corr_account', 'org_bank_bic');
            } else {
                array_push($options_to_validate, 'person_bank_name', 'person_bank_account', 'person_bank_corr_account', 'person_bank_bic');
            }
        } else if($step_id === 'receiver_terms_of_service') {
            if(leyka()->opt('receiver_legal_type') === 'legal') {
                array_push($options_to_validate, 'terms_of_service_text');
            } else {
                array_push($options_to_validate, 'person_terms_of_service_text');
            }
        } else if($step_id === 'receiver_pd_terms') {
            if(leyka()->opt('receiver_legal_type') === 'legal') {
                array_push($options_to_validate, 'pd_terms_text');
            } else {
                array_push($options_to_validate, 'person_pd_terms_text');
            }
        }

        $options_invalid = [];
        foreach($options_to_validate as $option_id) {
            if( !leyka_options()->opt($option_id) || !leyka_options()->is_valid($option_id) ) {
                $options_invalid[] = $option_id;
            }
        }

        return $options_invalid ? $options_invalid : true;

    }
}