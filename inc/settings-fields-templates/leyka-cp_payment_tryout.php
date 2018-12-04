<?php if( !defined('WPINC') ) die;

/** Custom field group for the CP payment tryout. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */?>

<?php if(empty($this->_field_data['is_live'])) { // CP integration is in the test mode, no real money involved ?>

<table class="payment-tryout-wrapper">

    <tr>
        <th class="bank-card-data card-number"><?php esc_html_e('Bank card number', 'leyka');?></th>
        <th class="bank-card-data card-date"><?php esc_html_e('Date', 'leyka');?></th>
        <th class="bank-card-data card-cvv"><?php esc_html_e('CVV', 'leyka');?></th>
        <th class="cp-payment-result"><?php esc_html_e('Test result', 'leyka');?></th>
    </tr>

    <tr>
        <td class="bank-card-data card-number"><span class="leyka-wizard-copy2clipboard short">5555&nbsp;5555&nbsp;5555&nbsp;4444</span></td>
        <td class="bank-card-data card-date">12/99</td>
        <td class="bank-card-data card-cvv">123</td>
        <td class="cp-payment-result payment-result">
            <input type="button" class="do-payment sec-action not-tested" value="<?php esc_attr_e('Do a test payment', 'leyka');?>">
            <div class="result ok"><?php esc_html_e('The test passed', 'leyka');?></div>
            <div class="result fail"><?php esc_html_e('An error occured', 'leyka');?></div>
        </td>
    </tr>

    <tr>
        <td class="bank-card-data card-number"><span class="leyka-wizard-copy2clipboard short">4242&nbsp;4242&nbsp;4242&nbsp;4242</span></td>
        <td class="bank-card-data card-date">12/99</td>
        <td class="bank-card-data card-cvv">123</td>
        <td class="cp-payment-result payment-result">
            <input type="button" class="do-payment sec-action not-tested" value="<?php esc_attr_e('Do a test payment', 'leyka');?>">
            <div class="result ok"><?php esc_html_e('The test passed', 'leyka');?></div>
            <div class="result fail"><?php esc_html_e('An error occured', 'leyka');?></div>
        </td>
    </tr>

</table>

<div class="payment-tryout-comment">
    <?php esc_html_e('Click the "Do a test payment" button and you will see a CloudPayments payment form. Check every bank card in the list.', 'leyka');?>
</div>

<?php } else { // Live payment testing ?>

<div class="payment-tryout-wrapper">

    <div class="cp-payment-result payment-result">
        <input type="button" class="do-payment sec-action not-tested live-payment" value="Провести платёж">
        <div class="result ok"><?php esc_html_e('The test passed', 'leyka');?></div>
        <div class="result fail"><?php esc_html_e('An error occured', 'leyka');?></div>
    </div>

</div>

<div class="payment-tryout-comment live-payment">
    <span class="attention-needed"><?php esc_html_e('Warning!', 'leyka');?></span> <?php esc_html_e('You will have to enter the real and working bank card, and the real money will be taken from it.', 'leyka');?>
</div>

<?php }?>

<input type="hidden" name="payment_tryout_completed" value="0">

<a href="mailto:<?php echo LEYKA_SUPPORT_EMAIL;?>" class="call-support"><?php esc_html_e('', 'leyka');?>Написать в поддержку</a>