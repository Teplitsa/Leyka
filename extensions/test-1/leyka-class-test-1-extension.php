<?php if( !defined('WPINC') ) die;
/**
 * Leyka Extension: Test 1
 * Version: 0.1a
 * Author: Author name goes here
 * Author URI: https://some-author.org
 **/

class Leyka_Test1_Extension extends Leyka_Extension {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'test-1'; // Must be a unique string, like "support-packages"
        $this->_title = 'Test extension #1'; // A human-readable title, like "Support packages"

        // A human-readable description (for backoffice extensions list page):
        $this->_description = 'Это небольшое описание расширения, символов на 100-130.';
        // A human-readable description (for backoffice extension settings page):
        $this->_settings_description = 'The human-friendly extension description for extension settings page goes here.';

        $this->_user_docs_link = ''; // Extension user manual page URL
        $this->_has_wizard = false;

    }

}

function leyka_add_extension_test_1() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_extension(Leyka_Test1_Extension::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_extension_test_1');