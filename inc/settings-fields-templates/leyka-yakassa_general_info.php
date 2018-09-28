<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa general info step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */
?>

<p>В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Кликните на кнопку <b>«Заполнить»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_general_info-click-fill.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Кликните на пункт <b>«Общие сведения»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_general_info.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Заполните поля формы используя рекомендации ниже</div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_general_info-fill-form.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Адрес сайта</div>
        <div class="body value">
            <b><?php echo preg_replace("/^http[s]?:\/\//", "", site_url())?></b>
        </div>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption bold">Примерный оборот онлайн-платежей в месяц</div>
        <div class="body">
            Если вам тяжело оценить оборот, то выберите <b>«До 1 млн Р»</b>
        </div>
    </div>

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption bold">Подлежит обязательному лицензированию</div>
        <div class="body">
            Пропускаем этот пункт, если у вас отсутствует лицензируемая деятельность
        </div>
    </div>

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption bold">У организации есть бенефициарный владелец</div>
        <div class="body">
            Не устанавливать признак того, что у организации есть бенефициарный владелец, в поле о причине отсутсвтвия бенефициарного владельца либо выбрать <b>«Юрлицо – госучреждение»</b>, либо выбрать <b>«Другое»</b> и вручную написать <b>«Благотворительная организация»</b>
        </div>
    </div>

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption bold">Есть выгодоприобретатели</div>
        <div class="body">
            По умолчанию флаг с поля снят, пропускаем этот пункт
        </div>
    </div>

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption bold">Подтверждаю отсутствие производства по делу о несостоятельности (банкротстве)</div>
        <div class="body">
            Ставим галочку
        </div>
    </div>

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption bold">Происхождение средств</div>
        <div class="body">
            Выбираем пункт <b>«Другое»</b> и в появившемся поле пишем <b>«Пожертвования»</b>
        </div>
    </div>

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption bold">Деловая репутация</div>
        <div class="body">
            Выбираем первый или второй пункт, который вам больше всего подходит
        </div>
    </div>

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="body">
            После заполнения, нажмите кнопку <b>«Сохранить»</b>
        </div>
    </div>

</div>
