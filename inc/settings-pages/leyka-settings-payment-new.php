<?php if( !defined('WPINC') ) die; // If this file is called directly, abort ?>

<div id="payment-settings-area-new">

    <div class="main-area-wrapper">

        <?php if(isset($_GET['gateway'])) {?>

            <div class="main-area-top">
                <a href="<?php echo admin_url('admin.php?page=leyka_settings&stage=payment');?>" class="settings-return-link">К списку платёжных операторов</a>
            </div>

            <?php $gateway = leyka_get_gateway_by_id($_GET['gateway']);
            if( !$gateway ) {?>
            <p class="error">Неизвестный платёжный оператор.</p>
            <?php } else { // Gateway settings area ?>

            <div class="main-area single-gateway-settings gateway-<?php echo $_GET['gateway'];?>">
                Single gateway settings here: <?php echo $_GET['gateway'];?>
            </div>

            <?php }

        } else {?>

            <div class="main-area-top">
                Gateways list filter here
            </div>

            <div class="main-area all-gateways-settings">
                Gateways cards list here
            </div>

        <?php }?>

    </div>

    <div class="side-area-wrapper">
        <div class="side-area">
            PM order area here
        </div>
    </div>

</div><!-- #payment-settings-area-new -->