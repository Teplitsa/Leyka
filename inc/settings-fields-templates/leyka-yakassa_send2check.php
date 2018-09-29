<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */
?>

<p>Обычно этот процес занимает 2-3 рабочих дня. Вам придет уведомление на почту о завершении проверки.</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_send2check.png")?>
        <div class="body">
            Вы можете выйти из мастера установки – мы запомним, где вы прервали процесс.
        </div>
    </div>
    
</div>
