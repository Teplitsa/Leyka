<?php if( !defined('WPINC') ) die;
/**
 * Leyka Init plugin setup Wizard class.
 **/

class Leyka_Cp_Wizard_Settings_Controller extends Leyka_Wizard_Settings_Controller {

    protected static $_instance = null;

    protected function _setAttributes() {

        $this->_id = 'cp';
        $this->_title = 'Мастер подключения CloudPayments';

    }

    protected function _loadCssJs() {

//        wp_enqueue_script(
//            'leyka-settings',
//            LEYKA_PLUGIN_BASE_URL.'assets/js/admin.js',
//            array('jquery',),
//            LEYKA_VERSION,
//            true
//        );
////        add_action('wp_enqueue_scripts', array($this, 'localize_scripts')); // wp_footer
//
//        wp_enqueue_style(
//            'leyka-settings',
//            LEYKA_PLUGIN_BASE_URL.'assets/css/admin.css',
//            array(),
//            LEYKA_VERSION
//        );

        parent::_loadCssJs();

    }

    protected function _setSections() {

        // The main CP settings section:
        $section = new Leyka_Settings_Section('cp', 'CloudPayments');

        // Test payment tryout step:
        $step = new Leyka_Settings_Step('cp_payment_tryout', $section->id, 'Тестовое пожертвование');
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => 'Давайте проверим, проходят ли пожертвования. Мы можем это сделать с помощью тестовых банковских карт, номера и данные которые находятся ниже.',
        )))->addBlock(new Leyka_Custom_Setting_Block(array(
            'id' => 'payment-tryout',
            'custom_setting_id' => 'cp_payment_tryout',
            'field_type' => 'custom_cp_payment_tryout',
            'rendering_type' => 'template',
        )))->addHandler(array($this, 'handlePaymentTryoutStep'))
            ->addTo($section);

        $this->_sections[$section->id] = $section;
        // The main CP settings section - End

        // Final Section:
        $section = new Leyka_Settings_Section('final', 'Завершение');

        $step = new Leyka_Settings_Step('init', $section->id, 'Поздравляем!', array('header_classes' => 'greater',));
        $step->addBlock(new Leyka_Text_Block(array(
            'id' => 'step-intro-text',
            'text' => '<p>Вы подключили CloudPayments. Стали доступны платежи с помощью платежных систем Visa и MasterCard.</p>
<p>Поделитесь вашей последней кампанией с друзьями и попросите их отправить пожертвование. Так вы сможете протестировать новый способ оплаты.</p>',
        )))->addTo($section);

        $this->_sections[$section->id] = $section;
        // Final Section - End

    }

    public function getSubmitData($component = null) {

        $step = $component && is_a($component, 'Leyka_Settings_Step') ? $component : $this->current_step;
        $submit_settings = array(
            'next_label' => 'Сохранить и продолжить',
            'next_url' => true,
            'prev' => 'Вернуться на предыдущий шаг',
        );
        
        if($step->next_label) {
            $submit_settings['next_label'] = $step->next_label;
        }

        if($step->section_id === 'cp' && $step->id === 'init') {

            $submit_settings['next_label'] = 'Поехали!';
            $submit_settings['prev'] = false; // I. e. the Wizard shouln't display the back link

        } else if($step->section_id === 'final') {

            $submit_settings['next_label'] = 'Перейти в Панель управления';
            $submit_settings['next_url'] = admin_url('admin.php?page=leyka');

        }

        return $submit_settings;

    }

    public function handlePaymentTryoutStep(array $step_settings) {

        $errors = array();

        // ...

//        if( !$campaign_id || !$campaign ) {
//            return new WP_Error('wrong_init_campaign_id', 'ID кампании неправильный или отсутствует');
//        }

        return $errors ? $errors : true;

    }

}