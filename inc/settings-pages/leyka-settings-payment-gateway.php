<?php if( !defined('WPINC') ) die; // If this file is called directly, abort ?>

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