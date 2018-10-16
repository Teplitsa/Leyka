<?php if( !defined('WPINC') ) die; // If this file is called directly, abort ?>

<div id="payment-settings-area-new">

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

</div><!-- #payment-settings-area-new -->