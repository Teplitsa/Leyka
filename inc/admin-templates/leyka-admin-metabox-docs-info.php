<?php if( !defined('WPINC') ) die;
/** Admin Feedback metabox content */

/** @var $this Leyka_Admin_Setup */?>

<div class="metabox-content leyka-docs-info" data-thumbnail="/img/admin-boxes/docs-info.svg">

    <div class="col-left">
        <ul>

            <li><?php _e('Work start', 'leyka');?><a href="//leyka.te-st.ru/docs/what-is-leyka/" target="_blank"><img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-outer-link.svg" alt=""></a></li>

            <li><?php _e('Campaign display', 'leyka');?><a href="//leyka.te-st.ru/docs/chto-takoe-postoyannaya-kampaniya/" target="_blank"><img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-outer-link.svg" alt=""></a></li>

            <li><?php _e('Settings', 'leyka');?><a href="//leyka.te-st.ru/docs/panel-upravleniya/" target="_blank"><img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-outer-link.svg" alt=""></a></li>

            <li><?php _e('Gateways', 'leyka');?><a href="//leyka.te-st.ru/docs/integration/" target="_blank"><img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-outer-link.svg" alt=""></a></li>

            <li><?php _e('Other', 'leyka');?><a href="//leyka.te-st.ru/docs/informatsii-po-starym-versiyam/" target="_blank"><img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-outer-link.svg" alt=""></a></li>

            <li><?php _e('FAQ', 'leyka');?><a href="//leyka.te-st.ru/docs-faq/obshhie-voprosy/" target="_blank"><img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-outer-link.svg" alt=""></a></li>

        </ul>
    </div>

    <div class="col-right">
        <ul>
            <li><img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-github-logo.svg" alt=""><a href="https://github.com/Teplitsa/Leyka/" target="_blank"><?php _e('Git repository', 'leyka');?></a></li>
            <li><img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-chat-generic.svg" alt=""><a href="https://t.me/leykadev" target="_blank"><?php _e('The developers chat', 'leyka');?></a></li>
        </ul>
    </div>

</div>