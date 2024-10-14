<?php if( !defined('WPINC') ) die;
/** Custom field group for the MIXPLAT payments cards. */
/** @var $this Leyka_Text_Block A block for which the template is used. */?>

<?php 
    foreach(leyka_get_gateway_by_id('mixplat')->get_options_names() as $option_id) {
        $option_info = leyka_options()->get_info_of($option_id);
        if($option_id == "mixplat_test_mode"){
?>
            <div id="<?php echo esc_attr( $option_id );?>" class="settings-block option-block type-<?php echo esc_attr( $option_info['type'] );?>">
                <?php do_action("leyka_render_{$option_info['type']}", $option_id, $option_info);?>
                <div class="field-errors"></div>
            </div>
<?php
        }
    }
?>

<div id="more-testinfo">
    <p><strong><?php esc_html_e("Attention! Payment testing mode is enabled.", "leyka"); ?>
    <p><?php esc_html_e("The test mode is intended only for the initial setup of payments and should be turned off immediately when confirmation is received from your manager. During the enabled testing mode, no real money is debited. To test payments, you must use special bank card numbers:", "leyka");?></p>
    <ul>
        <li><b>4242424242424242</b> – <?php esc_html_e("all payments will be successful", "leyka");?>,</li>
        <li><b>5555555555554444</b> – <?php esc_html_e("all payments will be unsuccessful", "leyka");?>,</li>
        <li><b>2201382000000013</b> – <?php esc_html_e("the payment status will be selected randomly", "leyka");?>.</li>
    </ul>
    <p><?php esc_html_e("Use any date (in the future) and any CVC code. As long as the testing mode is enabled, the data of any real cards will lead to a payment error.", "leyka"); ?></p>
</div>
<p><?php esc_html_e("If you have any questions about setting up payment methods, you will be happy to help the Mixplat support service.","leyka"); ?></p>
