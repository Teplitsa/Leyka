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
	$before = '';
	$after = '';

	if(isset($_GET['rev']) && (int)$_GET['rev'] == 1) {
		$before = leyka_rev_campaign_top($campaign_id);
		$after = leyka_rev_campaign_bottom($campaign_id);
	}


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
<?php include(LEYKA_PLUGIN_DIR.'assets/svg/svg.svg');?>
<div class="">
	<div class="leyka-pf__card inpage-card">
		<?php $thumb_url = get_the_post_thumbnail_url($campaign_id, 'post-thumbnail'); ?>
		<?php  if($thumb_url) { //add other terms ?>
			<div class="inpage-card__thumb" style="background-image: url(<?php echo $thumb_url;?>);"></div>
		<?php  } ?>

		<div class="inpage-card_title"><?php echo get_the_title($campaign_id);?></div>

		<div class="inpage-card_scale">
			<div class="scale"><div class="progress" style="width:20%;"></div></div>
			<div class="target">50 000<span class="curr-mark">&#8381;</span></div>
			<div class="info">собрано из 250 000<span class="curr-mark">&#8381;</span></div>
		</div>

		<div class="inpage-card__note supporters">
			<strong>Поддержали:</strong> Василий Иванов, Мария Петрова, Семен Луковичный, Даниил Черный, Ольга Богуславская и <a href="#" class="history-more">еще 35 человек</a>
		</div>

		<div class="inpage-card__action">
			<button type="button">Поддержать</button>
		</div>
	</div>

	<div class="leyka-pf__form">

	<form action="#" method="post">
	<!-- step amount -->
	<div class="step step--amount">
		<div class="step__selection"></div>

		<div class="step__title">Укажите сумму пожертвования</div>

		<div class="step__fields amount">

			<div class="amount__figure">
				<input type="text" name="amount" value="500" autocomplete="off" />
				<span class="curr-mark">&#8381;</span>
			</div>

			<div class="amount__icon">
				<svg class="svg-icon pic-money-middle"><use xlink:href="#pic-money-middle" /></svg>
				<div class="amount__error">Укажите сумму от 10 до 30&nbsp;000 руб.</div>
			</div>

			<div class="amount_range">
				<input  name="amount-range" type="range" min="100" max="2500" step="200" value="500">
			</div>

		</div>

		<div class="step__action">
			<!-- hidden field to store choice ? -->
			<a href="#cards" class="remember-amount">Поддержать разово</a>
			<a href="#person" class="remember-amount monthly">
				<svg class="svg-icon icon-card"><use xlink:href="#icon-card" /></svg>Ежемесячно</a>
		</div>
	</div>

	<!-- step pm -->
	<div class="step step--cards">
		<div class="step__selection">
			<a href="#amount" class="another-step">
				<span class="remembered-amount">500</span>&nbsp;<span class="curr-mark">&#8381;</span>
			</a>
		</div>
		<div class="step__title">Выберите способ оплаты</div>

		<div class="step__fields payments">
		<!-- hidden field to store choice ? -->
		<?php
			$items = array(
				'bcard' => array('label' => 'Банковская карта', 'icon' => ''),
				'yandex' => array('label' => 'Яндекс.Деньги', 'icon' => ''),
				'sber' => array('label' => 'Сбербанк Онлайн', 'icon' => ''),
				'check' => array('label' => 'Квитанция', 'icon' => ''),
			);

			foreach($items as $key => $item) {
		?>
			<div class="p-item">
				<a href="#person" class="remember-payment" data-remember-label="<?php echo esc_attr($item['label']);?>" data-payment-type="<?php echo esc_attr($key);?>" class="p-item__link">
					<div class="p-item__icon"></div>
					<div class="p-item__label"><?php echo $item['label'];?></div>
				</a>
			</div>
		<?php } ?>
		</div>
	</div>

	<!-- step data -->
	<div class="step step--person">
		<div class="step__selection">
			<a href="#amount" class="another-step">
				<span class="remembered-amount">500</span>&nbsp;<span class="curr-mark">&#8381;</span>
				<span class="remembered-monthly">ежемесячно </span>
			</a>
			<a href="#cards" class="another-step"><span class="remembered-payment">Банковская карта</span></a>
		</div>

		<div class="step__title">Кого нам благодарить?</div>

		<div class="step__fields donor">

			<div class="donor__name">
				<label for="leyka_donor_name">Имя <span class="donor__name__error">Укажие имя</span></label>
				<input type="text" name="leyka_donor_name" value="">
			</div>

			<div class="donor__email">
				<label for="leyka_donor_email">Email <span class="donor__email__error">Укажие email в формате test@test.ru</span></label>
				<input type="email" name="leyka_donor_email" value="">
			</div>

			<div class="donor__oferta">
				<input type="checkbox" name="leyka_agree" value="1" checked="checked">
				<label for="leyka_agree">Я принимаю  <a href="#" class="oferta-trigger">договор-оферту</a></label>
			</div>

			<div class="donor-submit">
				<input type="submit" value="Продолжить">
			</div>

		</div>

		<div class="step__note">
			<p><a href="http://www.consultant.ru/document/cons_doc_LAW_162595/" target="_blank">110-ФЗ от 5 мая 2014 года</a> об анонимных интернет-платежах обязывает нас спрашивать имя и почту.</p>
		</div>

	</div>
	</form>

	<div class="oferta">
		<a href="#" class="oferta-close">Я принимаю договор-оферту</a>
		<?php echo apply_filters('leyka_terms_of_service_text', do_shortcode(leyka_options()->opt('terms_of_service_text')));?>
		<a href="#" class="oferta-close">Я принимаю договор-оферту</a>
	</div>
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