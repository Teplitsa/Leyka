<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */
?>

<p>Переходим к техническому подключению Яндекс Кассы к Лейке.</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Кликните на пункт <b>«Заполнить»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_settings-click.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Выберите пункт <b>«Платежный модуль»</b> кликнув на кружок напротив пункта и нажмите кнопку <b>«Продолжить»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_settings-payment-module.png")?>
    </div>
    
</div>
