<?php if( !defined('WPINC') ) die;
/**
 * Leyka Settings Block class.
 **/

abstract class Leyka_Settings_Block {

    protected $_id;

    public function __construct(array $params = array()) {

        if( !empty($params['id']) ) {
            $this->_id = trim($params['id']);
        }

    }

    public function __get($name) {
        switch($name) {
            case 'id':
                return $this->_id;
            default:
                return null;
        }
    }

    abstract public function getContent();
    abstract public function isValid();
    abstract public function getErrors();
    abstract public function getFieldsValues();

}

class Leyka_Text_Block extends Leyka_Settings_Block {

    protected $_text = '';

    public function __construct(array $params = array()) {

        parent::__construct($params);

        if( !empty($params['text'] ) ) {
            $this->_text = $params['text'];
        }

    }

    public function getContent() {
        return $this->_text;
    }

    public function isValid() {
        return true;
    }

    public function getErrors() {
        return array();
    }

    public function getFieldsValues() {
        return array();
    }

}

class Leyka_Option_Block extends Leyka_Settings_Block {

    protected $_option_id = '';
    protected $_show_title = true;
    protected $_show_description = true;

    public function __construct(array $params = array()) {

        parent::__construct($params);

        if(empty($params['option_id'])) {
            /** @todo Throw some Exception */
        } else if( !leyka_options()->option_exists($params['option_id']) ) {
            /** @todo Throw some Exception */
        }

        $params = wp_parse_args($params, array(
            'show_title' => true,
            'show_description' => true,
        ));

        $this->_option_id = $params['option_id'];
        $this->_show_title = !!$params['show_title'];
        $this->_show_description = !!$params['show_description'];

    }

    public function __get($name) {

        switch($name) {
            case 'option_id': return $this->_option_id;
            case 'show_title': return !!$this->_show_title;
            case 'show_description': return !!$this->_show_description;
            default: return parent::__get($name);
        }

    }

    public function getContent() {
        return $this->_option_id; // leyka_options()->get_info_of($this->_option_id);
    }

    public function isValid() {

        $value = isset($_POST['leyka_'.$this->_option_id]) ? $_POST['leyka_'.$this->_option_id] : false;

        return leyka_options()->is_valid($this->_option_id, $value);

    }

    public function getErrors() {

        $value = isset($_POST['leyka_'.$this->_option_id]) ? $_POST['leyka_'.$this->_option_id] : false;
        $error_messages = array();

        foreach(leyka_options()->get_validation_errors($this->_option_id, $value) as $error_message) {
            $error_messages[] = $error_message;
        }

        if($error_messages) {

            $error = new WP_Error();
            foreach($error_messages as $error_message) {
                $error->add('option_invalid', $error_message);
            }

            return $error;

        } else {
            return false;
        }

    }

    /** Get all options & values set on the step
     * @return array
     */
    public function getFieldsValues() {
        return isset($_POST['leyka_'.$this->_option_id]) ?
            array($this->_option_id => $_POST['leyka_'.$this->_option_id]) : array();
    }

}

class Leyka_Container_Block extends Leyka_Settings_Block {

    protected $_blocks;
    protected $_entry_width = false;

    public function __construct(array $params = array()) {

        parent::__construct($params);

        if( !empty($params['entry_width']) ) {

            $params['entry_width'] = (float)$params['entry_width'];
            if($params['entry_width'] > 0.0 && $params['entry_width'] <= 1.0) {
                $this->_entry_width = $params['entry_width'];
            }

        }

        if( !empty($params['entries']) && is_array($params['entries']) ) {

            foreach($params['entries'] as $block) {
                if( !is_a($block, 'Leyka_Settings_Block') ) {
                    /** @todo Throw some Exception */
                } else {
                    $this->_blocks[] = $block;
                }
            }

        }

    }

    public function __get($name) {

        switch($name) {
            case 'entry_width': return $this->_entry_width ?
                $this->_entry_width :
                (count($this->_blocks) ? round(1.0/count($this->_blocks), 1) : false);
            default: return parent::__get($name);
        }

    }

    public function addBlock(Leyka_Settings_Block $block) {
        $this->_blocks[] = $block;
    }

    public function getContent() {
        return $this->_blocks;
    }

    public function isValid() {

        foreach($this->_blocks as $block) { /** @var $block Leyka_Settings_Block */
            if( !$block->isValid() ) {
                return false;
            }
        }

        return true;

    }

    public function getErrors() {

        $errors = array();

        foreach($this->_blocks as $sub_block) { /** @var $sub_block Leyka_Settings_Block */

            $sub_block_errors = $sub_block->getErrors();
//            echo '<pre>'.print_r($sub_block_errors, 1).'</pre>';
            $errors = $sub_block_errors ? array_merge($errors, $sub_block_errors) : $errors;

        }

        return $errors;

    }

    /** Get all options & values set on the step
     * @return array
     */
    public function getFieldsValues() {

        $fields_values = array();

        foreach($this->_blocks as $block) { /** @var $block Leyka_Settings_Block */
            $fields_values = array_merge($fields_values, $block->getFieldsValues());
        }

        return $fields_values;

    }

}

class Leyka_Custom_Option_Block extends Leyka_Settings_Block {

    protected $_option_id = '';

    public function __construct(array $params = array()) {

        parent::__construct($params);

        if(empty($params['option_id'])) {
            /** @todo Throw some Exception */
        } else if( !leyka_options()->option_exists($params['option_id']) ) {
            /** @todo Throw some Exception */
        }

        $this->_option_id = $params['option_id'];

    }

    public function __get($name) {

        switch($name) {
            case 'option_id': return $this->_option_id;
            default: return parent::__get($name);
        }

    }

    public function getContent() {
        return $this->_option_id; // leyka_options()->get_info_of($this->_option_id);
    }

    public function isValid() {
        return true;
    }

    public function getErrors() {
        return false;
    }

    /** Get all options & values set on the step
     * @return array
     */
    public function getFieldsValues() {
        return array($this->_option_id => 'some value');
//        return isset($_POST['leyka_'.$this->_option_id]) ?
//            array($this->_option_id => $_POST['leyka_'.$this->_option_id]) : array();
    }

}