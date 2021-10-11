<?php if( !defined('WPINC') ) die;


class Leyka_Engagement_Banner  {

	public function get_header() {

		$value = leyka_engb_get_option('title');

		if( empty( $value ) ) {
			return '';
		}

		if( false !== strpos( $value, '[' ) ) {
			return do_shortcode( html_entity_decode($value, ENT_QUOTES) );
		}

		$value = esc_html( strip_tags( $value ) );

		return  "<span>{$value}</span>";
	}


	public function get_text() {

		$out = [];

		$text = leyka_engb_get_option('text');

		if( !empty( $text ) ) {
			$text = html_entity_decode($text, ENT_QUOTES);
			$out[] = $text;
		}

		$selection = leyka_engb_get_option('selection');

		if( !empty( $selection ) ) {
			$out[] = "<span class='selection'>".esc_html($selection)."</span>";
		}

		return implode(' ', $out);
	}


	public function get_button_link() {

		return leyka_engb_get_option('button_link');
	}


	public function get_button_label() {
		
		return leyka_engb_get_option('button_label');
	}


	public function get_classes() {

		$classes = [];
		$classes[] = $this->_get_position_class();
		$classes[] = $this->_get_header_class();

		return implode(' ', $classes);
	}


	protected function _get_position_class() {

		$position = leyka_engb_get_option('screen_position');
		$position = empty($position) ? 'bottom' : $position;

		return 'engb-position--' . $position;
	}


	protected function _get_header_class() {

		$header = leyka_engb_get_option('title');
		
		if( has_shortcode( $header, 'leyka_engb_scale' ) ) {
			return 'engb--format-scale';
		}

		if( has_shortcode( $header, 'leyka_engb_photo' ) ) {
			return 'engb--format-photo';
		}

		return 'engb--format-text';
	}


	public function get_attributes() {

		$data = [];
		$attrs = '';

		$data['delay'] = $this->_get_delay_attribute();
		$data['remember_close'] = $this->_get_remember_close_attribute();

		foreach ($data as $key => $obj) {
			$value = is_array($obj) ? wp_json_encode( $obj ) : $obj;
			$value = esc_attr($value);

			$attrs .= "data-{$key}='{$value}' ";
		}

		return $attrs;
	}


	protected function _get_delay_attribute() {

		$delay_type = leyka_engb_get_option('delay_type');

		if( empty($delay_type) ) {
			$delay_type = 'time';
		}

		$delay_value = leyka_engb_get_option("{$delay_type}_amount");

		if(empty($delay_value)) {
			$delay_value = $delay_type == 'time' ? 30 : 50;
		}

		return [$delay_type => $delay_value,];

	}
	

	protected function _get_remember_close_attribute() {

		$remember_close = leyka_engb_get_option('remember_close');

		if( empty($remember_close) ) {
			$remember_close = 'day';
		}

		return $remember_close;
	}

}