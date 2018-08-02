<?php if( !defined('WPINC') ) die;
/**
 * Leyka Setup Wizard Section class.
 **/

class Leyka_Settings_Section {

    protected $_id;
    protected $_title;

    protected $_steps;

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
            case 'init_step':
                return reset($this->_steps);
            case 'steps':
                return $this->_steps;
            default:
                return null;
        }
    }

    public function addStep(Leyka_Settings_Step $step) {

        $this->_steps[$step->id] = $step;

        return $this;

    }

    /** @return array An array of Section Steps. */
    public function getSteps() {
        return $this->_steps;
    }

    /** @return Leyka_Settings_Step  */
    public function getStepById($id) {

        $id = trim($id);

        return empty($this->_steps[$id]) ? null : $this->_steps[$id];

    }

}