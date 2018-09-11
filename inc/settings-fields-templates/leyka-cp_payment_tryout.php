<?php if( !defined('WPINC') ) die;

/** Custom field group for the CP payment tryout. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */
?>

<table class="payment-tryout-wrapper">

    <tr>
        <th>Номер карты</th>
        <th>Дата</th>
        <th>CVV</th>
        <th></th>
    </tr>

    <tr>
        <td>5555&nbsp;5555&nbsp;5555&nbsp;4444</td>
        <td>12/99</td>
        <td>123</td>
        <td class="cp-payment-result">
            <input type="button" class="do-payment sec-action" value="Сделать тестовый платёж" data-status="not-completed">
            <div class="result ok">Успешный результат</div>
        </td>
    </tr>

    <tr>
        <td>5105&nbsp;1051&nbsp;0510&nbsp;5100</td>
        <td>12/99</td>
        <td>123</td>
        <td class="cp-payment-result">
            <input type="button" class="do-payment sec-action" value="Сделать тестовый платёж" data-status="not-completed">
            <div class="result fail">Недостаточно средств на карте</div>
        </td>
    </tr>

</table>

<input type="hidden" name="payment_tryout_completed" value="0">

<div class="payment-tryout-comment">Нажмите на кнопку «Начать тестовое пожертвование» и вам покажется форма приема пожертвований CloudPayments. Проверьте каждую из карт.</div>

<div class="error-message"></div>

<a href="mailto:<?php echo LEYKA_SUPPORT_EMAIL;?>" class="call-support">Написать в поддержку</a>