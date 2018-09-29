<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */
?>

<p>Давайте проверим работу Яндекс Кассы заплатив небольшую сумму сами себе. После проведения платежи деньги будут зачислены на расчетный счет, указанный ранее в Яндекс Кассе в течение 1 банковского дня</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

<div class="payment-tryout-wrapper">
    <input type="button" class="button button-secondary" value="Реальное пожертвование">
</div>

<div class="payment-tryout-comment live-payment"><span class="attention-needed">Внимание!</span> Необходимо будет ввести данные действующей карты и деньги будут с нее списаны.</div>

<input type="hidden" name="payment_completed" value="0">
    
</div>
