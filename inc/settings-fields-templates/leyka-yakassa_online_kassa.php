<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */
?>

<p>НКО не нужно использовать он-лайн кассу, поэтому выбираем пункт <b>«Самостоятельно»</b> и нажмите кнопку <b>«Отправить»</b></p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_online_kassa.png")?>
    </div>
    
</div>
