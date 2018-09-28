<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa contact info step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */
?>

<p>В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Кликните на пункт <b>«Контактная информация»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_contact_info-click.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Добавьте тех, кто имеет отношение к подключению и работе с Яндекс.Кассой и после заполнения, нажмите кнопку <b>«Сохранить»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_contact_info-save.png")?>
    </div>
    
</div>
