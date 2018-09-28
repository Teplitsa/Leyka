<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa contact info step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */
?>

<p>В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Кликните на пункт <b>«Данные руководителя»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_boss_info-click.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">У вас на руках сканы паспорта вашего руководителя. Введите все необходимые данные.  После заполнения, нажмите кнопку <b>«Сохранить»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_boss_info-fill-form.png")?>
    </div>
    
</div>
