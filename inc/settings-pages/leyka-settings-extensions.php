<?php if( !defined('WPINC') ) die;?>

<div id="extensions-settings-area-new" class="<?php echo empty($_GET['stage']) ? 'stage-extension' : 'stage-'.esc_attr($_GET['stage']);?>">

    <div class="main-area-wrapper">

    <?php if(isset($_GET['extension'])) {
        require_once LEYKA_PLUGIN_DIR.'inc/settings-pages/leyka-settings-extensions-extension.php';
    } else {
        require_once LEYKA_PLUGIN_DIR.'inc/settings-pages/leyka-settings-extensions-list.php';
    }?>

    </div>

</div>