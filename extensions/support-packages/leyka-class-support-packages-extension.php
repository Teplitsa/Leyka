<?php if( !defined('WPINC') ) die;
/**
 * Leyka Extension: Support Packages
 * Version: -
 * Author: Teplitsa of social technologies
 * Author URI: https://te-st.ru
 **/

class Leyka_Support_Packages_Extension extends Leyka_Extension {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'support-packages'; // Must be a unique string, like "support-packages"
        $this->_title = __('Support packages', 'leyka'); // A human-readable title, like "Support packages"

        // A human-readable description (for backoffice extensions list page):
        $this->_description = 'Это небольшое описание расширения, символов на 100-130.';
        // A human-readable description (for backoffice extension settings page):
        $this->_settings_description = 'Если пользователь вдруг решает поменять сколько он(а) месячно жертвует, например увеличивает размер месячной поддержки с 999 рублей до 1050 рублей (попадая, таким образом из Базовых доноров в Серебряные), то переключение между Пакетами происходит автоматически.';

        $this->_user_docs_link = '//leyka.te-st.ru'; // Extension user manual page URL
        $this->_has_wizard = false;

    }

}

function leyka_add_extension_support_packages() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_extension(Leyka_Support_Packages_Extension::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_extension_support_packages');