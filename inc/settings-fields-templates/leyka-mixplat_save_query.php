<?php if( !defined('WPINC') ) die;
/** Custom field group for the Yandex Kassa payments cards. */
/** @var $this Leyka_Text_Block A block for which the template is used. */?>

<div class="<?php echo esc_attr( $this->field_type );?> custom-block-mixplat_save_query mixplat-steps">    

    <div class="step">
        <div class="block-separator"><div></div></div>
        <?php
            _e("<p>In the project settings, in the «Receiving payment statuses» field, you need to enter the URL where the payment status handler script is located on your site.</p><p>Click the «Edit» button and in the «script handler» field, copy and paste the following address:</p>", "leyka");
        ?>
        <span class="info2copy leyka-wizard-copy2clipboard"><?php echo site_url('/leyka/service/mixplat/');?></span>
        <div class="captioned-screen">
            <div class="screen-wrapper">
                <img src="/wp-content/plugins/leyka/img/mixplat_img8.png" class="leyka-instructions-screen" alt="">
                <img src="/wp-content/plugins/leyka/img/icon-zoom-screen.svg" class="zoom-screen" alt="">
            </div>
            <img src="/wp-content/plugins/leyka/img/mixplat_img8.png" class="leyka-instructions-screen-full" alt="" style="display: none; position: fixed; z-index: 0; left: 50%; top: 100px;">
        </div>
    </div>

    <div class="step">
        <div class="block-separator"><div></div></div>
        <p>
            <?php
                _e("Check the correctness of the address and click «Save».", "leyka");
            ?>
        </p>
        <div class="captioned-screen">
            <div class="screen-wrapper">
                <img src="/wp-content/plugins/leyka/img/mixplat_img9.png" class="leyka-instructions-screen" alt="">
                <img src="/wp-content/plugins/leyka/img/icon-zoom-screen.svg" class="zoom-screen" alt="">
            </div>
            <img src="/wp-content/plugins/leyka/img/mixplat_img9.png" class="leyka-instructions-screen-full" alt="" style="display: none; position: fixed; z-index: 0; left: 50%; top: 100px;">
        </div>
    </div>
</div>