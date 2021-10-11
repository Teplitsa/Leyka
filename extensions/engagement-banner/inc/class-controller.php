<?php if( !defined('WPINC') ) die;

class Leyka_Engagement_Banner_Controller  {

	/** @var Leyka_Engagement_Banner */
	protected $_banner;


	public function display() {

		if( !$this->_can_display() ) {
			return;
		}

		$this->_set_banner();

		$this->_load_template();

	}

	protected function _can_display() {

		$can_display = apply_filters( 'leyka_engb__can_display_rules', null );

		if( $can_display !== null ) {
			return $can_display;
		}

		if( is_admin() ) { // only at frontend
			return false;
		}

		if( !$this->_common_rule() ) {
			return false;
		}

		// remember 
		if( !$this->_remember_rule() ) {
			return false;
		}

		// user 
		if( !$this->_user_rule() ) {
			return false;
		}

		if( !$this->_donor_rule() ) {
			return false;
		}

		// pages
		if( !$this->_pages_rule() ) {
			return false;
		}

		if( !$this->_exclude_rule() ) {
			return false;
		}

		return true;

	}

	protected function _common_rule() {

		// common exclusions
		if(is_404() || is_search() || is_singular('leyka_campaign')) {
			return false;
		}

		// leyka service pages 
		$exclude = [];

		$thanks = (int)get_option('leyka_success_page');
		if($thanks > 0) {
			$exclude[] = $thanks;
		}

		$error = (int)get_option('leyka_failure_page');
		if($error > 0) {
			$exclude[] = $error;
		}

		$privacy = (int)get_option('leyka_pd_terms_page');
		if($privacy > 0) {
			$exclude[] = $privacy;
		}

		$tos = (int)get_option('leyka_terms_of_service_page');
		if($tos) {
			$exclude[] = $tos;
		}

		if(is_page($exclude)) {
			return false;
		}

		return true;

	}

	protected function _remember_rule() {

		if(leyka_engb_get_option('remember_close') == 'none') {
			return true; // should not remember
		}

		if(isset($_COOKIE["leyka_engb_close"]) && $_COOKIE["leyka_engb_close"] = 1) {
			return false;
		}

		return true;

	}

	protected function _user_rule() {

		if( !is_user_logged_in() ) {
			return true; // don't know about roles 
		}

		$hide_from_roles = leyka_engb_get_option('hide_from_roles'); 

		if( empty($hide_from_roles) ) {
			return true; // no limit 
		}

		if( in_array('logged_in', $hide_from_roles) ) {
			return false; // hide from all logged-ins
		}

		$check = array_intersect((array)wp_get_current_user()->roles, $hide_from_roles);

		if(count($check)) {
			return false; // user have some matched roles
		}

		return true;

	}


	protected function _donor_rule() {

		$hide_type = leyka_engb_get_option('hide_on_donation');

		if( $hide_type == 'none' ) {
			return true; // no restriction
		}

		$donation_id = isset($_COOKIE["leyka_donation_id"]) ? (int)$_COOKIE["leyka_donation_id"] : 0;

		if( !$donation_id ) {
			return true; // no donation info found
		}

		if( $hide_type == 'forever' ) {
			return false; // date does not matter
		}

		$donation = Leyka_Donations::get_instance()->get_donation($donation_id);
		if( !$donation ) {
			return true; // invalid donation info
		}

		$difference_limit = $hide_type == 'day' ? 24*60*60 : 7*24*60*60;

		if((strtotime('now') - $donation->date_timestamp) <= $difference_limit) {

			error_log('block by donation time');
			return false; // donation in time limit

		}

		return true;

	}

	protected function _pages_rule() {

		$onpages = leyka_engb_get_option('show_on_pages');
		$onhome = leyka_engb_get_option('show_on_home');
		
		if($onpages == 'onlyhome' && !is_front_page()) {
			return false;
		}

		if($onhome == 'hide' && is_front_page()) {
			return false;
		}

		if($onpages == 'singles' && !is_singular()) {
			return false;
		}

		return true;

	}

	protected function _exclude_rule() {

		$ids = [];
		$tids = [];
		$pts = [];
		$taxes = [];

		$raw_rules = leyka_engb_get_option('exclude_rules');

		if( !$raw_rules ) {
			return true;
		}

		foreach(explode(PHP_EOL, $raw_rules) as $i => $rule) {

			$rule_parts = array_map('trim', explode(':', $rule));

			if( !is_array($rule_parts) || 2 !== count($rule_parts) ) {
				continue;
			}

			if($rule_parts[0] == 'id') {
				$ids[] = (int)$rule_parts[1];
			} else if($rule_parts[0] == 'pt') {
				$pts[] = $rule_parts[1];
			} else if($rule_parts[0] == 'tid') {
				$tids[] = $rule_parts[1];
			} else if($rule_parts[0] == 'tax') {
				$taxes[] = $rule_parts[1];
			}

		}

		$ids = $ids ? array_map('absint', $ids) : $ids;
		$tids = $tids ? array_map('absint', $tids) : $tids;

		if($ids && is_singular() && in_array(get_the_ID(), $ids)) {
			return false;
		}
		if($pts && is_singular($pts)) {
			return false;
		}
		if( $tids && (is_tax('', $tids) || is_tag($tids) || is_category()) ) {
			return false;
		}
		if($taxes && is_tax($taxes)) {
			return false;
		}
		if($taxes && in_array('post_tag', $taxes) && is_tag()) {
			return false;
		}
		if($taxes && in_array('category', $taxes) && is_category()) {
			return false;
		}

		return true;

	}

	protected function _set_banner() {
		$this->_banner = apply_filters('leyka_engb_banner_object', new Leyka_Engagement_Banner());
	}

	protected function _get_banner_data() {

		if($this->_banner === null) {
			$this->_set_banner();
		}

		// prepare data for template 
		$data = [
			'title' 		=> $this->_banner->get_header(),
			'text' 			=> $this->_banner->get_text(),
			'button_link' 	=> $this->_banner->get_button_link(),
			'button_label' 	=> $this->_banner->get_button_label(),
			'button_target' => '',
			'classes' 		=> $this->_banner->get_classes(),
			'attributes' 	=> $this->_banner->get_attributes()
		];

		if(mb_strpos($data['button_link'], home_url()) === false) {
			$data['button_target'] = 'target="_blank" rel="noopener"';
		}

		return apply_filters('leyka_engb_banner_data', $data, $this->_banner);

	}

	protected function _load_template() {

		$template = apply_filters(
            'leyka_engb_banner_template',
            Leyka_Engagement_Banner_Extension::get_base_path().'/inc/template-banner.php'
        );

		if(file_exists($template)) {

			$banner = $this->_get_banner_data();

			include $template;

		}
	}

}