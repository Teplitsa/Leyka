<?php if( !defined('WPINC') ) die;
/**
 * Leyka Extension: Example extension
 * Version: 0.1a
 * Author: Author name
 * Author URI: https://some-author.org
 * Debug only: 1
 **/

class Leyka_Example_Extension extends Leyka_Extension {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'example'; // Must be a unique string, like "example_ext". Use "_" to separate words, not "-"
        $this->_title = 'Пример расширения'; // A human-readable title, like "Extension Example"

        // A human-readable short description (for backoffice extensions list page):
        $this->_description = 'Это небольшое описание расширения, символов на 100-130. Оказалось, придумать осмысленный текст сама по себе задачка не из лёгких.';

        // A human-readable full description (for backoffice extensions list page):
        $this->_full_description = 'Это более подробное описание расширения, символов на 150-300. Например, вот такое длинное, как эта строка.';

        // A human-readable description (for backoffice extension settings page):
        $this->_settings_description = 'Это текст с описанием или комментарием работы расширения, который выводится на странице настроек расширения в административном разделе Лейки. Текст может быть довольно длинным, но мы рекомендуем помнить, что молчание - золото. А лаконичность - так вообще платина; лучше только телепатия.';

        // A human-readable description of how to enable the main feature (for backoffice extension settings page):
        $this->_connection_description = '<p><strong>В этом месте можно вывести краткое описание использования некоторой функции</strong></p>
<p>Например, вы можете использовать функцию «раз-два-три» так:</p>
<code>[some_shortcode param1="Какая-то надпись"]</code>
<br>Ваш текст<br>
<code>[/some_shortcode]</code>';

//        $this->_user_docs_link = '//your-site.org/extension-manual'; // Extension user manual page URL
        $this->_has_wizard = false;
        $this->_has_color_options = true;

    }

    protected function _set_options_defaults() {

        $this->_options = apply_filters('leyka_'.$this->_id.'_extension_options', [
            $this->_id.'_text_req' => [
                'type' => 'text',
                'title' => 'Обязательное текстовое поле',
                'comment' => 'Комментарий к полю. Может отсутствовать - при этом символ комментария к полю не выводится.',
                'required' => true,
                'placeholder' => 'Подсказка для заполнения поля',
                'description' => 'Пояснение к полю выше. Может отсутствовать.',
            ],
            $this->_id.'_text_non_req' => [
                'type' => 'text',
                'title' => 'Необязательное текстовое поле',
//                'comment' => 'Комментарий к полю. Может отсутствовать - при этом символ комментария к полю не выводится.',
                'required' => false,
                'placeholder' => 'Подсказка для заполнения поля',
            ],
            $this->_id.'_textarea_non_req' => [
                'type' => 'textarea',
                'title' => 'Необязательный многострочный текст',
                'comment' => 'Комментарий к полю',
                'required' => false,
            ],
            $this->_id.'_test_contained_options_1' => [
                'type' => 'container',
                'entries' => [
                    $this->_id.'_some_color' => [
                        'type' => 'colorpicker',
                        'title' => 'Цвет для вывода',
                        'description' => 'Рекомендуем красивый цвет',
                        'default' => '#123456',
                    ],
                    $this->_id.'_some_number' => [
                        'type' => 'number',
                        'title' => 'Некоторое число процентов',
                        'required' => true,
                        'default' => 25.7,
                        'min' => 0.0,
                        'max' => 100.0,
                        'step' => 0.1,
                    ],
                    $this->_id.'_checkbox' => [
                        'type' => 'checkbox',
                        'default' => true,
                        'title' => 'Галочка',
                        'description' => 'Отметьте, чтобы галочка оказалась проставленной',
                        'comment' => 'Комментарий к полю-галочке.',
//                        'short_format' => true,
                    ],
                ],
            ],
        ]);

    }

    /** Will be called only if the Extension is active. */
    protected function _initialize_active() {
    }

    /** Will be called everytime the Extension is loading into the plugin (i.e. always). */
    protected function _initialize_always() {
    }

}

function leyka_add_extension_test_1() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_extension(Leyka_Example_Extension::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_extension_test_1');