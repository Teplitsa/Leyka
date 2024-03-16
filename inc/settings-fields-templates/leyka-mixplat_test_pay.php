<?php if( !defined('WPINC') ) die;
/** Custom field group for the Yandex Kassa payments cards. */
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
    <?php
        _e("<p><strong>Attention! Payment testing mode is enabled.</strong></p><p>The test mode is intended only for the initial setup of payments and should be turned off immediately when confirmation is received from your manager. During the enabled testing mode, no real money is debited. To test payments, you must use special bank card numbers:</p><ul><li><b>4242424242424242</b> – all payments will be successful,</li><li><b>5555555555554444</b> – all payments will be unsuccessful,</li><li><b>2201382000000013</b> – the payment status will be selected randomly.</li></ul><p>Use any date (in the future) and any CVC code. As long as the testing mode is enabled, the data of any real cards will lead to a payment error.</p>", "leyka");
    ?>
</div>
<p>
    <?php
        _e("If you have any questions about setting up payment methods, you will be happy to help the Mixplat support service.","leyka");
    ?>
</p>