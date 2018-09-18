<?php if( !defined('WPINC') ) die;

/** Custom field group for the CP payments cards. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */
?>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

<div class="captioned-screen">
    <p>Если вы успешно залогинились, то увидите примерно такую страницу:</p>
    <div class="screen-wrapper">
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/cp/cp_account_setup-after-login.png" class="leyka-instructions-screen" />
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-zoom-screen.svg" class="zoom-screen" />
    </div>
    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/cp/cp_account_setup-after-login.png" class="leyka-instructions-screen-full" />
</div>

<div class="captioned-screen">
    <p>Кликните на мобильное меню слева и выберите пункты «Сайты»</p>
    <div class="screen-wrapper">
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/cp/cp_account_setup-choose-sites.png" class="leyka-instructions-screen"  />
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-zoom-screen.svg" class="zoom-screen" />
    </div>
    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/cp/cp_account_setup-choose-sites.png" class="leyka-instructions-screen-full"  />
</div>

<div class="captioned-screen">
    <p>Выберите пункт «Добавить сайт»</p>
    <div class="screen-wrapper">
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/cp/cp_account_setup-add-site.png" class="leyka-instructions-screen"  />
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-zoom-screen.svg" class="zoom-screen" />
    </div>
    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/cp/cp_account_setup-add-site.png" class="leyka-instructions-screen-full"  />
</div>

<div class="captioned-screen">
    <p>Добавьте название и адрес вашего сайта</p>
    <div class="screen-wrapper">
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/cp/cp_account_setup-set-site-name.png" class="leyka-instructions-screen"  />
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-zoom-screen.svg" class="zoom-screen" />
    </div>
    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/cp/cp_account_setup-set-site-name.png" class="leyka-instructions-screen-full"  />
</div>

<div class="captioned-screen">
    <p>Ваш сайт появится в списке. Кликните по иконке «Шестеренка»</p>
    <div class="screen-wrapper">
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/cp/cp_account_setup-click-settings.png" class="leyka-instructions-screen"  />
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-zoom-screen.svg" class="zoom-screen" />
    </div>
    <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/cp/cp_account_setup-click-settings.png" class="leyka-instructions-screen-full"  />
</div>

</div>

<?php if(!empty($_SESSION['leyka-cp-notif-documents-sent'])):
    unset($_SESSION['leyka-cp-notif-documents-sent']);
?>

<div id="cp-documents-sent" class="hidden leyka-wizard-modal" style="max-width:433px">
    <h3>Ваше письмо отправлено!</h3>
    <p>На ваш администраторский e-mail придет подтверждения доступа к CloudPayments. Обычно это занимает 1-2 дня.</p>
    <p>Сейчас вы можете прервать процесс установки. А через 2 дня продолжить со следующего шага.</p>
    <input type="button" class="button button-primary button-dialog-close" value="Продолжить"/>
</div>

<?php endif?>


