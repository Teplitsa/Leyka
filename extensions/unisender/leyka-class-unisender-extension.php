<?php if ( !defined('WPINC') ) die;

// TODO В названии расширения в этом комменте лучше "Unisender integration". "Extension" там неинформативно.
/**
 * Leyka Extension: Unisender extension
 * Version: 0.1
 * Author: Teplitsa of social technologies
 * Author URI: https://te-st.ru
 **/

// TODO Лишний пропуск строки.
class Leyka_Unisender_Extension extends Leyka_Extension {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'unisender';
        $this->_title = __('Unisender', 'leyka');
        $this->_description = __('This extension provides an integration with the "Unisender" service.', 'leyka'); // TODO Здесь и далее - Unisender не пишется в кавычках.
        $this->_full_description = __('The extension provides an automatic subscrition of the donors to Unisender mailing lists of your choice.', 'leyka');
        $this->_settings_description = ''; // TODO На будущее: если один из текстов описания расширения объективно не нужен, его лучше оставить пустым, а не дублировать другой текст.
        $this->_connection_description = __(
            '<h4>Short instruction:</h4>
            <div>
                <ol>
                    <li>Register the "Unisender" account</li>
                    <li>Create one or more mailing lists and save their IDs in extension settings</li>
                    <li>Copy API key from "Unisender" personal account to extension settings</li>
                    <li>Select needed donor fields</li>
                </ol>
            </div>'
        , 'leyka'); // TODO Заменить до релиза
        $this->_user_docs_link = 'https://www.unisender.com/ru/support/'; // TODO Заменить до релиза
        $this->_has_wizard = false;
        $this->_has_color_options = false;
        $this->_icon = LEYKA_PLUGIN_BASE_URL.'extensions/unisender/img/main_icon.jpeg'; // TODO В идеале, для иконки лучше найти файл SVG. Другие форматы (png, jpg) мы используем только если подходящего SVG не было найдено.

    }

    protected function _set_options_defaults() {

        $this->_options = apply_filters('leyka_'.$this->_id.'_extension_options', [
            $this->_id.'_api_key' => [
                'type' => 'text',
                'title' => __('API key', 'leyka'),
                'comment' => __('"Unisender" API key', 'leyka'),
                'required' => true,
                'is_password' => true,
                'placeholder' => 'abcdefghijklmnopqrstuvwxyz1234567890',
            ],
            $this->_id.'_lists_ids' => [
                'type' => 'text',
                'title' => __('IDs of the "Unisender" lists to subscribe', 'leyka'),
                'comment' => __('IDs of the lists (in "Unisender") that holds donors contacts', 'leyka'),
                'required' => true,
                'placeholder' => '1,3,10', // TODO Нужно обязательно проверить/обработать кейс, когда ИДы перечислены с пробелами (1, 3, 10). Это должно приниматься и обрабатываться корректно.
                'description' => __('Comma-separated IDs list', 'leyka'),
            ],
            $this->_id.'_donor_fields' => [
                'type' => 'multi_select',
                'title' => __('Donor fields', 'leyka'),
                'required' => true,
                'comment' => __('Donor fields which will be transferred to "Unisender"', 'leyka'),
                'list_entries' => $this->_get_donor_fields(),
                'default' => ['name'], // 'default' should be an array of values (even if it's single value there)
            ],
            $this->_id.'_donor_confirmation' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Donor subscription confirmation', 'leyka'),
                'comment' => __('If enabled donors will be asked by email for permission upon subscribing on the list', 'leyka'),
                'short_format' => true,
            ]
        ]);

    }

    protected function _get_donor_fields() {

        $fields_library = leyka_options()->get_value('additional_donation_form_fields_library'); // TODO Здесь и далее: почему используется get_value()? Это низкоуровневый метод, его обычно не нужно вызывать напрямую. opt() не подходит?
        $additional_fields = ['name' => __('Name', 'leyka')];

        foreach ($fields_library as $name => $data) {
            $additional_fields[$name] = __($data['title'], 'leyka');
        }

        return $additional_fields;

    }

    protected function _initialize_active() {
        add_action('leyka_donation_funded_status_changed', [$this, 'add_donor_to_unisender_list'],11,3); // TODO По стилю кода: пробелы между аргументами.
    }

    // TODO Это метод хотя, технически, публичный (иначе он не будет работать на хуке), но, по сути, внутренний и служебный (т.к. никогда и никем не будет вызываться напрямую). Названия таких хук-методов мы начинаем с "_", как и всех методов protected и private.
    public function add_donor_to_unisender_list($donation_id, $old_status, $new_status) {

        if($old_status !== 'funded' && $new_status === 'funded') {

            require_once LEYKA_PLUGIN_DIR.'extensions/unisender/lib/UnisenderApi.php';

            $apikey = leyka_options()->get_value($this->_id.'_api_key'); // TODO По стилю кода: 2 слова в названии переменной. Их нужно разделять подчёркиванием
            $donation = Leyka_Donations::get_instance()->get($donation_id);
            $lists_ids = str_replace(' ','', // TODO По стилю кода: пробелы между аргументами, перенос строки не нужен
                stripslashes(leyka_options()->get_value($this->_id.'_lists_ids')));
            $double_optin = leyka_options()->get_value($this->_id.'_donor_confirmation') === '1' ? 4 : 3; // TODO По стилю кода: 2 слова в названии переменной. Их нужно разделять подчёркиванием
            $donor_fields = ['email' => $donation->get_meta('leyka_donor_email')]; // TODO В клиентском коде параметры пож-я нужно получать/устанавливать не через get_meta(), а через __get()/__set() класса пож-я. Здесь, например, будет $donation->donor_email
            foreach (leyka_options()->opt($this->_id.'_donor_fields') as $field_name) { // TODO По стилю кода: лишний пробел после foreach

                if ( !empty($donation->get_meta('leyka_donor_'.$field_name)) ) { // TODO По стилю кода: лишний пробел после if
                    // TODO В клиентском коде параметры пож-я нужно получать/устанавливать не через get_meta(), а через __get()/__set() класса пож-я
                    $donor_fields[$field_name] = $donation->get_meta('leyka_donor_'.$field_name);
                } else {

                    $donation_additional_fields = $donation->get_meta('leyka_additional_fields'); // TODO В клиентском коде параметры пож-я нужно получать/устанавливать не через get_meta(), а через __get()/__set() класса пож-я
                    // TODO Нужна проверка, задано ли доп. поле с таким $field_name в составе доп. полей пож-я
                    $donor_fields[$field_name] = $donation_additional_fields[$field_name];

                }

                // TODO Вообще, по этому фрагменту давай созвонимся отдельно. Я дам пояснения про доп. поля.

            };

            $uni = new \Unisender\ApiWrapper\UnisenderApi($apikey);
            $uni->subscribe(['list_ids' => $lists_ids, 'fields' =>  $donor_fields, 'double_optin' => $double_optin]);

        }

    }

}

function leyka_add_extension_unisender() {
    leyka()->add_extension(Leyka_Unisender_Extension::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_extension_unisender');