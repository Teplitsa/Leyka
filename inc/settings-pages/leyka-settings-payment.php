<?php if( !defined('WPINC') ) die;

$payment_class = 'stage-payment';
if ( isset( $_GET['stage'] && ! empty( $_GET['stage'] ) ) {
	$stage         = $_GET['stage'];
	$payment_class = 'stage-' . $stage;
}

?>

<div id="payment-settings-area-new" class="<?php echo esc_attr( $payment_class ); ?>">

	<div class="main-area-wrapper">

	<?php
	if ( isset( $_GET['gateway'] ) ) {
		require_once LEYKA_PLUGIN_DIR . 'inc/settings-pages/leyka-settings-payment-gateway.php';
	} else {
		require_once LEYKA_PLUGIN_DIR . 'inc/settings-pages/leyka-settings-payment-gateways-list.php';
	}
	?>

	</div>

	<div class="side-area-wrapper">
		<?php require_once LEYKA_PLUGIN_DIR . 'inc/settings-pages/leyka-settings-payment-pm-order.php'; ?>
	</div>

</div>
