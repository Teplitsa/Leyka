<?php if( !defined('WPINC') ) die;?>

<div id="payment-settings-area-new" class="<?php echo empty($_GET['stage']) ? 'stage-payment' : 'stage-'.esc_attr($_GET['stage']);?>">

    <div class="main-area-wrapper">

    <?php if(isset($_GET['gateway'])) {
        require_once LEYKA_PLUGIN_DIR.'inc/settings-pages/leyka-settings-payment-gateway.php';
    } else {
        require_once LEYKA_PLUGIN_DIR.'inc/settings-pages/leyka-settings-payment-gateways-list.php';
    }?>

    </div>

    <div class="side-area-wrapper">
        <?php require_once LEYKA_PLUGIN_DIR.'inc/settings-pages/leyka-settings-payment-pm-order.php';?>
    </div>

</div>