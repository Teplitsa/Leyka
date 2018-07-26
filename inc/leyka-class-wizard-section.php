<?php if( !defined('WPINC') ) die;
/**
 * Leyka Setup Wizard Section class.
 **/

abstract class Leyka_Wizard_Section extends Leyka_Singleton {

    protected $_id;
    protected $_title;
    protected $_steps;

    /** @todo Check if this "special" steps needed. */
    protected $_init_step = false;
    protected $_terminal_step = false;

//    protected $_current_step;

    protected static $_instance = null;

    /**
     * @return Leyka_Wizard_Section
     */
//    public final static function get_instance() {
//
//        if(null == static::$_instance) {
//            static::$_instance = new static();
//        }
//
//        return static::$_instance;
//
//    }
//
//    final protected function __clone() {}

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
    protected function _get_next_step($step) {
        /** @todo Incapsulate steps branching here. The result must be filterable. By default, it's next step in _steps array. */
    }

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
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'intro',
            'text' => 'А это второй абзац вступительного текста начального шага начального же визарда. Путешествие в тысячу ли начинается с одного шага. Население перевозит органический мир. Коневодство постоянно. Приокеаническая пустыня вероятна. Приокеаническая пустыня параллельна.',
        )));

        $this->_steps[] = $step;

        // Receiver type step:
        $step = new Leyka_Wizard_Step('account-type', 'Шаг 1: тип получателя');
        $step->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver-type', /** @todo Add this option to the meta array. */
            'option_id' => 'receiver_legal_type',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'receiver-country', /** @todo Add this option to the meta array. */
            'option_id' => 'receiver_country',
        )));

        $this->_steps[] = $step;

        // ...

//        $step = new Leyka_Wizard_Step('terminal', 'Шаг X: заключительное сообщение');
//        $step->addBlock(new Leyka_Text_Block(array(
//            'id' => 'outro',
//            'text' => 'Это заключительный абзац первого раздела визарда. Здесь какой-то мотивирующий текст а ля вы супер-молодец и уже почти всё сделали, уии! И про ваш пирожок на полке не забудьте - это самое важное условие для валидации раздела.',
//        )));
//
//        $this->_terminal_step = $step;

    }

    protected function _get_next_step($step) {
        /** @todo Incapsulate steps branching here. The result must be filterable. */
    }

}

class Leyka_Init_CampaignData_Section extends Leyka_Wizard_Section {

    protected static $_instance = null;

    protected function _set_attributes() {

        $this->_id = 'cd';
        $this->_title = 'Раздел 2: настройка кампании';

    }

    protected function _set_steps() {

        $step = new Leyka_Wizard_Step('init', 'Шаг 0: настроим кампанию!');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'intro',
            'text' => 'Осталось совсем немного и первая кампания будет запущена. Это будет первый абзац вступительного текста, и ещё немного о том, что дальше ещё нужно настроить платёжку.',
        )));

        $this->_steps[] = $step;

        // Receiver type step:
        $step = new Leyka_Wizard_Step('campaign-description', 'Шаг 1: описание вашей кампании');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'campaign-desc-text',
            'text' => 'Текст, который идёт перед полями кампании на этом шаге. В нём может описываться, например, что вообще такое кампания.',
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'campaign-title',
            'option_id' => 'init-campaign-title',
            'option_data' => array(
                'type' => 'text',
                'title' => 'Название кампании',
//                'description' => '',
                'required' => 1,
                'placeholder' => 'Например, «На уставную деятельность организации»',
            ),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'campaign-lead',
            'option_id' => 'init-campaign-lead',
            'option_data' => array(
                'type' => 'textarea',
                'title' => 'Краткое описание кампании',
                'required' => 0,
                'placeholder' => 'Например, «Ваше пожертвование пойдёт на выполнение уставной деятельности в текущем году.»',
            ),
        )))->addBlock(new Leyka_Option_Block(array(
            'id' => 'campaign-target',
            'option_id' => 'init-campaign-target',
            'option_data' => array(
                'type' => 'number',
                'title' => 'Целевая сумма',
                'required' => 0,
                'min' => 0,
                'max' => 60000,
                'step' => 1,
            ),
        )))->addBlock(new Leyka_Text_Block(array(
            'id' => 'campaign-desc-finished',
            'text' => 'Текст, которым визард завершается. Здесь слова о том, что настройки выполнены, и вэлкам вам в дэшборд.',
        )));

        $this->_steps[] = $step;

    }

}