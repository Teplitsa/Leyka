<?php if( !defined('WPINC') ) die;
/**
 * Leyka Setup Wizard Section class.
 **/

abstract class Leyka_Wizard_Section {

    protected $_id;
    protected $_title;
    protected $_steps;
    protected $_init_step = false;
    protected $_terminal_step = false;

//    protected $_current_step;

    protected static $_instance = null;

    /**
     * @return Leyka_Wizard_Section
     */
    public final static function get_instance() {

        if(null == static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;

    }

    final protected function __clone() {}

    protected function __construct() {

        $this->_set_attributes();
        $this->_set_steps();

        echo '<pre>'.print_r($this->_title.' instantiated!', 1).'</pre>';

    }

    abstract protected function _set_attributes();
    abstract protected function _set_steps();

    /**
     * @param $step mixed Either Leyka_Wizard_Step object ID or a whole object
     * @return Leyka_Wizard_Step
     */
    abstract protected function _get_next_step($step);

}

class Leyka_Init_ReceiverData_Section extends Leyka_Wizard_Section {

    protected static $_instance = null;

    protected function _set_attributes() {

        $this->_id = 'rd';
        $this->_title = 'Раздел 1: ваши данные';

    }

    protected function _set_steps() {

        // 0-step:
        $step = new Leyka_Wizard_Step('init', 'Шаг 0: вступительное сообщение');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'intro',
            'text' => 'Это будет первый абзац вступительного текста. Население перевозит органический мир. Коневодство постоянно. Приокеаническая пустыня вероятна. Приокеаническая пустыня параллельна.',
        )));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'intro',
            'text' => 'А это второй абзац вступительного текста начального шага начального же визарда. Путешествие в тысячу ли начинается с одного шага. Население перевозит органический мир. Коневодство постоянно. Приокеаническая пустыня вероятна. Приокеаническая пустыня параллельна.',
        )));

        $this->_init_step = $step;

        // Receiver type step:
        $step = new Leyka_Wizard_Step('account-type', 'Шаг 1: тип получателя');
        $step->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver-type', /** @todo Add this option to the meta array. */
            'option_id' => 'receiver_legal_type',
        )));
        $step->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver-country', /** @todo Add this option to the meta array. */
            'option_id' => 'receiver_country',
        )));

        $this->_steps[] = $step;

        // ...

        $step = new Leyka_Wizard_Step('terminal', 'Шаг X: заключительное сообщение');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'outro',
            'text' => 'Это заключительный абзац первого раздела визарда. Здесь какой-то мотивирующий текст а ля вы супер-молодец и уже почти всё сделали, уии! И про ваш пирожок на полке не забудьте - это самое важное условие для валидации раздела.',
        )));

        $this->_terminal_step = $step;

    }

    protected function _get_next_step($step) {
        /** @todo Incapsulate steps branching here. The result must be filterable. */
    }

}