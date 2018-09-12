<?php if( !defined('WPINC') ) die;

/** Custom field group for the CP payment tryout. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */
?>

<table class="payment-tryout-wrapper">

    <tr>
        <th class="bank-card-data card-number">Номер карты</th>
        <th class="bank-card-data card-date">Дата</th>
        <th class="bank-card-data card-cvv">CVV</th>
        <th class="cp-payment-result">Результат тестирования</th>
    </tr>

    <tr>
        <td class="bank-card-data card-number">5555&nbsp;5555&nbsp;5555&nbsp;4444</td>
        <td class="bank-card-data card-date">12/99</td>
        <td class="bank-card-data card-cvv">123</td>
        <td class="cp-payment-result">
            <input type="button" class="do-payment sec-action" value="Сделать тестовый платёж" data-is-testing-passed="0">
            <div class="result ok">Тестирование успешно</div>
        </td>
    </tr>

    <tr>
        <td class="bank-card-data card-number">4242&nbsp;4242&nbsp;4242&nbsp;4242</td>
        <td class="bank-card-data card-date">12/99</td>
        <td class="bank-card-data card-cvv">123</td>
        <td class="cp-payment-result">
            <input type="button" class="do-payment sec-action" value="Сделать тестовый платёж" data-is-testing-passed="0">
            <div class="result ok">Тестирование успешно</div>
        </td>
    </tr>

</table>

<input type="hidden" name="payment_tryout_completed" value="0">

<div class="payment-tryout-comment">Нажмите на кнопку «Начать тестовое пожертвование» и вам покажется форма приема пожертвований CloudPayments. Проверьте каждую из карт.</div>

<div class="error-message"></div>

<a href="mailto:<?php echo LEYKA_SUPPORT_EMAIL;?>" class="call-support">Написать в поддержку</a>