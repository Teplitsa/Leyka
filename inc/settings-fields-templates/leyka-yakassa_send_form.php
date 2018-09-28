<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */
?>

<p>В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Все данные Анкеты заполнены. Нажмите на кнопку <b>«Отправить анкету»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_send_form.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="body">
            <p>Процес проверки занимает 2-3 рабочих дня. Вам придет уведомление на почту о завершении проверки или вы можете узнать о завершении проверки <a href="https://kassa.yandex.ru/" target="_blank">в личном кабинете на Яндекс Кассы</a></p>
            <p>Сейчас можно выйти из нашего мастера установки. Мы запомним, где вы прервали процесс.</p>
        </div>
    </div>
    
</div>
