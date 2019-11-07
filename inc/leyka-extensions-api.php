<?php if( !defined('WPINC') ) die;
/** Leyka Extensions API */

abstract class Leyka_Extension extends Leyka_Singleton {

    protected static $_instance;

	protected $_id = ''; // Must be a unique string, like "support-packages"
	protected $_title = ''; // A human-readable title, like "Support packages"

	protected $_description = ''; // A human-readable description (for backoffice)
	protected $_settings_description = ''; // A human-readable description (for backoffice)

    protected $_icon = ''; // An icon URL
    protected $_user_docs_link = ''; // Extension user manual page URL
    protected $_has_wizard = false;

    protected $_is_premium = false;

    protected $_options = array();

    /**
     * @param $extension_id string
     * @return Leyka_Extension|false
     */
    public static function get_by_id($extension_id) {

        $extensions = leyka()->get_extensions();

        return empty($extensions[$extension_id]) ? false : $extensions[$extension_id];

    }

    /**
     * Extensions filter categories main source.
     * @return array
     */
    public static function get_filter_categories_list() {
        return apply_filters('leyka_extensions_filter_categories', array(
            'active' => esc_attr__('Active', 'leyka'),
            'inactive' => esc_attr__('Inactive', 'leyka'),
            'activating' => esc_attr__('Activating', 'leyka'),
            'premium' => esc_attr__('Premium', 'leyka'),
        ));
    }

    public static function get_filter_categories_ids() {
        return array_keys(self::get_filter_categories_list());
    }

    public static function get_filter_category_label($category_id) {

        $category_id = esc_attr(trim($category_id));
        $categories_list = self::get_filter_categories_list();

        return $category_id && !empty($categories_list[$category_id]) ? $categories_list[$category_id] : false;

    }

    public static function get_activation_status_list() {
        return array(
            'active' => __('Active', 'leyka'),
            'inactive' => __('Inactive', 'leyka'),
            'activating' => __('Setup is in process', 'leyka'),
        );
    }

    public static function get_activation_status_label($activation_status) {

        $activation_status_list = self::get_activation_status_list();

        return $activation_status && !empty($activation_status_list[$activation_status]) ?
            $activation_status_list[$activation_status] : false;

    }

    protected function __construct() {

        $this->_set_attributes(); // Initialize main extension attributes

        $this->_set_options_defaults(); // Set configurable options in admin area

        do_action('leyka_initialize_extension', $this, $this->_id);

        add_action("leyka_extension_{$this->_id}_save_settings", array($this, 'save_settings'));

        $this->_initialize_options();

        add_action('leyka_enqueue_scripts', array($this, 'enqueue_scripts'));

    }

    public function __get($param) {
        switch($param) {
            case 'id':
            case 'ID':
                return $this->_id;
            case 'title':
            case 'name':
            case 'label':
                return $this->_title;
            case 'description': return $this->_description;
            case 'icon':
            case 'icon_url':

                $icon = false;
                if($this->_icon) {
                    $icon = $this->_icon;
                } else if(file_exists(LEYKA_PLUGIN_DIR."extensions/{$this->_id}/img/main-icon.svg")) {
                    $icon = LEYKA_PLUGIN_BASE_URL."extensions/{$this->_id}/img/main-icon.svg";
                } else if(file_exists(LEYKA_PLUGIN_DIR."extensions/{$this->_id}/img/main-icon.png")) {
                    $icon = LEYKA_PLUGIN_BASE_URL."extensions/{$this->_id}/img/main-icon.png";
                }
                return $icon;

            case 'docs':
            case 'docs_url':
            case 'docs_href':
            case 'docs_link':
                return $this->_user_docs_link ? $this->_user_docs_link : false;
            case 'has_wizard': return !!$this->_has_wizard;
            case 'wizard_id':
            case 'wizard_suffix':
                return $this->_has_wizard ? $this->_id : false;
            case 'wizard_url':
            case 'wizard_href':
            case 'wizard_link':
                return admin_url('admin.php?page=leyka_settings_new&screen=wizard-'.$this->_id);

            case 'activation_status':
                return $this->get_activation_status();
            case 'activation_status_label':
                return self::get_activation_status_label($this->activation_status);

            case 'is_premium':
                return !!$this->_is_premium;

            default:
                return false;
        }
    }

    public function get_settings_url() {

        $wizard_id = leyka_extension_setup_wizard($this);

        if($this->get_activation_status() !== 'active' && $wizard_id) {
            $url = admin_url('/admin.php?page=leyka_settings_new&screen=wizard-'.$wizard_id);
        } else {
            $url = admin_url('/admin.php?page=leyka_extensions&extension='.$this->id);
        }

        return $url;

    }

    /** @todo Use this method + allocate_options() if we can use the options allocation system */
    public function get_options_names() {

        $option_names = array();
        foreach($this->_options as $option_name => $params) {
            $option_names[] = $option_name;
        }

        return $option_names;

    }

    /** @todo Try to merge it with Gateway class allocate_options() method. */
    public function allocate_options($options) {

        $section_index = -1;
        foreach($options as $index => $option) {
            if( !empty($option['section']) && $option['section']['name'] == $this->_id ) {
                $section_index = $index;
                break;
            }
        }

        $gateway_options_names = $this->get_options_names();
        if($section_index < 0) {
            $options[] = array('section' => array(
                'name' => $this->_id,
                'title' => $this->_title,
                'is_default_collapsed' => false,
                'options' => $gateway_options_names
            ));
        } else {
            $options[$section_index]['section']['options'] = array_unique(array_merge(
                $gateway_options_names,
                $options[$section_index]['section']['options']
            ));
        }

        return $options;

    }

    /** Register an extension in the plugin manually */
    public function add_extension() {
        leyka()->add_extension(self::get_instance());
    }

    /** Register an extension frontend scripts in the plugin */
    public function enqueue_scripts() {
    }

    abstract protected function _set_attributes(); // Attributes are constant, like id, title, etc.
    protected function _set_options_defaults() {} // Options are admin configurable parameters

    protected function _initialize_options() {

        foreach($this->_options as $option_name => $params) {
            if( !leyka_options()->option_exists($option_name) ) {
                leyka_options()->add_option($option_name, $params['type'], $params);
            }
        }

        add_filter('leyka_extension_options_allocation', array($this, 'allocate_options'), 1, 1);

    }

    /** @todo Use the method to save the extension settings manually - if we can't use the options allocation system */
    public function save_settings() {
    }

    /**
     * @return string: active inactive activating
     */
    public function get_activation_status() {

        $status = 'inactive';

        if(false /** @todo Get the "active" option value for the extension */) {
            $status = 'active';
        } else if($this->wizard_id && leyka_wizard_started($this->wizard_id)) {
            $status = 'activating';
        }

        return $status;

    }

    /** @return array A list of relevant values from the list of Leyka_Extension::_get_filter_categories_ids(). */
    public function get_filter_categories() {

        $categories = array($this->get_activation_status());

        if($this->is_premium) {
            $categories[] = 'premium';
        }

        return $categories;

    }

}