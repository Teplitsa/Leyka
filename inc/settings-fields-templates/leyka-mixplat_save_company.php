<?php if( !defined('WPINC') ) die;

/** Custom field group for the MIXPLAT payments cards. */

/** @var $this Leyka_Text_Block A block for which the template is used. */?>

<div class="<?php echo esc_attr( $this->field_type );?> custom-block-mixplat-save-company mixplat-steps"> 
    <div class="step">
        <div class="block-separator"><div></div></div>
        <p>
            <?php esc_html_e("Click the &quot;Settings&quot; button on the Project", "leyka" ); ?> <a href='https://stat.mixplat.ru/projects' target='_blank'><?php esc_html_e('Settings page','leyka'); ?></a>.
        <p>
        <div class="captioned-screen">
            <div class="screen-wrapper">
                <img src="/wp-content/plugins/leyka/img/mixplat/mixplat_img06.png" class="leyka-instructions-screen" alt="">
                <img src="/wp-content/plugins/leyka/img/icon-zoom-screen.svg" class="zoom-screen" alt="">
            </div>
            <img src="/wp-content/plugins/leyka/img/mixplat/mixplat_img06.png" class="leyka-instructions-screen-full" alt="" style="display: none; position: fixed; z-index: 9991; left: 50%; top: 100px;">
        </div>
    </div>

    <div class="step">
        <div class="block-separator"><div></div></div>
        <p><?php esc_html_e("Copy the project id, widget key, and API key to the appropriate fields.", "leyka"); ?><p>
        <div class="captioned-screen">
            <div class="screen-wrapper">
                <img src="/wp-content/plugins/leyka/img/mixplat/mixplat_img07.png" class="leyka-instructions-screen" alt="">
                <img src="/wp-content/plugins/leyka/img/icon-zoom-screen.svg" class="zoom-screen" alt="">
            </div>
            <img src="/wp-content/plugins/leyka/img/mixplat/mixplat_img07.png" class="leyka-instructions-screen-full" alt="" style="display: none; position: fixed; z-index: 9991; left: 50%; top: 100px;">
        </div>
    </div>
</div>
