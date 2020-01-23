<?php if( !defined('WPINC') ) die;
/**
 * Leyka Extension: Test 1
 * Version: 0.1a
 * Author: Author name goes here
 * Author URI: https://some-author.org
 * Debug only: 1
 **/

class Leyka_Test1_Extension extends Leyka_Extension {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'test-1'; // Must be a unique string, like "support-packages"
        $this->_title = 'Test extension #1'; // A human-readable title, like "Support packages"

        // A human-readable short description (for backoffice extensions list page):
        $this->_description = 'Это небольшое описание расширения, символов на 100-130. Оказалось, придумать осмысленный текст сама по себе задачка не из лёгких.';

        // A human-readable full description (for backoffice extensions list page):
        $this->_full_description = 'Это более подробное описание расширения, символов на 150-300. Например, вот такое длинное, как эта строка, которую нужно придумывать.<br><br>Это наш первый модуль - Пакеты поддержки. Бумажные или полиэтиленовые, отдельный вопрос - его ещё не прорабатывали на проектировании. Надо поднять на ближайшем созвоне.';

        // A human-readable description (for backoffice extension settings page):
        $this->_settings_description = 'Это описание работы расширения (или комментарий к нему), который выводится на странице настроек расширения в админ. разделе Лейки. Может быть довольно длинным - это уж вам решать. Но мы рекомендуем помнить, что молчание - золото. А лаконичность - вообще платина; лучше только телепатия.';

        // A human-readable description of how to enable the main feature (for backoffice extension settings page):
        $this->_connection_description = '<p><strong>Подключение функции «Бла-бла-бла»</strong></p>
<p>Вы можете сделать раздва или тричетырепять следующим образом:</p>
<code>[some_shortcode param1="Какая-то надпись"]</code>
<br>Ваш текст<br>
<code>[/some_shortcode]</code>';

        $this->_user_docs_link = '//your-site.org/extension-manual'; // Extension user manual page URL
        $this->_has_wizard = false;
        $this->_has_color_options = true;

    }

    protected function _set_options_defaults() {

        $this->_options = apply_filters('leyka_'.$this->_id.'_extension_options', array(
            $this->_id.'_title' => array(
                'type' => 'text',
                'title' => '1. Заголовок обращения', // __('', 'leyka'),
//                'comment' => __('Please, enter ...', 'leyka'),
                'required' => true,
                'placeholder' => 'Подпишитесь, чтобы прочитать целиком', // __('E.g., ', 'leyka'),
            ),
            $this->_id.'_main_text' => array(
                'type' => 'textarea',
                'title' => '2. Текстовое обращение', // __('', 'leyka'),
//                'comment' => __('Please, enter ...', 'leyka'),
                'required' => false,
            ),
            $this->_id.'_subscription_text' => array(
                'type' => 'textarea',
                'title' => '3. Текст о подписке', // __('', 'leyka'),
//                'comment' => __('Please, enter ...', 'leyka'),
                'required' => false,
            ),
            $this->_id.'_test_contained_options_1' => array(
                'type' => 'container',
                'entries' => array(
                    $this->_id.'_activation_button_label' => array(
                        'type' => 'text',
                        'title' => '4. Надпись на кнопке активации', // __('', 'leyka'),
//                        'comment' => __('Please, enter ...', 'leyka'),
                        'required' => true,
                        'placeholder' => 'Подписаться', // __('E.g., ', 'leyka'),
                        'default' => 'Подписаться', // __('E.g., ', 'leyka'),
                    ),
                    $this->_id.'_account_link_label' => array(
                        'type' => 'text',
                        'title' => '5. Надпись для ссылки перехода в ЛК', // __('', 'leyka'),
//                        'comment' => __('Please, enter ...', 'leyka'),
                        'required' => true,
                        'placeholder' => 'У меня уже есть подписка', // __('E.g., ', 'leyka'),
                        'default' => 'У меня уже есть подписка', // __('', 'leyka'),
                    ),
                )
            ),
        ));

    }

}

function leyka_add_extension_test_1() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_extension(Leyka_Test1_Extension::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_extension_test_1');