<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa payments cards. */

/** @var $this Leyka_Text_Block A block for which the template is used. */
?>

<div class="<?php echo $this->field_type;?> custom-block-payment-cards-icons">

<div class="cp-supported-cards">
    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-card-mastercard.svg" />
    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-card-visa.svg" />
    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-card-mir.svg" />
</div>

<h3>Перед началом регистрации приготовьте, пожалуйста, сканированные копии документов (jpg/png):</h3>
<ul>
    <li>Паспорт руководителя: разворот главной страницы и страницу с регистрацией</li>
    <li>Свидетельство о регистрации в министерстве юстиции РФ</li>
</ul>

</div>