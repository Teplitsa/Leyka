<?php if( !defined('WPINC') ) die;

$extensions_class = 'stage-extension';
if ( isset( $_GET['stage'] ) && ! empty( $_GET['stage'] ) ) {
	$stage            = $_GET['stage'];
	$extensions_class = 'stage-' . $stage;
}

?>

<div id="extensions-settings-area-new" class="<?php echo esc_attr( $extensions_class ); ?>">

	<div class="main-area-wrapper">

	<?php
	if ( isset( $_GET['extension'] ) ) {
		require_once LEYKA_PLUGIN_DIR . 'inc/settings-pages/leyka-settings-extensions-extension.php';
	} else {
		require_once LEYKA_PLUGIN_DIR . 'inc/settings-pages/leyka-settings-extensions-list.php';
	}
	?>

	</div>

</div>
