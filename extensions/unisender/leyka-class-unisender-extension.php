<?php if ( !defined('WPINC') ) die;

/**
 * Leyka Extension: Unisender extension
 * Version: 0.1
 * Author: Teplitsa of social technologies
 * Author URI: https://te-st.ru
 **/


class Leyka_Unisender_Extension extends Leyka_Extension {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'unisender';
        $this->_title = __('Unisender', 'leyka');
        $this->_description = __('Расширение для интеграции с оператором почтовых рассылок Unisender.', 'leyka');
        $this->_full_description = __('Расширение предоставляет возможность автоматического добавления доноров из плагина Лейки в список рассылки в Unisender.', 'leyka');
        $this->_settings_description = __('Расширение предоставляет возможность автоматического добавления доноров из плагина Лейки в список рассылки в Unisender.', 'leyka');
        // $this->_connection_description = 'Some HTML';
        //$this->_user_docs_link = '//your-site.org/extension-manual'; // Extension user manual page URL
        $this->_has_wizard = false;
        $this->_has_color_options = false;
        $this->_icon = LEYKA_PLUGIN_BASE_URL.'extensions/unisender/img/main_icon.jpeg';

    }

    protected function _set_options_defaults() {

        $this->_options = apply_filters('leyka_'.$this->_id.'_extension_options', [
            $this->_id.'_login' => [
                'type' => 'text',
                'title' => __('Login', 'leyka'),
                'comment' => __('Login for the "Unisender" personal account', 'leyka'),
                'required' => true,
                'placeholder' => 'Unisender_login'
            ],
            $this->_id.'_api_key' => [
                'type' => 'text',
                'title' => __('API key', 'leyka'),
                'comment' => __('"Unisender" API key', 'leyka'),
                'required' => true,
                'placeholder' => '11aabbcdyefghk2aabbcdyefghkabcdef3ghka4b',
            ],
            $this->_id.'_donor_fields' => [
                'type' => 'select',
                'title' => __('Donor fields', 'leyka'),
                'required' => true,
                'comment' => __('Donor fields which will be transferred to "Unisender"', 'leyka'),
                'list_entries' => [
                    1 => __('Name', 'leyka'),
                    2 => __('Email', 'leyka')
                ],
                'value' => 2
            ]
        ]);

    }

    /** Will be called only if the Extension is active. */
    protected function _initialize_active()
    {
    }

    /** Will be called everytime the Extension is loading into the plugin (i.e. always). */
    protected function _initialize_always()
    {
    }

}

function leyka_add_extension_unisender()
{
    leyka()->add_extension(Leyka_Unisender_Extension::get_instance());
}

add_action('leyka_init_actions', 'leyka_add_extension_unisender');

