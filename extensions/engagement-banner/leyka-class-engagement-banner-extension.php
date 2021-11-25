<?php if( !defined('WPINC') ) die;
/**
 * Leyka Extension: Engagement Banner
 * Version: 0.1.0
 * Author: Teplitsa of social technologies
 * Author URI: https://te-st.ru
 **/

class Leyka_Engagement_Banner_Extension extends Leyka_Extension {

    protected static $_instance;

    /** Required methods **/
    protected function _set_attributes() {

    	$this->_id = 'engagement_banner';
        $this->_title = __('Engagement Banner', 'leyka');

        $this->_description = __('Display fundrising banner on website pages, control appearance logic.', 'leyka');

        $this->_full_description = __('Display fundrising banner on website pages. Customise its appearance and behaviour through set of simple options.', 'leyka');

        $this->_settings_description = __('Setup content of a banner and  color scheme, tune appeacne logic to you need and attract more donations.', 'leyka');

        $this->_connection_description = '<p><strong>Шорткоды</strong></p><p>В заглавной части баннера (поле <em>Заголовок</em>) можно использовать следующие шорткоды:</p><p><code>[leyka_engb_scale id="campaign_id"]</code><br>прогрессбар сбора по кампании</p><p><code>[leyka_engb_photo img="media_lib_id" name="Иван Чернов" role="главный редактор"]</code><br>фото с подписью (2 уровня) - например фото человека с указанием имени и должности.</p><p>Фото должно быть загружено в медиа-библиотеку и в параметрах шорткода указывается его ID.</p>';

        $this->_user_docs_link = false;
        $this->_has_wizard = false;
        $this->_has_color_options = true;

    }


	protected function _set_options_defaults() {
 
        require_once(self::get_base_path().'/inc/config-options.php');

		$this->_options = apply_filters('leyka_'.$this->_id.'_extension_options', leyka_engb_options($this->_id));

	}

    protected function _initialize_always() {

        add_action('admin_enqueue_scripts', [$this, 'load_admin_cssjs']);

        add_action('leyka_render_custom_engb_multiselect', [$this, 'render_custom_multiselect'], 10, 2);

        // Process custom multiselect options:
        $custom_options = $this->_get_multiselect_fields();

        if( !empty($custom_options) ) {
            foreach ($custom_options as $option_id) {
                add_action("leyka_save_custom_option-$option_id", [$this, 'save_custom_multiselect']);
            }
        }

    }

    /** Support options with custom_multiselect type **/
    public function render_custom_multiselect($option_id, $option_info) {

        $option_info = $this->_get_multiselect_field_config($option_id);
        $items_list = $option_info['list_entries'];

        $field_key = "leyka_$option_id";

        $selection = get_option($field_key);
        $selection = is_array($selection) ? $selection : (array)$selection;?>

    <div id="<?php echo esc_attr($option_id);?>" class="settings-block option-block type-engb-multiselect">

        <div id="<?php echo esc_attr($option_id);?>-wrapper" class="engb-multiselect-field-wrapper">

            <input type="hidden" name="<?php echo esc_attr($field_key);?>_submission" value="1">
            <label for="<?php echo esc_attr($field_key);?>">

                <span class="field-component title">
                    <span class="text"><?php echo esc_html($option_info['title']);?></span>
                 </span>

                <span class="field-component field">
                    <select class="engb-multiselect" name="<?php echo esc_attr($field_key);?>[]" multiple="multiple">
                        <?php foreach($items_list as $key => $label) {?>
                            <option value="<?php echo esc_attr($key);?>" <?php echo in_array($key, $selection) ? "selected='selected'" : '';?>>
                                <?php echo esc_html($label);?>
                            </option>
                        <?php } ?>
                    </select>
                </span>

            </label>

        </div>

        <div class="field-errors"></div>

    </div>

    <?php
    }


    protected function _get_multiselect_field_config($option_id) {

        $config = [];

        if( !$this->_options ) {
            return $config;
        }

        foreach($this->_options as $i => $section) {
            if(isset($section['section']['options'][$option_id])) {

                $config = $section['section']['options'][$option_id];
                break;

            }
        }
            
        return $config;

    }

    public function save_custom_multiselect() {

        $custom_options = $this->_get_multiselect_fields();

        if(empty($custom_options)) {
            return;
        }

        foreach($custom_options as $option_id) {
            
            // our submission
            $test_key = "leyka_{$option_id}_submission"; 

            if( !isset($_POST[$test_key]) ) {
                continue;
            }

            if( (int)$_POST[$test_key] !== 1 ) {
                continue;
            }

            $config = $this->_get_multiselect_field_config($option_id);
            $callback = (isset($config['update_callback'])) ? $config['update_callback'] : '';
 
            if( !empty($callback) && is_callable($callback) ) {
                call_user_func($callback);
            }

        }

    }


    protected function _get_multiselect_fields() {

        $fields = [];

        if(empty($this->_options)) {
            return $fields;
        }

        foreach($this->_options as $section) {

            if( !isset( $section['section']['options'] ) ) {
                continue;
            }

            foreach($section['section']['options'] as $key => $config) {
                if(isset($config['type']) && $config['type'] == 'custom_engb_multiselect') {
                    $fields[] = $key;
                }
            }

        }

        return $fields;

    }

    protected function _initialize_active() {

        $this->_load_files();

        add_action('wp_enqueue_scripts', [$this, 'load_cssjs']);
        add_action('wp_footer', [$this, 'display_banner']);

        add_shortcode('leyka_engb_scale', [$this, 'shortcode_scale_screen']);
        add_shortcode('leyka_engb_photo', [$this, 'shortcode_photo_screen']);

    }

//    public static function get_base_path() {
//        return __DIR__;
//    }
//    public static function get_base_url() {
//        return LEYKA_PLUGIN_BASE_URL.'extensions/engagement-banner';
//    }

	protected function _load_files() {

		require_once(self::get_base_path().'/inc/class-controller.php');
		require_once(self::get_base_path().'/inc/class-banner.php');

	}

	public function load_cssjs() {

		wp_enqueue_style(
            $this->_id.'-front',
            self::get_base_url().'/assets/css/engb.css',
            [],
            defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? uniqid() : null
        );

        wp_add_inline_style($this->_id.'-front', $this->_build_colors_css());

        wp_enqueue_script(
            $this->_id.'-front',
            self::get_base_url().'/assets/js/engb.js',
            ['jquery'],
            defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? uniqid() : null
        );

	}

    public function load_admin_cssjs() {

        if( !Leyka_Extension::is_admin_settings_page($this->_id) ) { // Extension CSS & JS is only for admin settings page
            return;
        }

        wp_enqueue_style(
            $this->_id.'-select2',
            self::get_base_url().'/assets/css/select2.min.css'
        );

        wp_enqueue_style(
            $this->_id.'-admin',
            self::get_base_url().'/assets/css/engb-admin.css',
            [$this->_id.'-select2'],
            defined('WP_DEBUG') && WP_DEBUG ? uniqid() : null
        );

        wp_enqueue_script(
            $this->_id.'-select2',
            self::get_base_url().'/assets/js/select2.min.js',
            ['jquery',],
            null, 
            true
        );

        wp_enqueue_script(
            $this->_id.'-admin',
            self::get_base_url().'/assets/js/engb-admin.js',
            ['jquery', $this->_id.'-select2',],
            defined('WP_DEBUG') && WP_DEBUG ? uniqid() : null,
            true
        );

        wp_localize_script($this->_id.'-admin', 'engb', [
            'placeholder' => __('Select user role', 'leyka'),
        ]);

    }

    protected function _build_colors_css() {

        $button_bg = $this->get_option('main_color');
        $button_text = $this->get_option('caption_color');
        $body_bg = $this->get_option('background_color');
        $body_text = $this->get_option('text_color');

        return "
        :root {
            --engb-color-button-bg: {$button_bg};
            --engb-color-button-text: {$button_text};
            --engb-color-body-bg: {$body_bg};
            --engb-color-body-text: {$body_text};
        }";

    }

    /** Main action */
	public function display_banner() {

		try {

			$controller = new Leyka_Engagement_Banner_Controller();
			$controller->display();

		} catch(Exception $ex) {

			$err = $ex->getMessage();

			if(defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) {
				echo $err;
			}

			error_log($err);

		}

	}

	/** Shortcodes **/
    public function shortcode_scale_screen($atts) {

        $atts = shortcode_atts([
            'id' => 0
        ], $atts);

        $campaign = get_post($atts['id']);

        if( !class_exists('Leyka_Campaign_Management') ) {
            return '';
        }

        if( !$campaign || $campaign->post_type != Leyka_Campaign_Management::$post_type ) { 
            return '';
        }

        $campaign = leyka_get_validated_campaign($campaign);

        if( !$campaign ) {
            return '';
        }

        // progress scale 
        $target = (int)$campaign->target;
        $funded = (int)$campaign->total_funded;
        
        if( !$target ) {
            return '';
        }

        $percentage = ceil(($funded/$target)*100);

        $template = self::get_base_path() . '/inc/template-scale.php';
        $template = apply_filters( 'leyka_engb_scale_template', $template );

        $out = '';

        if(file_exists($template)) {

            ob_start();

            $scale = [];
            $scale['currency'] = leyka_get_currency_label('rub');
            $scale['percentage'] = $percentage > 100 ? 100 : $percentage;
            $scale['delta'] = ($funded < $target) ? $target - $funded : 0;
            $scale['target'] = number_format($target, 0, '.', ' ');

            include $template;

            $out = ob_get_contents();

            ob_end_clean();

        }

        return $out;

    }


    public function shortcode_photo_screen( $atts, $content = null ) {

        $photo = shortcode_atts([
            'img' => 0,
            'name' => '',
            'role' => ''
        ], $atts);

        if(empty($photo['name']) || (int)$photo['img'] === 0) {
            return '';
        }

        $image = wp_get_attachment_image($photo['img'], 'thumbnail');

        if( !$image ) {
            return '';
        }

        $template = apply_filters('leyka_engb_photo_template', self::get_base_path().'/inc/template-photo.php');
        $out = '';

        if(file_exists($template)) {

            ob_start();

            $photo['image'] = $image;
            include $template;

            $out = ob_get_contents();

            ob_end_clean();

        }

        return $out;

    }

    /** Universal options access **/
    public function get_option($key) {
        return leyka_options()->opt("{$this->_id}_{$key}");
    }

}


function leyka_engb_get_option($key) {
	return Leyka_Engagement_Banner_Extension::get_instance()->get_option($key);
}

function leyka_add_extension_engagement_banner() {
    leyka()->add_extension(Leyka_Engagement_Banner_Extension::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_extension_engagement_banner');