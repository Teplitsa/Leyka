<?php /** An example of the Leyka addon. */

class Leyka_Test2_Addon extends Leyka_Addon {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'test-2'; // Must be a unique string, like "support-packages"
        $this->_title = 'Test addon #2'; // A human-readable title, like "Support packages"
        $this->_description = 'The human-friendly addon description goes here.'; // A human-readable description (for backoffice)

        $this->_user_docs_link = ''; // Addon user manual page URL
        $this->_has_wizard = false;

    }

}

function leyka_add_addon_test_2() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_addon(Leyka_Test2_Addon::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_addon_test_2');