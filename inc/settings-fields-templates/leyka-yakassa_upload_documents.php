<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */
?>

<p>В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Кликните на пункт <b>«Загрузка документов»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_upload_documents-click.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">По очереди загрузите документы. Нажимайте на кнопку <b>«Выбрать файл»</b> и добавляйте файлы.</div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_upload_documents-add-file.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="body">
            <p>Если вы являетесь зарегистрированным НКО, то в поле «Другие документы» загрузите скан свидетельства о регистрации в министерстве юстиции РФ.</p>
            <p>Если такого документа у вас нет, пропустите этот пункт</p>
        </div>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="body">
            После заполнения, нажмите кнопку <b>«Сохранить»</b>
        </div>
    </div>
    
</div>
