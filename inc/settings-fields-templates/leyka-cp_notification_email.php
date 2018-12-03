<?php if( !defined('WPINC') ) die;

/** Custom field group for the CP payments cards. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */
?>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

<span class="info2copy leyka-wizard-copy2clipboard"><?php echo get_option('admin_email')?></span>

<div class="captioned-screen">
    <p>Перевидите флажок у поля «Fail» в режим «Включено» и вставьте адрес  в поле, как на скриншоте:</p>
    <div class="screen-wrapper">
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/cp/cp_notification_email.png" class="leyka-instructions-screen" />
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-zoom-screen.svg" class="zoom-screen" />
    </div>
    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/cp/cp_notification_email.png" class="leyka-instructions-screen-full" />
</div>

</div>
