<?php if( !defined('WPINC') ) die;

/**
 ** Temp functions to implement revolution development
 **
 **/

//add form JS/CSS to campaign page
add_filter('the_content', 'leyka_rev_campaign_page');
function leyka_rev_campaign_page($content) {

	if(!is_singular('leyka_campaign'))
		return $content;

	$campaign_id = get_queried_object_id();

	$before = leyka_rev_campaign_top($campaign_id);
	$after = leyka_rev_campaign_bottom($campaign_id);


	return $before.$content.$after;
}

add_action('wp_enqueue_scripts', 'leyka_rev_cssjs');
function leyka_rev_cssjs() {
	//for dev just load them everywhere

	wp_enqueue_style(
		'leyka-rev',
		LEYKA_PLUGIN_BASE_URL.'assets/css/public.css',
		array(),
		LEYKA_VERSION
	);

	wp_enqueue_script(
        'leyka-rev',
        LEYKA_PLUGIN_BASE_URL.'assets/js/public.js',
		array('jquery'),
        LEYKA_VERSION,
        true
    );

	$js_data = apply_filters('leyka_js_localized_strings', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));

    wp_localize_script('leyka-rev', 'leykarev', $js_data);
}


/** Templates **/
function leyka_rev_campaign_top($campaign_id) {


?>
<div id="leyka-pf-<?php echo $campaign_id;?>" class="leyka-pf">
<div class="">
	<div class="leyka-pf__card">
		<div class="scale"><div class="progress"></div></div>
		<div class="">50 000<span>&#8381;</span></div>
		<div class="">собрано из 250 000<span>&#8381;</span></div>
		<div class=""><strong>Поддержали:</strong> Василий Иванов, Мария Петрова, Семен Луковичный, Даниил Черный, Ольга Богуславская и еще 35 человек</div>
		<div class="">
			<button type="button">Поддержать</button>
		</div>
	</div>

	<div class="leyka-pf__form">

	<form action="#" method="post">
	<!-- step amount -->
	<div class="step-amount">
		<div class="step__form">
			<div class="step__selection"></div>

			<div class="step__title">Укажите сумму пожертвования</div>

			<div class="step__fields amount__fields">

				<div class="amoun__figure">
					<input type="text" name="amount" value="500"/>
					<span>&#8381;</span>
				</div>

				<div class="amoun__icon">
					money icon
				</div>

				<div class="amount_range">
					<input  name="amount-range" type="range" min="100" max="2500" step="200" value="500">
				</div>

			</div>

			<div class="amount__error">Укажите сумму от 10 до 30&nbsp;000 руб.</div>

			<div class="step__action">
				<a href="#cards" class="remember-amount">Разово</a><a href="#cards" class="remember-amount monthly">Ежемесячно</a>
			</div>

		</div>
	</div>

	<!-- step pm -->
	<div class="step-two">

	</div>

	<!-- step data -->
	<div class="step-three">

	</div>
	</form>

	</div>
</div><!-- columnt -->
</div>
<?php
	$out = ob_get_contents();
	ob_end_clean();

	return $out;
}

function leyka_rev_campaign_bottom($campaign_id) {

	ob_start();
?>
<div rel="leyka-pf-<?php echo $campaign_id;?>" class="leyka-pf-bottom">
	<div class="">Сделайте пожертвование</div>
	<div class="">
		<input type="text" value="500" name="leyka_temp_amount">
		<span>&#8381;</span>
	</div>
	<div class="">
		<button type="button">Поддержать</button>
	</div>
	<div class="">
		<strong>Поддержали:</strong> Василий Иванов, Мария Петрова, Семен Луковичный, Даниил Черный, Ольга Богуславская и еще 35 человек
	</div>
</div>
<?php

	$out = ob_get_contents();
	ob_end_clean();

	return $out;
}