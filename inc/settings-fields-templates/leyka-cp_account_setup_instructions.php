<?php if( !defined('WPINC') ) die;

/** Custom field group for the CP payments cards. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */?>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="captioned-screen">

        <p><?php esc_html_e('If you logged in successfully, you will see a page like this:', 'leyka');?></p>
        <div class="screen-wrapper">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/cp/cp_account_setup-after-login.png" class="leyka-instructions-screen" alt="">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-zoom-screen.svg" class="zoom-screen" alt="">
        </div>
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/cp/cp_account_setup-after-login.png" class="leyka-instructions-screen-full" alt="">

    </div>

    <div class="captioned-screen">

        <p><?php esc_html_e('Click the "Sites" in the left menu', 'leyka');?></p>

        <div class="screen-wrapper">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/cp/cp_account_setup-choose-sites.png" class="leyka-instructions-screen" alt="">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-zoom-screen.svg" class="zoom-screen" alt="">
        </div>
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/cp/cp_account_setup-choose-sites.png" class="leyka-instructions-screen-full" alt="">

    </div>

    <div class="captioned-screen">

        <p><?php esc_html_e('Click the "Add site" line', 'leyka');?></p>
        <div class="screen-wrapper">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/cp/cp_account_setup-add-site.png" class="leyka-instructions-screen" alt="">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-zoom-screen.svg" class="zoom-screen" alt="">
        </div>
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/cp/cp_account_setup-add-site.png" class="leyka-instructions-screen-full" alt="">

    </div>

    <div class="captioned-screen">

        <p><?php esc_html_e('Add your website title and root URL', 'leyka');?></p>

        <div class="screen-wrapper">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/cp/cp_account_setup-set-site-name.png" class="leyka-instructions-screen" alt="">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-zoom-screen.svg" class="zoom-screen" alt="">
        </div>
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/cp/cp_account_setup-set-site-name.png" class="leyka-instructions-screen-full" alt="">

    </div>

    <div class="captioned-screen">

        <p><?php esc_html_e('Your website now appeared in the list. Click the "gear" icon', 'leyka');?></p>

        <div class="screen-wrapper">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/cp/cp_account_setup-click-settings.png" class="leyka-instructions-screen" alt="">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-zoom-screen.svg" class="zoom-screen" alt="">
        </div>
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/cp/cp_account_setup-click-settings.png" class="leyka-instructions-screen-full" alt="">

    </div>

</div>

<?php if( !empty($_SESSION['leyka-cp-notif-documents-sent']) ) {

    unset($_SESSION['leyka-cp-notif-documents-sent']);?>

<div id="cp-documents-sent" class="hidden leyka-wizard-modal" style="max-width:433px">

    <h3><?php esc_html_e('Your email sent!', 'leyka');?></h3>

    <p><?php esc_html_e("Your will receive a confirmation for your CloudPayments access on your administrator's email. It takes 1-2 days normally.", 'leyka');?></p>
    <p><?php esc_html_e('You may pause the setup process now, and in 2 days continue from the next step.', 'leyka');?></p>
    <input type="button" class="button button-primary button-dialog-close" value="<?php esc_attr_e('Continue', 'leyka');?>">

</div>

<?php }?>