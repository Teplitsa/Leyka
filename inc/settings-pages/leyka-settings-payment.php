<?php if( !defined('WPINC') ) die; // If this file is called directly, abort

$current_screen_id = get_current_screen()->id;

function leyka_add_gateway_metabox($post, $args) {

    // $post is always null

    /** @var Leyka_Gateway $gateway */
    $gateway = $args['args']['gateway'];

    $pm_active = leyka_options()->opt('pm_available'); ?>

    <div>

        <?php foreach($gateway->get_payment_methods() as $pm) {?>
            <div>
                <input type="checkbox" name="leyka_pm_available[]" value="<?php echo $pm->full_id;?>" class="pm-active" id="<?php echo $pm->full_id;?>" data-pm-label="<?php echo $pm->title_backend;?>" data-pm-label-backend="<?php echo $pm->label_backend;?>" <?php echo in_array($pm->full_id, $pm_active) ? 'checked="checked"' : '';?>>
                <label for="<?php echo $pm->full_id;?>"><?php echo $pm->title_backend;?></label>
            </div>
        <?php }?>

    </div>
<?php
}

function leyka_gateway_admin_icon_markup($gateway) {

    return $gateway->icon ?
        "<span class='gw-icon'><img src='".esc_url($gateway->icon)."'></span>" :
        "<span class='dashicons dashicons-admin-page'></span>";
}

$gateways_by_columns = array('side' => array(), 'normal' => array());
foreach(leyka_get_gateways() as $gateway) { // Place gateways metaboxes in their respective columns

    $gateways_by_columns[$gateway->admin_ui_column == 1 ? 'side' : 'normal'][$gateway->admin_ui_order][] = $gateway;
}

foreach($gateways_by_columns as $admin_ui_column => $gateways) { // Add gateways metaboxes

    ksort($gateways); // Sort by gateways priority

    foreach($gateways as $priority => $gateways_list) {

        foreach($gateways_list as $gateway) {

            $pm_active = leyka_options()->opt('pm_available');
            $active = '';

            if($pm_active) {
                foreach($pm_active as $pm_id) {

                    $test = explode('-', $pm_id);
                    if(trim($test[0]) == $gateway->id) {

                        $active = " <span class='active'>".__('active', 'leyka')."</span>";
                        break;
                    }
                }
            }

            add_meta_box(
                'leyka_payment_settings_gateway_'.$gateway->id,
                leyka_gateway_admin_icon_markup($gateway).$gateway->title.$active,
                'leyka_add_gateway_metabox',
                $current_screen_id,
                $admin_ui_column, // This is a column distribution only by default
                'high',
                array('gateway' => $gateway,)
            );
        }
    }
}?>

<div id="post-body" class="metabox-holder columns-3">
    <div id="leyka-pm-selectors">
        <div id="postbox-container-1" class="postbox-container"><?php do_meta_boxes('', 'side', null);?></div>
        <div id="postbox-container-2" class="postbox-container"><?php do_meta_boxes('', 'normal', null);?></div>
    </div>
</div>

<div id="payment-settings-area">

    <div class="pm-active-panel">

        <div id="active-pm-settings" class="panel-content">
            <h3 class="panel-title"><?php _e('Payment gateways parameters', 'leyka');?></h3>           

            <?php
                $pm_available = leyka_options()->opt('pm_available');
    
                $active_gateways = array();
                foreach($pm_available as $pm_full_id) {

                    $gateway_id = explode('-', $pm_full_id);
                    $gateway_id = reset($gateway_id); // Strict standards

                    if( !in_array($gateway_id, $active_gateways) ) {
                        $active_gateways[] = $gateway_id;
                    }
                }?>

            <div id="pm-settings-wrapper">
            <?php foreach(leyka_get_gateways() as $gateway) { /** @var $gateway Leyka_Gateway */ ?>

                <div id="gateway-<?php echo $gateway->id;?>" class="gateway-settings" <?php echo in_array($gateway->id, $active_gateways) ? '' : 'style="display:none;"'?>>
                    <h3 class="accordion-section-title">
                        <?php echo leyka_gateway_admin_icon_markup($gateway).$gateway->title;?>
                        <?php echo $gateway->docs_link ? ' <a class="doc-link" href="'.esc_url($gateway->docs_link).'" target="_blank">'.__('Setup Help', 'leyka').'</a>' : '';?>
                    </h3>
                    <div class="accordion-section-content">
                        <?php foreach($gateway->get_options_names() as $option_id) {
    
                            $option = leyka_options()->get_info_of($option_id);
                            do_action("leyka_render_{$option['type']}", $option_id, $option);
                        }

                        foreach($gateway->get_payment_methods() as $pm) { /** @var $pm Leyka_Payment_Method */ ?>

                            <div id="pm-<?php echo $pm->full_id;?>" class="pm-settings" <?php echo in_array($pm->full_id, $pm_available) ? '' : 'style="display:none;"';?>>
                            <?php foreach($pm->get_pm_options_names() as $option_id) {
    
                                $option = leyka_options()->get_info_of($option_id);
                                do_action("leyka_render_{$option['type']}", $option_id, $option);
                            }?>
                            </div>
                        <?php }?>
                    </div>
                </div>
            <?php }?>
            </div><!-- #pm-settings-wrapper -->

        </div><!-- #active-pm-settings -->
    </div><!-- .active-pm-panel -->

    <?php function leyka_pm_sortable_option_html($is_hidden = false, $full_id = '#FID#', $label = '#L#', $label_backend = '#LB#') {?>

        <li data-pm-id="<?php echo $full_id;?>" class="pm-order" <?php echo !!$is_hidden ? 'style="display:none"' : '';?>>
            <?php //echo $label_backend == $label ? '' : $label_backend.'<br>';?>
            <span class="pm-label" id="pm-label-<?php echo $full_id;?>"><?php echo $label;?></span>
            <span class="pm-label-fields" style="display:none;">
                <input type="text" id="pm_labels[<?php echo $full_id;?>]" value="<?php echo $label;?>" placeholder="<?php _e('Enter some title for this payment method', 'leyka');?>">
                <input type="hidden" class="pm-label-field" name="leyka_<?php echo $full_id;?>_label" value="<?php echo $label;?>">
                <span class="new-pm-label-ok"><span class="dashicons dashicons-yes"></span></span>
                <span class="new-pm-label-cancel"><span class="dashicons dashicons-no"></span></span>
            </span>
            <span class="pm-change-label" data-pm-id="<?php echo $full_id;?>"><span class="dashicons dashicons-edit"></span></span>
        </li>

    <?php }?>

    <div class="pm-order-panel"><div class="panel-content">

        <h3 class="panel-title"><?php _e('Payment methods order', 'leyka');?></h3>
        <p class="panel-desc"><?php _e('Drag the elements up or down to change their order on donation forms', 'leyka');?></p>
        <ul id="pm-order-settings">
            <?php leyka_pm_sortable_option_html(true);

            $pm_order = explode('pm_order[]=', leyka_options()->opt('pm_order'));
            array_shift($pm_order);

            foreach($pm_order as $i => &$pm_full_id) {

                $pm_full_id = str_replace('&amp;', '', $pm_full_id);
                $pm = leyka_get_pm_by_id($pm_full_id, true);

                if($pm && in_array($pm_full_id, $pm_available) ) {
                    leyka_pm_sortable_option_html(false, $pm_full_id, $pm->label, $pm->label_backend);
                } else {
                    unset($pm_order[$i]);
                }
            }

            $pm_order_flipped = array_flip($pm_order); // Somehow in_array() working incorrectly for no reason :((
            foreach($pm_available as $pm_full_id) { // Add to the end of the order all PMs that are out of this order

                if( !array_key_exists($pm_full_id, $pm_order_flipped) ) {

                    $pm = leyka_get_pm_by_id($pm_full_id, true);
                    if($pm) {

                        leyka_pm_sortable_option_html(false, $pm_full_id, $pm->label, $pm->label_backend);
                        $pm_order[] = $pm_full_id;
                    }
                }
            }?>
        </ul>

        <input type="hidden" name="leyka_pm_order" value="<?php echo leyka_options()->opt('pm_order');?>">

        <p class="submit">
            <?php /** @var string $current_stage "payment" for this place */?>
            <input type="submit" name="<?php echo "leyka_settings_{$current_stage}";?>_submit" value="<?php _e('Save settings', 'leyka'); ?>" class="button-primary">
        </p>

    </div></div>

</div><!-- #payment-settings-area -->