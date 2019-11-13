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

        $this->_id = 'support_packages'; // Must be a unique string, like "support-packages"
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
                    $this->_id.'_title' => array(
                        'type' => 'text',
                        'title' => '1. Заголовок обращения', // __('', 'leyka'),
//                        'comment' => __('Please, enter ...', 'leyka'),
                        'required' => true,
                        'placeholder' => 'Подпишитесь, чтобы прочитать целиком', // __('E.g., ', 'leyka'),
                    ),
                    $this->_id.'_main_text' => array(
                        'type' => 'textarea',
                        'title' => '2. Текстовое обращение', // __('', 'leyka'),
//                        'comment' => __('Please, enter ...', 'leyka'),
                        'required' => false,
                    ),
                    $this->_id.'_subscription_text' => array(
                        'type' => 'textarea',
                        'title' => '3. Текст о подписке', // __('', 'leyka'),
//                        'comment' => __('Please, enter ...', 'leyka'),
                        'required' => false,
                    ),
                    $this->_id.'_cta_labels' => array(
                        'type' => 'container',
                        'entries' => array(
                            $this->_id.'_activation_button_label' => array(
                                'type' => 'text',
                                'title' => '4. Надпись на кнопке активации', // __('', 'leyka'),
//                                'comment' => __('Please, enter ...', 'leyka'),
                                'required' => true,
                                'placeholder' => 'Подписаться', // __('E.g., ', 'leyka'),
                                'default' => 'Подписаться', // __('E.g., ', 'leyka'),
                            ),
                            $this->_id.'_account_link_label' => array(
                                'type' => 'text',
                                'title' => '5. Надпись для ссылки перехода в ЛК', // __('', 'leyka'),
//                                'comment' => __('Please, enter ...', 'leyka'),
                                'required' => true,
                                'placeholder' => 'У меня уже есть подписка', // __('E.g., ', 'leyka'),
                                'default' => 'У меня уже есть подписка', // __('', 'leyka'),
                            ),
                        )
                    ),
                    $this->_id.'_decoration_options' => array(
                        'type' => 'container',
                        'entries' => array(
                            $this->_id.'_active_elements_color' => array(
                                'type' => 'colorpicker',
                                'title' => '6. Цвет активных элементов', // __('', 'leyka'),
                                'description' => 'Рекомендуем яркий цвет', // __('', 'leyka'),
//                                'comment' => __('Please, enter ...', 'leyka'),
//                                'required' => true,
                                'default' => '#F38D04',
                            ),
                            $this->_id.'_background_color' => array(
                                'type' => 'colorpicker',
                                'title' => '7. Цвет подложки', // __('', 'leyka'),
                                'description' => 'Рекомендуем светлый оттенок', // __('', 'leyka'),
//                                'comment' => __('Please, enter ...', 'leyka'),
//                                'required' => true,
                                'default' => '#F4F5F9',
                            ),
                            $this->_id.'_inactive_elements_color' => array(
                                'type' => 'colorpicker',
                                'title' => '7. Цвет неактивных элементов', // __('', 'leyka'),
                                'description' => 'Рекомендуем светлый оттенок', // __('', 'leyka'),
//                                'comment' => __('Please, enter ...', 'leyka'),
//                                'required' => true,
                                'default' => '#FAFAFA',
                            ),
                        )
                    ),
                    $this->_id.'_closed_content_icon' => array(
                        'type' => 'file',
                        'title' => '',
                        'upload_label' => 'Загрузить иконку закрытого материала', // __('', 'leyka')
                        'description' => 'Файл в формате .png или .svg. Объём файла не больше 2 МБ', // __('', 'leyka'),
//                        'required' => false,
                        'default' => '', /** @todo Add the default icon URL */
                    ),
                )
            )),
            array('section' => array(
                'name' => $this->_id.'-packages',
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