<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */
?>

<p>Менеджер кассы проверяет анкету и формирует заявление, которое станет доступным для скачивания в личном кабинете.</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Скачайте документы из кабинета Яндекс.Кассы</div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_sign_documents.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="body">
            <p>Распечатайте все листы и на последнем листе документа указажите дату подписи, подпишите у руководителя и поставьте печать организации</p>
            <p>Загрузите сканы всех листов документа в личный кабинет Яндекс.Кассы</p>
        </div>
    </div>
    
</div>
