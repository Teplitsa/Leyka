<?php if( !defined('WPINC') ) die;

/** Custom field group for the CP payment tryout. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */
?>

<?php if(empty($this->_field_data['is_live'])) { // CP integration is in the test mode, no real money involved ?>
<table class="payment-tryout-wrapper">

    <tr>
        <th class="bank-card-data card-number">Номер карты</th>
        <th class="bank-card-data card-date">Дата</th>
        <th class="bank-card-data card-cvv">CVV</th>
        <th class="cp-payment-result">Результат тестирования</th>
    </tr>

    <tr>
        <td class="bank-card-data card-number"><span class="leyka-wizard-copy2clipboard short">5555 5555 5555 4444</span></td>
        <td class="bank-card-data card-date">12/99</td>
        <td class="bank-card-data card-cvv">123</td>
        <td class="cp-payment-result payment-result">
            <input type="button" class="do-payment sec-action not-tested" value="Сделать тестовый платёж">
            <div class="result ok">Тестирование успешно</div>
            <div class="result fail">Произошла ошибка</div>
        </td>
    </tr>

    <tr>
        <td class="bank-card-data card-number"><span class="leyka-wizard-copy2clipboard short">4242 4242 4242 4242</span></td>
        <td class="bank-card-data card-date">12/99</td>
        <td class="bank-card-data card-cvv">123</td>
        <td class="cp-payment-result payment-result">
            <input type="button" class="do-payment sec-action not-tested" value="Сделать тестовый платёж">
            <div class="result ok">Тестирование успешно</div>
            <div class="result fail">Произошла ошибка</div>
        </td>
    </tr>

</table>

<div class="payment-tryout-comment">Нажмите на кнопку «Сделать тестовый платёж» и вам покажется форма приема пожертвований CloudPayments. Проверьте каждую из карт.</div>

<?php } else { // Live payment testing ?>

<div class="payment-tryout-wrapper">

    <div class="cp-payment-result payment-result">
        <input type="button" class="do-payment sec-action not-tested live-payment" value="Провести платёж">
        <div class="result ok">Платёж успешен</div>
        <div class="result fail">Произошла ошибка</div>
    </div>

</div>

<div class="payment-tryout-comment live-payment"><span class="attention-needed">Внимание!</span> Необходимо будет ввести данные действующей карты, и с неё будут списаны реальные деньги.</div>

<?php }?>

<input type="hidden" name="payment_tryout_completed" value="0">

<a href="mailto:<?php echo LEYKA_SUPPORT_EMAIL;?>" class="call-support">Написать в поддержку</a>