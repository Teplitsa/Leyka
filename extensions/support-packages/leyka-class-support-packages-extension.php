<?php if( !defined('WPINC') ) die;
/**
 * Extension name: Support Packages
 * Version: 0
 * Author: Teplitsa of social technologies
 * Author URI: https://te-st.ru
 * Debug only: 0
 **/

class Leyka_Support_Packages_Extension extends Leyka_Extension {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'support-packages'; // Must be a unique string, like "support-packages"
        $this->_title = __('Support packages', 'leyka'); // A human-readable title, like "Support packages"

        // A human-readable description (for backoffice extensions list page):
        $this->_description = 'Это небольшое описание расширения, символов на 100-130. Оказалось, придумать осмысленный текст сама по себе задачка не из лёгких.';
        $this->_full_description = 'Это более подробное описание расширения, символов на 150-300. Например, вот такое длинное, как эта строка, которую нужно придумывать.<br><br>Это наш первый модуль - Пакеты поддержки. Бумажные или полиэтиленовые, отдельный вопрос - его ещё не прорабатывали на проектировании. Надо поднять на ближайшем созвоне.';
        // A human-readable description (for backoffice extension settings page):
        $this->_settings_description = 'Если пользователь вдруг решает поменять сколько он(а) месячно жертвует, например увеличивает размер месячной поддержки с 999 рублей до 1050 рублей (попадая, таким образом из Базовых доноров в Серебряные), то переключение между Пакетами происходит автоматически.';

        $this->_connection_description = '<p><strong>Подключение функции «Ограничение доступа к контенту»</strong></p>
<p>Доступ можно ограничить ко всему посту или к частям текста с помощью шорткода</p>
<code>[leyka_limited_content support_plan="Программное название вознаграждения"]</code>
<br>Ваш текст<br>
<code>[/leyka_limited_content]</code>';

        $this->_user_docs_link = '//leyka.te-st.ru'; // Extension user manual page URL /** @todo Change it when possible. */
        $this->_has_wizard = false;

    }

    protected function _set_options_defaults() {

        $this->_options = apply_filters('leyka_'.$this->_id.'_extension_options', array(
            array('section' => array(
                'name' => $this->_id.'-main-options',
                'title' => __('Main options', 'leyka'),
                'is_default_collapsed' => false,
                'options' => array(
                    $this->_id.'_some_name' => array(
                        'type' => 'text',
                        'title' => __('Some title', 'leyka'),
                        'comment' => __('Please, enter the blah-blah here.', 'leyka'),
                        'required' => true,
                        'placeholder' => __('E.g., Blah-blah', 'leyka'),
                    ),
                )
            )),
            array('section' => array(
                'name' => $this->_id.'-packages-settings',
                'title' => __('Packages options', 'leyka'),
                'is_default_collapsed' => false,
                'options' => array(
                    $this->_id.'_custom_packages_settings' => array(
                        'type' => 'custom_support_packages_settings', // Special option type
                    ),
                )
            )),
            array('section' => array(
                'name' => $this->_id.'-for-devs',
                'title' => __('For developers', 'leyka'),
                'is_default_collapsed' => true,
                'options' => array(
                    $this->_id.'_for_devs' => array(
                        'type' => 'textarea',
                        'is_code' => true,
                        'title' => __('Styles settings', 'leyka'),
                        'default' => '/* .some-selector-1 { color: black; } */ '.__('/* The main font color */', 'leyka')
                            .'/* .some-selector-2 { color: orange; } */ '.__('/* The secondary font color */', 'leyka'),
                    ),
                )
            )),
        ));

    }

}

function leyka_add_extension_support_packages() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_extension(Leyka_Support_Packages_Extension::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_extension_support_packages');