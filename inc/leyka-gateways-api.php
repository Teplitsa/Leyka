<?php
/**
 * Leyka Gateways API
 **/

/**
 * Functions to register and deregister GW
 **/
function leyka_add_gateway($class_name){
    leyka()->add_gateway($class_name);
}

function leyka_remove_gateway($class_name){
    leyka()->remove_gateway($class_name);
}

function leyka_get_gateways(){
	return leyka()->get_gateways();
}

/**
 * @param mixed $activity True to select only active PMs, false for only non-active ones,
 * NULL for both types altogether.
 * @param $currency mixed
 * @return array
 */
function leyka_get_pm_list($activity = null, $currency = false) {

    $pm_list = array();
    foreach(leyka()->get_gateways() as $gateway) {
        /** @var Leyka_Gateway $gateway */
        $pm_list = array_merge($pm_list, $gateway->get_payment_methods($activity, $currency));
    }

    return $pm_list;
}

/**
 * @param $pm_id string
 * @param $is_full_id boolean
 * @return Leyka_Payment_Method Or false, if no PM found.
 */
function leyka_get_pm_by_id($pm_id, $is_full_id = false) {

    $pm = false;
    if($is_full_id) {
		
		$id = explode('-', $pm_id);
        $gateway = leyka_get_gateway_by_id(reset($id)); //otherwise error in PHP 5.4.0
        if( !$gateway )
            return false;

        $pm = $gateway->get_payment_method_by_id(end($id));

    } else {

        foreach(leyka()->get_gateways() as $gateway) {
            /** @var Leyka_Gateway $gateway */
            $pm = $gateway->get_payment_method_by_id($pm_id);
            if($pm)
                break;
        }
    }

    return $pm;
}

/**
 * @param $gateway_id string
 * @return Leyka_Gateway
 */
function leyka_get_gateway_by_id($gateway_id) {
    foreach(leyka()->get_gateways() as $gateway) {
        /** @var Leyka_Gateway $gateway */
        if($gateway->id == $gateway_id)
            return $gateway;
    }
}

abstract class Leyka_Gateway {
	
    /** @var $_instance Leyka_Gateway Gateway is always a singleton */
    protected static $_instance;

	protected $_id = ''; // A unique string, as "quittance", "yandex" or "chronopay"
	protected $_title = ''; // A human-readable title of gateway, a "Bank quittances" or "Yandex.money"
    protected $_payment_methods = array(); // Supported PMs array
    protected $_options = array(); // Gateway configs

    protected function __construct() {

        // All methods must be redefined in a Gateway subclass to customize it's behavior:

        $this->_set_gateway_attributes(); // Create main Gateway's attributes

        $this->_set_options_defaults(); // Svaret an admin area's configurable options  

        $this->_set_gateway_pm_list(); // Initialize or restore Gateway's PMs list and all their options

        // Set a Gateway class method to process a service calls from gateway:
        add_action('leyka_service_call-'.$this->_id, array($this, '_handle_service_calls'));
    }

    final protected function __clone() {}

    public final static function get_instance() {

        if(null == static::$_instance) {

            static::$_instance = new static();

//            if( !empty($_GET['gateway_refresh_options']) ) {
//
//                foreach(static::$_instance->_get_options_names() as $option_name) {
//                    leyka_options()->delete_option($option_name);
//                }
//            }

            static::$_instance->_initialize_options();

            add_action('leyka_payment_form_submission', array(static::$_instance, 'process_form'), 10, 4);
            add_action('leyka_log_donation', array(static::$_instance, 'log_gateway_fields'));

            add_filter('leyka_submission_redirect_url-'.static::$_instance->id, array(static::$_instance, 'submission_redirect_url'), 10, 2);
            add_filter('leyka_submission_form_data-'.static::$_instance->id, array(static::$_instance, 'submission_form_data'), 10, 3);
        }

        return static::$_instance;
    }

    public function __get($param) {
        if($param == 'id')
            return $this->_id;
        elseif($param == 'title' || $param == 'name' || $param == 'label')
            return $this->_title;
    }

    public function get_options_names() {

        $option_names = array();
        foreach($this->_options as $option_name => $params) {
            $option_names[] = $option_name;
        }

        return $option_names;
    }

    /** Allocate gateway options, if needed */
    public function allocate_gateway_options($options) {

        $gateway_section_index = -1;
        foreach($options as $index => $option) {
            if( !empty($option['section']) && $option['section']['name'] == $this->_id ) {
                $gateway_section_index = $index;
                break;
            }
        }

        $gateway_options_names = $this->get_options_names();
        if($gateway_section_index < 0)
            $options[] = array('section' => array(
                'name' => $this->_id,
                'title' => $this->_title,
                'is_default_collapsed' => false,
                'options' => $gateway_options_names
            ));
        else
            $options[$gateway_section_index]['section']['options'] = array_unique(array_merge(
                $gateway_options_names,
                $options[$gateway_section_index]['section']['options']
            ));

        return $options;
    }

    abstract protected function _set_gateway_attributes(); // Attributes are constant, like id, title, etc. 
    abstract protected function _set_options_defaults(); // Options are admin configurable parameters
    abstract protected function _initialize_pm_list(); // PM list is specific for each Gateway

    // Handler for Gateway's service calls (activate the donations, etc.):
    abstract public function _handle_service_calls($call_type = '');

    // Handler to use Gateway's responses in Leyka UI:
    abstract public function get_gateway_response_formatted(Leyka_Donation $donation);

    protected function _get_gateway_pm_list($pm_id = false) {

        return $pm_id ? array_keys($this->_payment_methods, $pm_id) : array_keys($this->_payment_methods);
    }

    protected function _set_gateway_pm_list() {

        $this->_initialize_pm_list();
        $this->_initialize_pm_options();
        $this->_set_pm_activity();
    }

    protected function _set_pm_activity() {

        $all_active_pm_list = leyka_options()->opt('pm_available');
        $own_active_pm_list = array();
        for($i=0; $i<count($all_active_pm_list); $i++) {
            
            if(stristr($all_active_pm_list[$i], $this->_id.'-') !== false)
                $own_active_pm_list[] = str_replace($this->_id.'-', '', $all_active_pm_list[$i]);
        }

        foreach($this->_payment_methods as $pm_id => $pm) {

            /** @var $pm Leyka_Payment_Method */
            if(in_array($pm_id, $own_active_pm_list) && !$pm->is_active)
                $pm->set_activity(true);
            else if( !in_array($pm_id, $own_active_pm_list) && $pm->is_active )
                $pm->set_activity(false);
        }
    }

    protected function _initialize_pm_options() {

//        if(empty($_GET['pm_refresh_options'])) {

        foreach($this->_payment_methods as $pm) {

            /** @var $pm Leyka_Payment_Method */
            $pm->initialize_pm_options();
            $this->_payment_methods[$pm->id] = $pm;
        }

//        } else {
//
//            foreach(get_option('leyka_'.$this->_id.'_payment_methods', array()) as $pm) {
//
//                /** @var $pm Leyka_Payment_Method */
//                foreach($pm->get_pm_options_names() as $option_name) {
//                    leyka_options()->delete_option($option_name);
//                }
//            }
//
//            delete_option('leyka_'.$this->_id.'_payment_methods');
//        }
    }

    protected function _initialize_options() {

        foreach($this->_options as $option_name => $params) {

            if( !leyka_options()->option_exists($option_name) )
                leyka_options()->add_option($option_name, $params['type'], $params);
        }

        add_filter('leyka_payment_options_allocation', array($this, 'allocate_gateway_options'), 1, 1);

//        global $wp_filter;
//        echo '<pre>' . print_r($wp_filter['leyka_payment_options_allocation'], TRUE) . '</pre>';
    }

    abstract public function process_form($gateway_id, $pm_id, $donation_id, $form_data);
    
    abstract public function submission_redirect_url($current_url, $pm_id);

    abstract public function submission_form_data($form_data_vars, $pm_id, $donation_id);
    
    abstract public function log_gateway_fields($donation_id);

//    abstract protected function _initialize_options();

    /**
     * @param mixed $activity True to select only active PMs, false for only non-active ones,
     * NULL for both types altogether.
     * @param mixed $currency
     * @return array Of Leyka_Payment_Method objects.
     */
    public function get_payment_methods($activity = null, $currency = false) {
        $pm_list = array();
        foreach($this->_payment_methods as $pm_name => $pm) {

            /** @var $pm Leyka_Payment_Method */
            if( (($activity || $activity === null) && $pm->is_active) || empty($activity) ) {
                if(empty($currency))
                    $pm_list[] = $pm;
                elseif($currency && $pm->has_currency_support($currency))
                    $pm_list[] = $pm;
            }
        }

        return $pm_list;
    }

    /**
     * @param string $pm_id
     * @return Leyka_Payment_Method Object, or false if it's not found. 
     */
    public function get_payment_method_by_id($pm_id) {

        $pm_id = trim((string)$pm_id);
        return empty($this->_payment_methods[$pm_id]) ? false : $this->_payment_methods[$pm_id]; 
    }
} //class end

/**
 * Class Leyka_Payment_Method
 */
abstract class Leyka_Payment_Method {

    protected $_id = '';
    protected $_gateway_id = '';
    protected $_active = true;
    protected $_label = '';
    protected $_description = '';
    protected $_support_global_fields = true;
    protected $_custom_fields = array();
    protected $_icons = array();
    protected $_submit_label = '';
    protected $_supported_currencies = array();
    protected $_default_currency = '';
    protected $_options = array();

    abstract public function __construct(array $params = array());

    public function __get($param) {

        switch($param) {
            case 'id': $param = $this->_id; break;
            case 'full_id': $param = $this->_gateway_id.'-'.$this->_id; break;
            case 'gateway_id': $param = $this->_gateway_id; break;
            case 'active':
            case 'is_active': $param = $this->_active; break;
            case 'label':
            case 'title':
            case 'name': $param = $this->_label; break;
            case 'desc':
            case 'description': $param = html_entity_decode($this->_description); break;
            case 'has_global_fields': $param = $this->_support_global_fields; break;
            case 'custom_fields': $param = $this->_custom_fields; break;
            case 'icons': $param = $this->_icons; break;
            case 'submit_label': $param = $this->_submit_label; break;
            case 'currencies': $param = $this->_supported_currencies; break;
            case 'default_currency': $param = $this->_default_currency; break;
            default:
//                trigger_error('Error: unknown param "'.$param.'"');
                $param = null;
        }

        return $param;
    }
    
    public function has_currency_support($currency = false) {
        if(empty($currency))
            return true;
        elseif(is_array($currency) && !array_diff($currency, $this->_supported_currencies))
            return true;
        elseif(in_array($currency, $this->_supported_currencies))
            return true;
        else
            return false;
    }

    abstract protected function _set_pm_options_defaults();

    /** @todo Someday we can comletely stop the support for this method, as it was only used in v2.0. */
    public function modify_options_values() {}

    /** @todo Maybe, it's worth to make this method a final. */
    protected function _add_pm_options() {

        foreach($this->_options as $option_name => $params) {

            if( !leyka_options()->option_exists($option_name) )
                leyka_options()->add_option($option_name, $params['type'], $params);
        }
    }

    public function initialize_pm_options() {

        $this->_set_pm_options_defaults();

        $this->_add_pm_options();

        /** @todo Someday we can comletely stop the support for this method, as it was only used in v2.0. */
        $this->modify_options_values();
    
        add_filter('leyka_payment_options_allocation', array($this, 'allocate_pm_options'), 10, 1);

//        global $wp_filter;
//        echo '<pre>' . print_r($wp_filter['leyka_payment_options_allocation'], TRUE) . '</pre>';
    }
    
    public function get_pm_options_names() {

        $option_names = array();
        foreach($this->_options as $option_name => $params) {
            $option_names[] = $option_name;
        }

        return $option_names;
    }

    /** Allocate gateway options, if needed */
    public function allocate_pm_options($options) {

        $gateway = leyka_get_gateway_by_id($this->_gateway_id); 
        $gateway_section_index = -1;

        foreach($options as $index => $option) {
            if( !empty($option['section']) && $option['section']['name'] == $gateway->id ) {
                $gateway_section_index = $index;
                break;
            }
        }

        $pm_options_names = $this->get_pm_options_names();

        if($gateway_section_index < 0)
            $options[] = array('section' => array(
                'name' => $gateway->id,
                'title' => $gateway->title,
                'is_default_collapsed' => false,
                'options' => $pm_options_names,
            ));
        else
            $options[$gateway_section_index]['section']['options'] = array_unique(array_merge(
                $pm_options_names,
                $options[$gateway_section_index]['section']['options']
            ));

        return $options;
    }

    public function save_settings() {

//        $pm_list = get_option('leyka_'.$this->_gateway_id.'_payment_methods');
//        $pm_list[$this->_id] = $this;
//
//        update_option('leyka_'.$this->_gateway_id.'_payment_methods', $pm_list);
    }
    
    public function set_activity($is_active) {
        
        $this->_active = !!$is_active;
        $this->save_settings();
    }
} // Leyka_Payment_Method end