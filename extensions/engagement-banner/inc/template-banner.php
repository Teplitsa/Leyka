<?php if( !defined('WPINC') ) die;

/** @var $banner array */ ?>

<div class="engb <?php echo esc_attr( $banner['classes'] );?>" <?php echo $banner['attributes'];?>>
<div class="engb-drawer">
	<div class="engb-close"><a href="#" class="engb-close-trigger"><svg viewBox="0 0 34 34" xmlns="http://www.w3.org/2000/svg"><path fill-rule="nonzero" d="M34.029 29.779l-12.75-12.75 12.75-12.75-4.25-4.25-12.75 12.75L4.279.029.03 4.279l12.75 12.75-12.75 12.75 4.25 4.25 12.75-12.75 12.75 12.75z"/></svg></a></div>
	<div class="engb-row">
		<div class="engb-title"><?php echo $banner['title'];?></div>
		<div class="engb-text">
			<?php echo apply_filters( 'the_content', $banner['text'] );?>
		</div>
		<div class="engb-action">
			<a href="<?php echo esc_url($banner['button_link']);?>" <?php echo $banner['button_target'];?> class='engb-button'><span><?php echo esc_html($banner['button_label']);?></span></a>
		</div>
	</div>
</div></div>