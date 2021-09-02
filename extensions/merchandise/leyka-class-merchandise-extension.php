<?php if( !defined('WPINC') ) die;
/**
 * Leyka Extension: Merchandise/gifts for donors
 * Version: 1.0
 * Author: Teplitsa of social technologies
 * Author URI: https://te-st.ru
 **/

class Leyka_Merchandise_Extension extends Leyka_Extension {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'merchandise';
        $this->_title = __('Merchandise/gifts for donors', 'leyka');

        // A human-readable short description (for backoffice extensions list page):
        $this->_description = 'Добавьте варианты подарков за пожертвования на ваши Лейко-формы, чтобы донор мог получать подарки от вас в зависимости от размера его пожертвования.';

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

        $this->_user_docs_link = '//leyka.te-st.ru/docs/merch-manual';
        $this->_has_wizard = false;
        $this->_has_color_options = false;

    }

    protected function _set_options_defaults() {

        $this->_options = apply_filters('leyka_'.$this->_id.'_extension_options', array(
            // No options for this Extension yet
        ));

    }

    /** Will be called only if the Extension is active. */
    protected function _initialize_active() {
    }

    /** Will be called everytime the Extension is loading into the plugin (i.e. always). */
    protected function _initialize_always() {
    }

}

function leyka_add_extension_merchandise() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_extension(Leyka_Merchandise_Extension::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_extension_merchandise');