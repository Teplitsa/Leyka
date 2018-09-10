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
            'text' => 'Вы успешно завершили установку и настройку платежей через CloudPayments для «Лейки».',
        )))->addTo($section);

        $this->_sections[$section->id] = $section;
        // Final Section - End

    }

//    protected function _getNextStepId(Leyka_Settings_Step $step_from = null, $return_full_id = true) {
//
//        $step_from = $step_from && is_a($step_from, 'Leyka_Settings_Step') ? $step_from : $this->current_step;
//        $next_step_full_id = false;
//
//        /** @todo To many if-elses sucks - try some wrapping pattern here */
//        if($step_from->section_id === 'rd') {
//
//            if($step_from->id === 'init') {
//                $next_step_full_id = $step_from->section_id.'-receiver_type';
//            } else if($step_from->id === 'receiver_type') {
//
//                $next_step_full_id = $this->_getSettingValue('receiver_legal_type') === 'legal' ?
//                    $step_from->section_id.'-receiver_legal_data' :
//                    $step_from->section_id.'-receiver_physical_data';
//
//            } else if($step_from->id === 'receiver_legal_data') {
//                $next_step_full_id = $step_from->section_id.'-receiver_legal_bank_essentials';
//            } else if($step_from->id === 'receiver_physical_data') {
//                $next_step_full_id = $step_from->section_id.'-receiver_physical_bank_essentials';
//            } else if(stripos($step_from->id, 'bank_essentials')) {
//
//                $next_step_full_id = $this->_getSettingValue('receiver_legal_type') === 'legal' ?
//                    $step_from->section_id.'-receiver_legal_terms_of_service' :
//                    $step_from->section_id.'-receiver_physical_terms_of_service';
//
//            } else if(stripos($step_from->id, 'terms_of_service')) {
//
//                $next_step_full_id = $this->_getSettingValue('receiver_legal_type') === 'legal' ?
//                    $step_from->section_id.'-receiver_legal_pd_terms' :
//                    $step_from->section_id.'-receiver_physical_pd_terms';
//
//            } else if(stripos($step_from->id, 'pd_terms')) {
//                $next_step_full_id = $step_from->section_id.'-final';
//            } else if($step_from->id === 'final') {
//                $next_step_full_id = 'dd-plugin_stats';
//            }
//
//        } else if($step_from->section_id === 'dd') {
//
//            if($step_from->id === 'plugin_stats') {
//
//                $next_step_full_id = $this->_getSettingValue('send_plugin_stats') === 'n' ?
//                    $step_from->section_id.'-plugin_stats_refused' :
//                    $step_from->section_id.'-plugin_stats_accepted';
//
//            } else {
//                $next_step_full_id = 'cd-campaign_description';
//            }
//
//        } else if($step_from->section_id === 'cd') {
//
//            if($step_from->id === 'campaign_description') {
//                $next_step_full_id = $step_from->section_id.'-campaign_decoration';
//            } else if($step_from->id === 'campaign_decoration') {
//                $next_step_full_id = $step_from->section_id.'-donors_communication';
//            } else if($step_from->id === 'donors_communication') {
//                $next_step_full_id = $step_from->section_id.'-campaign_completed';
//            } else if($step_from->id === 'campaign_completed') {
//                $next_step_full_id = 'final-init';
//            }
//
//        } else if($step_from->section_id === 'final') { // Final Section
//            $next_step_full_id = true;
//        }
//
//        if( !!$return_full_id || !is_string($next_step_full_id) ) {
//            return $next_step_full_id;
//        } else {
//
//            $next_step_full_id = explode('-', $next_step_full_id);
//
//            return array_pop($next_step_full_id);
//
//        }
//
//    }

//    protected function _initNavigationData() {
//
//        $this->_navigation_data = array(
//            array(
//                'section_id' => 'cp',
//                'title' => 'CloudPayments',
//                'url' => '',
//                'steps' => array(
//                    array(
//                        'step_id' => 'cp_payment_tryout',
//                        'title' => 'Тестовое пожертвование',
//                        'url' => '',
//                    ),
////                    array(
////                        'step_id' => '',
////                        'title' => '',
////                        'url' => '',
////                    ),
//                ),
//            ),
//            array(
//                'section_id' => 'final',
//                'title' => 'Завершение',
//                'url' => '',
//            ),
//        );
//
//    }

//    public function getNavigationData() {
//
//        $current_navigation_data = $this->_navigation_data;
//        $current_step_full_id = $this->getCurrentStep()->full_id;
//
//        switch($current_step_full_id) {
//            case 'rd-init': $navigation_position = 'rd'; break;
//            case 'rd-receiver_type': $navigation_position = $current_step_full_id; break;
//            case 'rd-receiver_legal_data':
//            case 'rd-receiver_physical_data':
//                $navigation_position = 'rd-receiver_data';
//                break;
//            case 'rd-receiver_legal_bank_essentials':
//            case 'rd-receiver_physical_bank_essentials':
//                $navigation_position = 'rd-receiver_bank_essentials';
//                break;
//            case 'rd-receiver_legal_terms_of_service':
//            case 'rd-receiver_physical_terms_of_service':
//                $navigation_position = 'rd-receiver_terms_of_service';
//                break;
//            case 'rd-receiver_legal_pd_terms':
//            case 'rd-receiver_physical_pd_terms':
//                $navigation_position = 'rd-receiver_pd_terms';
//                break;
//            case 'rd-final': $navigation_position = 'rd--'; break;
//            case 'dd-plugin_stats': $navigation_position = 'dd'; break;
//            case 'dd-plugin_stats_accepted':
//            case 'dd-plugin_stats_refused':
//                $navigation_position = 'dd--';
//                break;
//            case 'cd-campaign_description':
//            case 'cd-campaign_decoration':
//            case 'cd-donors_communication':
//                $navigation_position = $current_step_full_id; break;
//            case 'cd-campaign_completed':
//                $navigation_position = 'cd--'; break;
//            case 'final-init': $navigation_position = 'final--'; break;
//            default: $navigation_position = false;
//        }
//
//        return $navigation_position ?
//            $this->_processNavigationData($navigation_position) :
//            $current_navigation_data;
//
//    }

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

//    public function stepInit() {
//
//        // Receiver type Step prerequisites - show "legal" receiver type only if receiver country is set:
//        if($this->_getSettingValue('receiver_country') === '-') {
//            add_filter('leyka_option_info-receiver_legal_type', function($option_data){
//
//                unset($option_data['list_entries']['legal']);
//
//                return $option_data;
//
//            });
//        }
//
//        // If init campaign is not set or deleted on the campaign decoration step, return to the campaign data step:
//        if($this->getCurrentStep()->id === 'campaign_decoration') {
//
//            $init_campaign_id = get_transient('leyka_init_campaign_id');
//            $init_campaign = get_post($init_campaign_id);
//
//            if( !$init_campaign_id || !$init_campaign ) {
//                $this->_handleSettingsGoBack('cd-campaign_description');
//            }
//
//        } else if($this->getCurrentStep()->id === 'campaign_completed') {
//
//            $init_campaign_id = get_transient('leyka_init_campaign_id');
//            $init_campaign = get_post($init_campaign_id);
//
//            if( !$init_campaign_id || !$init_campaign ) {
//                $this->_handleSettingsGoBack('cd-campaign_description');
//            }
//
//            $empty_bank_essentials_options = leyka_get_empty_bank_essentials_options();
//            if($empty_bank_essentials_options) { // Show the fields
//                foreach($empty_bank_essentials_options as $option_id) {
//                    $this->getCurrentStep()->addBlock(new Leyka_Option_Block(array(
//                        'id' => $option_id,
//                        'option_id' => $option_id,
//                    )));
//                }
//            } else { // Enable the Quittance PM
//
//                $pm_data = leyka_options()->opt('pm_available');
//                $quittance_pm_full_id = Leyka_Bank_Order::get_instance()->full_id;
//
//                if( !in_array($quittance_pm_full_id, $pm_data) ) {
//
//                    $pm_data[] = $quittance_pm_full_id;
//                    leyka_options()->opt('pm_available', $pm_data);
//
//                    $pm_order = array();
//                    foreach($pm_data as $pm_full_id) {
//                        if($pm_full_id) {
//                            $pm_order[] = "pm_order[]={$pm_full_id}";
//                        }
//                    }
//
//                    leyka_options()->opt('pm_order', implode('&', $pm_order));
//
//                }
//
//            }
//
//        }
//
//    }


    public function handlePaymentTryoutStep(array $step_settings) {

        $errors = array();

        // ...

//        if( !$campaign_id || !$campaign ) {
//            return new WP_Error('wrong_init_campaign_id', 'ID кампании неправильный или отсутствует');
//        }

        return $errors ? $errors : true;

    }

}