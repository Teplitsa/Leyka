<?php if( !defined('WPINC') ) die; // If this file is called directly, abort?>

<?php function leyka_add_gateway_metabox($post, $args) {

    // $post is always null

    /** @var Leyka_Gateway $gateway */
    $gateway = $args['args']['gateway'];

    $pm_active = leyka_options()->opt('pm_available');?>

    <div>

    <?php foreach($gateway->get_payment_methods() as $pm) {?>
        <div>
            <input type="checkbox" name="leyka_pm_available[]" value="<?php echo $pm->full_id;?>" class="pm-active" id="<?php echo $pm->full_id;?>" data-pm-label="<?php echo $pm->title_backend;?>" <?php echo in_array($pm->full_id, $pm_active) ? 'checked="checked"' : '';?>>
            <label for="<?php echo $pm->full_id;?>"><?php echo $pm->title_backend;?></label>
        </div>
    <?php }?>

    </div>
<?php
}

foreach(leyka_get_gateways() as $gateway) {

    add_meta_box('leyka_payment_settings_gateway_'.$gateway->id, $gateway->title, 'leyka_add_gateway_metabox', 'leyka_settings_payment', 'normal', 'high', array('gateway' => $gateway,));
}?>

<h1><?php _e('Payment methods', 'leyka');?></h1>

<?php do_meta_boxes('leyka_settings_payment', 'normal', null);?>

<div id="payment-settings-area">
    <div id="active-pm-settings">
        <h1><?php _e('Active payment methods', 'leyka');?></h1>
        <p><?php _e('Please, set your gateways parameters', 'leyka');?></p>

        <?php $pm_available = leyka_options()->opt('pm_available');

            $active_gateways = array();
            foreach($pm_available as $pm_full_id) {

                $gateway_id = explode('-', $pm_full_id);
                $gateway_id = reset($gateway_id); // Strict standards

                if( !in_array($gateway_id, $active_gateways) )
                    $active_gateways[] = $gateway_id;
            }?>

        <div id="pm-settings-wrapper">
        <?php foreach(leyka_get_gateways() as $gateway) { /** @var $gateway Leyka_Gateway */ ?>
            <div id="gateway-<?php echo $gateway->id;?>" class="gateway-settings" <?php echo in_array($gateway->id, $active_gateways) ? '' : 'style="display:none;"'?>>
                <h3><?php echo $gateway->title;?></h3>
                <div>
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
        </div>
    </div>

    <h1><?php _e('Payment methods order', 'leyka');?></h1>
    <p><?php _e('Drag the elements up or down to change their order in donation forms', 'leyka');?></p>
    <ul id="pm-order-settings">
        <?php $pm_order = explode('pm_order[]=', leyka_options()->opt('pm_order'));
        array_shift($pm_order);

        foreach($pm_order as $pm) { $pm = leyka_get_pm_by_id(str_replace('&amp;', '', $pm), true);?>

            <li data-pm-id="<?php echo $pm->full_id;?>" class="pm-order"><?php echo $pm->title_backend;?></li>
        <?php }?>
    </ul>
    <input type="hidden" name="leyka_pm_order" value="<?php echo leyka_options()->opt('pm_order');?>">
</div>