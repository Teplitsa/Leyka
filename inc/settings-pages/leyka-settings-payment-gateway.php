<?php if( !defined('WPINC') ) die;?>

<div class="main-area-top">
    <a href="<?php echo admin_url('admin.php?page=leyka_settings&stage=payment');?>" class="settings-return-link">
        <?php _e('Return to the gateways list', 'leyka');?>
    </a>
</div>

<?php $gateway = leyka_get_gateway_by_id($_GET['gateway']); /** @var $gateway Leyka_Gateway */
$pm_available = leyka_options()->opt('pm_available');

if( !$gateway ) {?>
    <p class="error"><?php _e('Unknown gateway.', 'leyka');?></p>
<?php } else { // Gateway settings area ?>

<div class="main-area single-gateway-settings gateway-<?php echo $gateway->id;?>">

    <div class="gateway-settings-header">

        <div class="gateway-title">

            <?php leyka_show_gateway_logo($gateway, true, 'gateway-header-element');?>

            <h2 class="gateway-header-element"><?php echo $gateway->title;?></h2>

            <?php if($gateway->registration_url) {?>
            <a href="<?php echo $gateway->registration_url;?>" class="gateway-link gateway-registration-link" target="_blank">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-outer-link.svg" alt="">
            </a>
            <?php }?>

            <?php if($gateway->docs_url) {?>
            <a href="<?php echo $gateway->docs_url;?>" class="gateway-link gateway-docs-link" target="_blank">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-docs-link.svg" alt="">
            </a>
            <?php }?>

        </div>

        <?php leyka_gateway_details_html($gateway);?>

    </div>

    <div class="gateway-settings-wrapper">

        <div class="gateway-settings">

            <h3><?php _e('Gateway settings', 'leyka');?></h3>

        <?php foreach($gateway->get_options_names() as $option_id) {

            $option_info = leyka_options()->get_info_of($option_id);?>

            <div id="<?php echo $option_id;?>" class="settings-block option-block type-<?php echo $option_info['type'];?>">
                <?php do_action("leyka_render_{$option_info['type']}", $option_id, $option_info);?>
                <div class="field-errors"></div>
            </div>

        <?php }

        foreach($gateway->get_payment_methods() as $pm) { /** @var $pm Leyka_Payment_Method */?>

            <div id="pm-<?php echo $pm->full_id;?>" class="pm-settings" <?php echo in_array($pm->full_id, $pm_available) ? '' : 'style="display:none;"';?>>

                <?php foreach($pm->get_pm_options_names() as $option_id) {

                    $option_info = leyka_options()->get_info_of($option_id);?>

                    <div id="<?php echo $option_id;?>" class="settings-block option-block type-<?php echo $option_info['type'];?>">
                        <?php do_action("leyka_render_{$option_info['type']}", $option_id, $option_info);?>
                        <div class="field-errors"></div>
                    </div>

                <?php }?>

            </div>

        <?php }

        if($gateway->has_wizard) {?>
            <a class="gateway-header-element gateway-wizard-link" href="<?php echo $gateway->wizard_url;?>" title="<?php esc_attr_e('Open the gateway setup wizard', 'leyka');?>">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-wizard.svg" alt=""><?php _e('Step-by-step setup', 'leyka');?>
            </a>
        <?php }?>

        </div>

        <div class="gateway-pm-list">

            <h3><?php _e('Payment methods', 'leyka');?></h3>

            <?php $pm_list_by_categories = $gateway->get_payment_methods(null, false, true);
            $commissions = leyka_options()->opt('commission');

            foreach($pm_list_by_categories as $category_id => $pm_list) {

                if( !$pm_list ) {
                    continue;
                }?>

                <?php if(count($pm_list_by_categories) > 1) {?>
                <h4><?php echo leyka_get_pm_category_label($category_id);?></h4>
                <?php }?>

                <?php foreach($pm_list as $pm) { /** @var $pm Leyka_Payment_Method */ ?>

                <div id="<?php echo $pm->full_id;?>" class="settings-block option-block type-checkbox">

                    <div id="<?php echo $pm->full_id.'-wrapper';?>">

                        <label>
                            <span class="field-component field">
                                <input type="checkbox" id="<?php echo $pm->full_id;?>" class="pm-available" name="leyka_pm_available[]" value="<?php echo $pm->full_id;?>" data-pm-label="<?php echo $pm->title_backend;?>" data-pm-label-backend="<?php echo $pm->label_backend;?>" <?php echo in_array($pm->full_id, $pm_available) ? 'checked="checked"' : '';?>> <?php echo $pm->title_backend;?>
                            </span>
                        </label>

                        <?php if($pm->description) {?>
                        <span class="field-q">
                            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                            <span class="field-q-tooltip"><?php echo $pm->description;?></span>
                        </span>
                        <?php }?>

                    </div>

                    <div id="<?php echo $pm->full_id.'-commission-wrapper';?>" class="pm-commission-wrapper" <?php echo in_array($pm->full_id, $pm_available) ? '' : 'style="display:none;"';?>>
                        <label>
                            <input type="number" class="leyka-commission-field" name="leyka_commission[<?php echo $pm->full_id;?>]" value="<?php echo empty($commissions[$pm->full_id]) ? '' : (float)$commissions[$pm->full_id];?>" step="0.01" min="0.0" max="100.0" id="leyka_commission_<?php echo $pm->full_id;?>" placeholder="<?php esc_attr_e('Commission size', 'leyka');?>">
                        </label>%
                    </div>

                </div>

                <?php }

            }?>
        </div>

    </div>

    <div class="gateway-settings-submit">

        <a href="#" class="gateway-turn-off"><?php _e('Turn off the gateway', 'leyka');?></a>

        <input type="submit" name="<?php echo "leyka_settings_{$_GET['stage']}_submit";?>" value="<?php esc_attr_e('Save settings', 'leyka');?>" class="button-primary">

    </div>

</div>

<?php }