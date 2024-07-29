<?php if( !defined('WPINC') ) die;
/** Admin Feedback metabox content */

/** @var $this Leyka_Admin_Setup */?>

<div class="metabox-content leyka-docs-info" data-thumbnail="/img/admin-boxes/docs-info.svg">

    <div class="col-left">
        <ul>

            <li><?php esc_html_e('Work start', 'leyka');?><a href="//leyka.org/docs/what-is-leyka/" target="_blank"><img src="<?php echo esc_url( LEYKA_PLUGIN_BASE_URL );?>img/icon-outer-link.svg" alt=""></a></li>

            <li><?php esc_html_e('Campaign display', 'leyka');?><a href="//leyka.org/docs/chto-takoe-postoyannaya-kampaniya/" target="_blank"><img src="<?php echo esc_url( LEYKA_PLUGIN_BASE_URL );?>img/icon-outer-link.svg" alt=""></a></li>

            <li><?php esc_html_e('Settings', 'leyka');?><a href="//leyka.org/docs/panel-upravleniya/" target="_blank"><img src="<?php echo esc_url( LEYKA_PLUGIN_BASE_URL );?>img/icon-outer-link.svg" alt=""></a></li>

            <li><?php esc_html_e('Gateways', 'leyka');?><a href="//leyka.org/docs/integration/" target="_blank"><img src="<?php echo esc_url( LEYKA_PLUGIN_BASE_URL );?>img/icon-outer-link.svg" alt=""></a></li>

            <li><?php esc_html_e('Other', 'leyka');?><a href="//leyka.org/docs/informatsii-po-starym-versiyam/" target="_blank"><img src="<?php echo esc_url( LEYKA_PLUGIN_BASE_URL );?>img/icon-outer-link.svg" alt=""></a></li>

            <li><?php esc_html_e('FAQ', 'leyka');?><a href="//leyka.org/docs-faq/obshhie-voprosy/" target="_blank"><img src="<?php echo esc_url( LEYKA_PLUGIN_BASE_URL );?>img/icon-outer-link.svg" alt=""></a></li>

        </ul>
    </div>

    <div class="col-right">
        <ul>
            <li><img src="<?php echo esc_url( LEYKA_PLUGIN_BASE_URL );?>img/icon-github-logo.svg" alt=""><a href="https://github.com/Teplitsa/Leyka/" target="_blank"><?php esc_html_e('Git repository', 'leyka');?></a></li>
            <li><img src="<?php echo esc_url( LEYKA_PLUGIN_BASE_URL );?>img/icon-chat-generic.svg" alt=""><a href="https://t.me/leykadev" target="_blank"><?php esc_html_e('The developers chat', 'leyka');?></a></li>
        </ul>
    </div>

</div>