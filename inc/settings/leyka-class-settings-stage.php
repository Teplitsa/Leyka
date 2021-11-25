<?php if( !defined('WPINC') ) die;
/**
 * Leyka Settings Stage class.
 **/

class Leyka_Settings_Stage {

    protected $_id;
    protected $_title;

    protected $_sections;

    public function __construct($id, $title) {

        $this->_id = trim($id);
        $this->_title = trim($title);

    }

    public function __get($name) {
        switch($name) {
            case 'id':
                return $this->_id;
            case 'title':
                return $this->_title;
            case 'init_section':
                return reset($this->_sections);
            case 'sections':
                return $this->_sections;
            default:
                return null;
        }
    }

    public function add_section(Leyka_Settings_Section $section) {

        $this->_sections[$section->id] = $section;

        return $this;

    }

    /** @return array An array of Stage Sections. */
    public function get_sections() {
        return $this->_sections;
    }

    /** @return Leyka_Settings_Section  */
    public function get_section_by_id($id) {

        $id = trim($id);

        return empty($this->_sections[$id]) ? null : $this->_sections[$id];

    }

    public function is_valid() {

        foreach($this->_sections as $section) { /** @var $section Leyka_Settings_Block */
            if( !$section->is_valid() ) {
                return false;
            }
        }

        return true;

    }

    public function get_errors() {

        $errors = [];

        foreach($this->_sections as $section) { /** @var $section Leyka_Settings_Section */
            $errors = array_merge($errors, $section->get_errors());
        }

        return $errors;

    }

}