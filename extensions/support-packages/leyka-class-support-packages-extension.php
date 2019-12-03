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

        // A human-readable short description (for backoffice extensions list page):
        $this->_description = 'Это небольшое описание расширения, символов на 100-130. Оказалось, придумать осмысленный текст сама по себе задачка не из лёгких.';

        // A human-readable full description (for backoffice extensions list page):
        $this->_full_description = 'Это более подробное описание расширения, символов на 150-300. Например, вот такое длинное, как эта строка, которую нужно придумывать.<br><br>Это наш первый модуль - Пакеты поддержки. Бумажные или полиэтиленовые, отдельный вопрос - его ещё не прорабатывали на проектировании. Надо поднять на ближайшем созвоне.';

        // A human-readable description (for backoffice extension settings page):
        $this->_settings_description = 'Если пользователь вдруг решает поменять сколько он(а) месячно жертвует, например увеличивает размер месячной поддержки с 999 рублей до 1050 рублей (попадая, таким образом из Базовых доноров в Серебряные), то переключение между Пакетами происходит автоматически.';

        // A human-readable description of how to enable the main feature (for backoffice extension settings page):
        $this->_connection_description = '<p><strong>Подключение функции «Ограничение доступа к контенту»</strong></p>
<p>Доступ можно ограничить ко всему посту или к частям текста с помощью шорткода</p>
<code>[leyka_limited_content support_plan="Программное название вознаграждения"]</code>
<br>Ваш текст<br>
<code>[/leyka_limited_content]</code>';

        $this->_user_docs_link = '//leyka.te-st.ru'; // Extension user manual page URL /** @todo Change it when possible. */
        $this->_has_wizard = false;
        $this->_has_color_options = true;

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
                        'width' => 0.5,
                    ),
                    $this->_id.'_main_text' => array(
                        'type' => 'textarea',
                        'title' => '2. Текстовое обращение', // __('', 'leyka'),
//                        'comment' => __('Please, enter ...', 'leyka'),
//                        'placeholder' => 'Разрушить стереотипы, объединить ребят, увлеченных технологиями, вдохновить на поиски своего призвания – такие цели ставили перед собой участники Европейской недели программирования.',
                        'required' => false,
                    ),
                    $this->_id.'_subscription_text' => array(
                        'type' => 'textarea',
                        'title' => '3. Текст о подписке', // __('', 'leyka'),
//                        'comment' => __('Please, enter ...', 'leyka'),
                        'placeholder' => 'Подписка продлевается автоматически. Вы можете отписаться в любой момент в личном кабинете',
                        'required' => false,
                    ),
                    $this->_id.'_activation_button_label' => array(
                        'type' => 'text',
                        'title' => '4. Надпись на кнопке активации', // __('', 'leyka'),
//                                'comment' => __('Please, enter ...', 'leyka'),
                        'required' => true,
                        'placeholder' => 'Подписаться', // __('E.g., ', 'leyka'),
                        'default' => 'Подписаться', // __('E.g., ', 'leyka'),
                        'width' => 0.5,
                    ),
                    $this->_id.'_account_link_label' => array(
                        'type' => 'text',
                        'title' => '5. Надпись для ссылки перехода в ЛК', // __('', 'leyka'),
//                                'comment' => __('Please, enter ...', 'leyka'),
                        'required' => true,
                        'placeholder' => 'У меня уже есть подписка', // __('E.g., ', 'leyka'),
                        'default' => 'У меня уже есть подписка', // __('', 'leyka'),
                        'width' => 0.5,
                    ),
                    $this->_id.'_closed_content_icon' => array(
                        'type' => 'file',
//                        'upload_format' => 'pics',
//                        'show_preview' => false,
                        'title' => '',
//                        'upload_title' => 'Выберите картинку',
                        'upload_label' => __('Load closed content icon', 'leyka'),
                        'description' => __('A *.png or *.svg file. The size is no more than 2 Mb', 'leyka'),
//                        'comment' => 'Тестовый коммент к полю загрузки картинки.',
//                        'required' => false,
                        'default' => '', /** @todo Add the default icon URL */
//                        'field_classes' => '', /** @todo Add the default icon URL */
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
                    $this->_id.'_css' => array(
                        'type' => 'textarea',
                        'is_code_editor' => 'css',
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