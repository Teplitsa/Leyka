<?php if( !defined('WPINC') ) die; // If this file is called directly, abort?>

<h3><?php _e('Payment methods', 'leyka');?></h3>

<?php function leyka_add_gateway_metabox($post, $args) {

    // $post is always null

    /** @var Leyka_Gateway $gateway */
    $gateway = $args['args']['gateway'];

    $pm_active = leyka_options()->opt('pm_available');?>

    <div>

    <?php foreach($gateway->get_payment_methods() as $pm) {?>
        <div>
            <input type="checkbox" name="leyka_pm_available[]" value="<?php echo $pm->full_id;?>" id="<?php echo $pm->full_id;?>" class="pm-active" <?php echo in_array($pm->full_id, $pm_active) ? 'checked="checked"' : '';?>>
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

<!-- Metaboxes reordering and folding support -->
<form style="display:none" method="get" action="">
    <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
    <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
</form>

<div id="payment-settings-area">

    <div id="active-pm-settings">
        <h1><?php _e('Active payment methods', 'leyka');?></h1>
        <p><?php _e('Please, set your gateways parameters', 'leyka');?></p>

        <div id="pm-settings-wrapper">
        <?php foreach(leyka_get_gateways() as $gateway) { /** @var $gateway Leyka_Gateway */ ?>
            <h3><?php echo $gateway->title;?></h3>
            <div>
            <?php foreach($gateway->get_options_names() as $option_id) {

                $option = leyka_options()->get_info_of($option_id);
                do_action("leyka_render_{$option['type']}", $option_id, $option);
            }

            foreach($gateway->get_payment_methods() as $pm) { /** @var $pm Leyka_Payment_Method */

                foreach($pm->get_pm_options_names() as $option_id) {

                    $option = leyka_options()->get_info_of($option_id);
                    do_action("leyka_render_{$option['type']}", $option_id, $option);
                }
            }?>
            </div>
        <?php }?>
        </div>
    </div>

    <div id="pm-order-settings">

    </div>

</div>