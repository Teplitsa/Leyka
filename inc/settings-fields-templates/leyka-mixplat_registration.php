<?php if( !defined('WPINC') ) die;
/** Custom field group for the Yandex Kassa payments cards. */
/** @var $this Leyka_Text_Block A block for which the template is used. */?>

<div class="<?php echo esc_attr( $this->field_type );?> custom-block-mixplat_registration">
    <div class="captioned-screen">
        <div class="screen-wrapper">
            <img src="/wp-content/plugins/leyka/img/mixplat_img1.png" class="leyka-instructions-screen" alt="">
            <img src="/wp-content/plugins/leyka/img/icon-zoom-screen.svg" class="zoom-screen" alt="">
        </div>
        <img src="/wp-content/plugins/leyka/img/mixplat_img1.png" class="leyka-instructions-screen-full" alt="" style="display: none; position: fixed; z-index: 0; left: 50%; top: 100px;">
    </div>
</div>