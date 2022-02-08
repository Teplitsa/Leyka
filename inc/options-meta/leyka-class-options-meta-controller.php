<?php if( !defined('WPINC') ) die;

abstract class Leyka_Options_Meta_Controller extends Leyka_Singleton {

    protected static $_instance;

    protected $_options_meta = [];

    /** Can't make instance of the root/factory class, so overload the get_instance() */
    public static function get_instance(array $params = []) {
        return self::get_controller();
    }

    /**
     * @return Leyka_Options_Meta_Controller
     * @throws Exception
     */
    public static function get_controller() {

        $country_id = get_option('leyka_receiver_country');
        $country_id = $country_id && mb_strlen($country_id) > 1 ? $country_id : 'ru'; // Default country

        // Specific Options Meta Controller class:
        $file_path = apply_filters(
            'leyka_options_meta_controller_class_file_address',
            LEYKA_PLUGIN_DIR.'inc/options-meta/leyka-class-'.$country_id.'-options-meta-controller.php',
            $country_id
        );

        if($country_id === 'eu') { // Some countries options meta controllers are descendants of the Ru controller
            require_once LEYKA_PLUGIN_DIR.'inc/options-meta/leyka-class-ru-options-meta-controller.php';
        }

        if(file_exists($file_path)) {
            require_once($file_path);
        } else {
            throw new Exception(
                sprintf(
                    __("Options Meta Controllers Factory error: Can't find Controller script by given country ID (%s, %s)"),
                    $country_id, $file_path
                ), 600
            );
        }

        $class_name = 'Leyka_'.mb_ucfirst($country_id).'_Options_Meta_Controller';
        if(class_exists($class_name)) {
            return new $class_name();
        } else {
            throw new Exception(
                sprintf(__('Options Meta Controllers Factory error: wrong controller class given (%s)'), $class_name), 601
            );
        }

    }

    /**
     * A service function to get options meta as an array.
     *
     * @param string $options_group string|array Either options group ID, or an array of them.
     * @return array
     */
    protected function _get_options_meta($options_group = 'main') {

        if($options_group === 'all') {
            return $this->_get_options_meta(
                ['main', 'org', 'person', 'payments', 'currency', 'email', 'templates', 'analytics', 'terms', 'admin',]
            );
        } else if(is_array($options_group)) {

            $options_meta = [];
            foreach($options_group as $group) {
                $options_meta = array_merge($options_meta, $this->_get_options_meta($group));
            }

            return $options_meta;

        }

        $options_group = empty($options_group) ? false : trim($options_group);

        switch($options_group) {
            case 'main': return $this->_get_meta_main();
            case 'org': return $this->_get_meta_org();
            case 'person': return $this->_get_meta_person();
            case 'payments': return $this->_get_meta_payments();
            case 'currency': return $this->_get_meta_currency();
            case 'email':
            case 'emails':
               return $this->_get_meta_emails();
            case 'template':
            case 'templates':
               return $this->_get_meta_templates();
            case 'analytics': return $this->_get_meta_analytics();
            case 'terms': return $this->_get_meta_terms();
            case 'admin': return $this->_get_meta_admin();
            default:
               return $this->_get_unknown_group_options_meta($options_group);

        }

    }

    /**
     * Initialize & return option group metadata array by group ID (the options prefixes or some keywords most of the time).
     *
     * @param $options_group string|array Either options group ID, or an array of them, or 'all' value for all groups.
     * @return array The options metadata as an array of [option_id => metadata] pairs, or empty array if group is not found.
     */
    public function get_options_meta($options_group = 'main') {

        $this->_options_meta = $this->_get_options_meta($options_group);

        if( !is_array($options_group) ) {

            $this->_options_meta = apply_filters("leyka_{$options_group}_options_meta", $this->_options_meta);
            $this->_options_meta = apply_filters('leyka_options_meta', $this->_options_meta, $options_group);

        }

        return $this->_options_meta;

    }
    
    public function get_options_names($options_group = 'main') {
        return array_keys($this->_get_options_meta($options_group));
    }

    protected function _get_meta_main() {
        return [
            'receiver_country' => [
                'type' => 'select',
                'default' => leyka_get_default_receiver_country_id(),
                'title' => __('Select your country', 'leyka'),
                'required' => true,
                'list_entries' => leyka_get_countries_list(),
            ],
            'receiver_legal_type' => [
                'type' => 'radio',
                'title' => __('Donations receiver legal type', 'leyka'),
                'required' => true,
                'list_entries' => [
                    'legal' => ['title' => __('NGO - legal person', 'leyka'), 'comment' => '',],
                    'physical' => ['title' => __('Physical person', 'leyka'), 'comment' => '',],
                ],
                'description' => __('If you plan to collect funds as a physical person, please <a href="https://te-st.ru/2019/09/03/donations-to-individuals/" target="_blank">read this</a>.', 'leyka'),
                'default' => 'legal',
            ],
            'pm_available' => [ // The option is never displayed in UI via standard means
                'type' => 'multi_checkbox',
                'default' => ['text-text_box'],
                'title' => __('Payment methods available on donation forms', 'leyka'),
                'description' => __("Check out payment methods through that you'd want to receive a donation payments.", 'leyka'),
                'required' => true,
            ],
            'pm_order' => [ // The option is never displayed in UI via standard means
                'type' => 'text', // It's intentionally of text type - the option contains a serialized array
                'default' => '', // PM will be ordered just as their gateways were added
                'title' => __('Payment methods order on donation forms', 'leyka'),
            ],
            'commission' => [
                'type' => 'custom_gateways_commission', // Special option type
                'title' => __('Payment operators commission', 'leyka'),
            ],
            'additional_donation_form_fields_library' => [
                'type' => 'custom_additional_fields_library', // Special option type
                'title' => __('Additional fields library', 'leyka'),
                'field_classes' => ['additional-fields-settings'],
                'default' => [],
            ],
            'extensions_available' => [
                'type' => 'custom_extensions', // Special option type
                'title' => __('Extensions available', 'leyka'),
            ],
            'extensions_active' => [ // The option is never displayed in UI via standard means
                'type' => 'multi_checkbox',
                'default' => [],
                'title' => __('Extensions', 'leyka'),
            ],
            'success_page' => [
                'type' => 'select',
                'default' => leyka_get_default_success_page(),
                'title' => __('Page of successful donation', 'leyka'),
                'comment' => __('Select a page for donor to redirect to when payment is successful.', 'leyka'),
                'list_entries' => 'leyka_get_pages_list',
            ],
            'failure_page' => [
                'type' => 'select',
                'default' => leyka_get_default_failure_page(),
                'title' => __('Page of failed donation', 'leyka'),
                'comment' => __('Select a page for donor to redirect to when payment is failed for some reason.', 'leyka'),
                'list_entries' => 'leyka_get_pages_list',
            ],
            'donor_management_available' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Donor management available', 'leyka'),
                'comment' => __("Check to turn on the donors logging for all donations. It allows CRM functions and adds additional donors management pages to the plugin administation area.", 'leyka'),
                'short_format' => true,
            ],
            'donor_accounts_available' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Donor accounts available', 'leyka'),
                'comment' => __("Check to turn on the donors accounts features. It include donor's personal desktop pages and recurring donations management functions.", 'leyka'),
                'short_format' => true,
            ],
            'load_scripts_if_need' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Load plugin scripts only if necessary', 'leyka'),
                'comment' => __("Check this to load Leyka scripts and styles only on an applicable pages. If this box is unchecked, plugin will load it's scripts on every website page.", 'leyka'),
                'short_format' => true,
            ],
            'check_nonce_on_public_donor_actions' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __("On each donor's action, check if its submit request is unique", 'leyka'),
                'comment' => __('WARNING: unchecking this option may compromise yor website security. Unckeck it ONLY if your website uses caching plugins, and your donors systematically encounter nonce check errors while trying to submit a donation.', 'leyka'),
                'short_format' => true,
            ],
        ];
    }

    // Default implementation - common NGO org options fields:
    protected function _get_meta_org() {
        return [
            'org_full_name' => [
                'type' => 'text',
                'title' => __('The organization full name', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Eastern charity foundation of st. John the Merciful', 'leyka')),
            ],
            'org_short_name' => [
                'type' => 'text',
                'title' => __('The organization short name', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __("St. John's foundation", 'leyka')),
            ],
            'org_face_fio_ip' => [
                'type' => 'text',
                'title' => __('Full name of the organization head person', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('John Frederic Daw', 'leyka')),
            ],
            'org_contact_person_name' => [
                'type' => 'text',
                'title' => __('Full name of the organization contact person', 'leyka'),
                'description' => __('Contact person is a person who watch over Leyka installation, setup and plugin connection to the payment gateways.', 'leyka'),
                'required' => false,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('James Frederic Daw Jr.', 'leyka')),
            ],
            'org_face_fio_rp' => [
                'type' => 'text',
                'title' => __('Full name of a person representing the NGO, in genitive case', 'leyka'),
                'description' => __("Enter a person's full name in genitive case.", 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('John Frederic Dow (in genitive case, if it exists)', 'leyka')),
            ],
            'org_face_position' => [
                'type' => 'text',
                'title' => __("Organization head's position", 'leyka'),
                'default' => __('Director', 'leyka'),
                'description' => __('Enter an official position of a person representing the NGO.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s'), __('Director', 'leyka')),
            ],
            'org_address' => [
                'type' => 'text',
                'title' => __('The organization official address', 'leyka'),
                'description' => __('Enter the organization official address.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Malrose str., 4, Washington, DC, USA', 'leyka')),
            ],
            'org_bank_name' => [
                'type' => 'text',
                'title' => __('The organization bank name', 'leyka'),
                'description' => __('Enter a full name for the organization bank.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('First Columbia Credit Bank', 'leyka')),
            ],
            'org_bank_account' => [
                'type' => 'text',
                'title' => __('The organization bank account number', 'leyka'),
                'description' => __('Enter a bank account number of the organization', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s'), '40123840529627089012'),
                'mask' => "'mask': '9{20}'",
            ],
        ];
    }

    protected function _get_meta_person() { // Keywords: person_
        return [
            'person_full_name' => [
                'type' => 'text',
                'title' => __('Your full name', 'leyka'),
                'required' => false,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('John Frederic Daw', 'leyka')),
                'comment' => __('This info is needed only to use in your report documents on the site. We are not going to collect this info ourselves or pass it to the third party.', 'leyka'),
            ],
            'person_address' => [
                'type' => 'text',
                'title' => _x('Your official address', 'For a physical person', 'leyka'),
                'required' => false,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Malrose str., 4, Washington, DC, USA', 'leyka')),
                'comment' => 'This info is needed only to use in your report documents on the site. We are not going to collect this info ourselves or pass it to the third party.',
            ],
            'person_inn' => [
                'type' => 'text',
                'title' => _x('Your taxpayer individual number', 'For a physical person', 'leyka'),
                'required' => false,
                'placeholder' => sprintf(__('E.g., %s'), '4283256127'),
                'mask' => "'mask': '9{12}'",
            ],
            'person_bank_name' => [
                'type' => 'text',
                'title' => __('Your bank name', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('First Columbia Credit Bank', 'leyka')),
            ],
            'person_bank_account' => [
                'type' => 'text',
                'title' => _x('Your bank account number', 'For a physical person', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s'), '40123840529627089012'),
                'mask' => "'mask': '9{20}'",
            ],
            'person_bank_bic' => [
                'type' => 'text',
                'title' => _x('Your bank BIC number', 'For a physical person', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s'), '044180293'),
                'mask' => "'mask': '9{9}'",
            ],
            'person_bank_corr_account' => [
                'type' => 'text',
                'title' => _x('Your correspondent bank account number', 'For a physical person', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s'), '30101810270902010595'),
                'mask' => "'mask': '9{20}'",
            ],
        ];
    }

    protected function _get_meta_payments() {

        $main_currency_id = leyka_get_country_currency();
        $main_currencies = leyka_get_main_currencies_full_info();

        if(empty($main_currencies[$main_currency_id])) {
            return [];
        }

        $options = [
            "payments_single_tab_title" => [
                'type' => 'text',
                'default' => __('Single payments'),
                'title' => __('Donation form tab title', 'leyka'),
                'required' => true,
                'placeholder' => __('Single payments')
            ],
            "payments_single_amounts_options_".$main_currency_id => [
                'type' => 'custom_payments_amounts_options',
                'title' => __('Amounts options', 'leyka'),
                'field_classes' => ['payments-amounts-options'],
                'default' => [],
                'payment_type' => 'single'
            ],
            "payments_recurring_tab_title" => [
                'type' => 'text',
                'default' => __('Recurring payments'),
                'title' => __('Donation form tab title', 'leyka'),
                'required' => true,
                'placeholder' => __('Recurring payments')
            ],
            "payments_recurring_amounts_options_".$main_currency_id => [
                'type' => 'custom_payments_amounts_options',
                'title' => __('Amounts options', 'leyka'),
                'field_classes' => ['payments-amounts-options'],
                'default' => [],
                'payment_type' => 'recurring'
            ]
        ];

        return $options;

    }

    protected function _get_meta_currency() { // Keywords: currency

        $currencies_options_meta = [
            'currency_main' => [
                'type' => 'select',
                'default' => leyka_get_country_currency(),
                'title' => __('The main currency', 'leyka'),
                'required' => true,
                'description' => __("Select the main currency of your donations. Most of the times it's going to be the official currency of your country.", 'leyka'),
                'list_entries' => leyka_get_currencies_list(),
            ],
//            'auto_refresh_currency_rates' => [
//                'type' => 'checkbox',
//                'default' => '1',
//                'title' => __('Automatically refresh currency rates', 'leyka'),
//                'description' => __('Check to enable auto-refresh of currency rates. It will be performed every 24 hours and will require connection with http://cbr.ru website.', 'leyka'),
//            ],
//            'currency_rur2usd' => [
//                'type' => 'number',
//                'title' => __('Exchange rate', 'leyka'),
//                'description' => __('Please set the RUB to USD currency rate here.', 'leyka'),
//                'required' => true,
//                'placeholder' => __('Enter rate value (e.g., 70)', 'leyka'),
//                'length' => 6,
//            ],
        ];

        $currencies_defaults = leyka_get_main_currencies_full_info() + leyka_get_secondary_currencies_full_info();
        foreach($currencies_defaults as $currency_id => $data) {

            $currencies_options_meta = $currencies_options_meta + [
                "currency_{$currency_id}_label" => [
                    'type' => 'text',
                    'default' => $data['label'],
                    'title' => __('Currency label', 'leyka'),
                    'description' => sprintf(__('Please set the %s currency label here.', 'leyka'), $data['title']),
                    'required' => true,
                    'placeholder' => sprintf(__('E.g., %s', 'leyka'), $data['label']),
                    'length' => 6,
                ],
                "currency_{$currency_id}_min_sum" => [
                    'type' => 'number',
                    'default' => $data['min_amount'],
                    'title' => __('Minimum sum available', 'leyka'),
                    'description' => sprintf(__('Please set minimum sum available for %s donations.', 'leyka'), $data['title']),
                    'required' => true,
                    'placeholder' => sprintf(__('E.g., %s', 'leyka'), $data['min_amount']),
                    'length' => 8,
                ],
                "currency_{$currency_id}_max_sum" => [
                    'type' => 'number',
                    'default' => $data['max_amount'],
                    'title' => __('Maximum sum available', 'leyka'),
                    'description' => sprintf(__('Please set maximum sum available for %s donations.', 'leyka'), $data['title']),
                    'required' => true,
                    'placeholder' => sprintf(__('E.g., %s', 'leyka'), $data['max_amount']),
                    'length' => 8,
                ],
                "currency_{$currency_id}_flexible_default_amount" => [
                    'type' => 'number',
                    'default' => $data['flexible_default_amount'],
                    'title' => __('Default donation amount (for «flexible» donation type)', 'leyka'),
                    'description' => sprintf(__('Please, set a default amount of donation when %s selected as currency.', 'leyka'), $data['title']),
                    'required' => true,
                    'placeholder' => sprintf(__('E.g., %s', 'leyka'), $data['flexible_default_amount']),
                    'length' => 8,
                ],
                "currency_{$currency_id}_fixed_amounts" => [
                    'type' => 'text',
                    'default' => $data['fixed_amounts'],
                    'title' => __('Possible donation amounts (for «fixed» donation type)', 'leyka'),
                    'description' => sprintf(__('Please, set possible amounts of donation in %s when «fixed» donation type is selected. Only an integer non-negative values, separated with commas.', 'leyka'), $data['title']),
                    'required' => true,
                    'placeholder' => sprintf(__('E.g., %s', 'leyka'), $data['fixed_amounts']),
                    'length' => 75,
                ],
            ];

        }

        return $currencies_options_meta;

    }

    protected function _get_meta_emails() { // Keywords: email, notify

        $placeholders_controls = '<div class="placeholders-help-actions">
    <a href="#" class="inner hide-available-tags">Свернуть доступные теги</a>
    <a href="#" class="inner show-available-tags">Посмотреть доступные теги</a>
    <a href="#" class="inner restore-original-doc">Вернуть первоначальный текст</a>
</div>';

        $email_placeholders = '<span class="placeholders-help">'
            .apply_filters('leyka_email_placeholders_help_list_content', '<span class="item">
        <code>#SITE_NAME#</code><span class="description">Название сайта</span>
    </span>
    <span class="item">
        <code>#SITE_EMAIL#</code><span class="description">Email сайта</span>
    </span>
    <span class="item">
        <code>#ORG_NAME#</code><span class="description">Полное официальное название организации</span>
    </span>
    <span class="item">
        <code>#ORG_SHORT_NAME#</code><span class="description">Сокращённое название организации</span>
    </span>
    <span class="item">
        <code>#DONATION_ID#</code><span class="description">Идентификатор текущего пожертвования</span>
    </span>
    <span class="item">
        <code>#DONATION_TYPE#</code><span class="description">Тип пожертвования</span>
    </span>
    <span class="item">
        <code>#DONOR_NAME#</code><span class="description">Имя донора</span>
    </span>
    <span class="item">
        <code>#DONOR_EMAIL#</code><span class="description">Email донора</span>
    </span>
    <span class="item">
        <code>#DONOR_COMMENT#</code><span class="description">Комментарий донора к пожертвованию</span>
    </span>
    <span class="item">
        <code>#SUM#</code><span class="description">Полная сумма пожертвования (без учёта комиссий)</span>
    </span>
    <span class="item">
        <code>#PAYMENT_METHOD_NAME#</code><span class="description">Название способа оплаты</span>
    </span>
    <span class="item">
        <code>#CAMPAIGN_NAME#</code><span class="description">Кампания, на которую было сделано пожертвование</span>
    </span>
    <span class="item">
        <code>#CAMPAIGN_URL#</code><span class="description">Адрес страницы кампании, на которую было сделано пожертвование</span>
    </span>
    <span class="item">
        <code>#CAMPAIGN_TARGET#</code><span class="description">Официальная цель пожертвования (см. настройки кампании, опция «заголовок для платёжной системы»)</span>
    </span>
    <span class="item">
        <code>#PURPOSE#</code><span class="description">Название кампании для платежных систем</span>
    </span>
    <span class="item">
        <code>#DATE#</code><span class="description">Дата пожертвования</span>
    </span>
    <span class="item">
        <code>#RECURRING_SUBSCRIPTION_CANCELLING_LINK#</code><span class="description">Отменить рекуррентную подписку донора</span>
    </span>
    <span class="item">
        <code>#DONOR_ACCOUNT_LOGIN_LINK#</code><span class="description">Ссылка на активацию/вход в Личный кабинет донора</span>
    </span>')
        .'</span>'
        .$placeholders_controls;

        $campaign_target_reaching_email_placeholders = "<span class='placeholders-help'>
    <code>#SITE_NAME#</code> — ".__('a website title', 'leyka')."<br>
    <code>#ORG_NAME#</code> — ".__('an organization official title', 'leyka')."<br>
    <code>#DONOR_NAME#</code> — ".__('a donor name', 'leyka')."<br>
    <code>#DONOR_EMAIL#</code> — ".__('a donor email', 'leyka')."<br>
    <code>#SUM#</code> — ".__('a full donations amount (without payment commission)', 'leyka')."<br>
    <code>#CAMPAIGN_NAME#</code> — ".__('a campaign to which donation was made', 'leyka')."<br>
    <code>#CAMPAIGN_TARGET#</code> — ".__('a campaign target amount', 'leyka')."<br>
    <code>#PURPOSE#</code> — ".__('a campaign title for payment systems (see campaign settings)', 'leyka')."<br>
</span>".$placeholders_controls;

        return [
            'tech_support_email' => [
                'type' => 'email',
                'title' => __('Website technical support email', 'leyka'),
                'description' => __('E-mail that you want to use to collect technical support requests from the donors.', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s'), 'techsupport@email.com'),
            ],
            'email_from_name' => [
                'type' => 'text',
                'default' => get_bloginfo('name'),
                'title' => __('Sender', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Daisy Foundation website', 'leyka')),
                'comment' => __('What your donors will see in the "from whom" email field. For the most of the cases, your organization name will do fine.', 'leyka'),
            ],
            'email_from' => [
                'type' => 'text',
                'default' => leyka_get_default_email_from(),
                'title' => __('Sender email', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'donations@daisyfoundation.org'),
                'comment' => __('What your donors will see in the "from email" field. For the most of the cases, your organization contact email will do fine.', 'leyka'),
            ],
            'email_thanks_title' => [
                'type' => 'text',
                'default' => __('Thank you for your donation!', 'leyka'),
                'title' => __('A title of after-donation notice sended to a donor', 'leyka'),
                'comment' => __('A title of the notification (or thankful) email with donation data that would be sended to each donor right after his donation is made.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Daisy Foundation thanks you for your kindness', 'leyka')),
            ],
            'email_thanks_text' => [
                'type' => 'rich_html',
                'default' => __('Hello, #DONOR_NAME#!<br><br>You made a #SUM# donation to the following charity campaign: #CAMPAIGN_NAME#, using #PAYMENT_METHOD_NAME#.<br><br>Sincerely thank you,<br>#ORG_NAME#', 'leyka'),
                'title' => __('Email text', 'leyka'),
                'comment' => __('An email text that your donor will see. You may use the special tags in the text.', 'leyka'),
                'description' => $email_placeholders,
                'required' => true,
                'field_classes' => ['type-rich_html'],
            ],
            'email_recurring_init_thanks_title' => [
                'type' => 'text',
                'default' => __('Thank you for your support!', 'leyka'),
                'title' => __('A title of an initial recurring donation notice sent to a donor', 'leyka'),
                'comment' => __('A title of a notification email with donation data that would be sended to each donor on each rebill donation.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Daisy Foundation thanks you for your kindness', 'leyka')),
            ],
            'email_recurring_init_thanks_text' => [
                'type' => 'rich_html',
                'default' => __('Hello, #DONOR_NAME#!<br><br>We just took a #SUM# from your account as a regular donation to the campaign «#CAMPAIGN_NAME#», using #PAYMENT_METHOD_NAME#.<br><br>#DONOR_ACCOUNT_LOGIN_LINK#<br><br>If you, regretfully, wish to stop future regular donations to this campaign, please #RECURRING_SUBSCRIPTION_CANCELLING_LINK#.<br><br>Sincerely thank you,<br>#ORG_NAME#', 'leyka'),
                'title' => __('A text of a recurring subscription donation notice sent to a donor', 'leyka'),
                'comment' => __('A text of the notification email that would be sended to each donor on each rebill donation.', 'leyka'),
                'description' => $email_placeholders,
                'required' => true,
                'field_classes' => ['type-rich_html'],
            ],
            'email_recurring_ongoing_thanks_title' => [
                'type' => 'text',
                'default' => __('Thank you for your unwavering support!', 'leyka'),
                'title' => __('A title of an after-rebill donation notice for a donor', 'leyka'),
                'comment' => __('A title of a donor notification email with donation data that will be sent on each recurring auto-payment.', 'leyka'),
                'required' => true,
            ],
            'email_recurring_ongoing_thanks_text' => [
                'type' => 'rich_html',
                'default' => __('Hello, #DONOR_NAME#!<br><br>We just took a #SUM# from your account as a regular donation to the campaign «#CAMPAIGN_NAME#», using #PAYMENT_METHOD_NAME#.<br><br>#DONOR_ACCOUNT_LOGIN_LINK#<br><br>If you, regretfully, wish to stop future regular donations to this campaign, please #RECURRING_SUBSCRIPTION_CANCELLING_LINK#.<br><br>Sincerely thank you,<br>#ORG_NAME#', 'leyka'),
                'title' => __('A text of after-rebill donation notice sent to a donor', 'leyka'),
                'comment' => __('A text of the notification email that would be sended to each donor on each rebill donation.', 'leyka'),
                'description' => $email_placeholders,
                'required' => true,
                'field_classes' => ['type-rich_html'],
            ],
            'send_donor_thanking_emails' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Send the emails', 'leyka'),
                'comment' => __('Check to send a thankful email to a donor on each funded donation', 'leyka'),
                'short_format' => true,
            ],
            'send_donor_thanking_emails_on_recurring_init' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Send the emails', 'leyka'),
                'comment' => __('Check to send a thankful email to a donor on each recurring donations subscription', 'leyka'),
                'short_format' => true,
            ],
            'send_donor_thanking_emails_on_recurring_ongoing' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Send the emails', 'leyka'),
                'comment' => __('Check to send a thankful email to a donor on each non-initial recurring donation', 'leyka'),
                'short_format' => true,
            ],
            'send_recurring_canceling_donor_notification_email' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Send the emails', 'leyka'),
                'comment' => __('Check to send an email to a donor if he canceled a recurring subscription', 'leyka'),
                'short_format' => true,
            ],
            'send_donor_emails_on_campaign_target_reaching' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Send the emails', 'leyka'),
                'comment' => __('Check to send a special thankful email to each donor when campaign target reached', 'leyka'),
                'short_format' => true,
            ],
            'recurring_canceling_donor_notification_emails_title' => [
                'type' => 'text',
                'default' => __('Thank you for being with us!', 'leyka'),
                'title' => __('A title of a notification email', 'leyka'),
                'description' => __('Enter the email title.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Thank you for being with us!', 'leyka')),
            ],
            'recurring_canceling_donor_notification_emails_text' => [
                'type' => 'rich_html',
                'default' => __('Hello, #DONOR_NAME#!<br><br>You canceled the recurring donations on the "#CAMPAIGN_NAME#" campaign. We thank you for support, and we will miss it greatly.<br><br>Recurring donations are very important for us, as they allow us to plan stable future works. You may subscribe to monthly recurring donations again on the <a href="#CAMPAIGN_URL#">campaign page</a>.<br><br>Sincerely thank you for your support,<br>#ORG_NAME#', 'leyka'),
                'title' => __('Notification email text', 'leyka'),
                'description' => $email_placeholders,
                'required' => true,
                'field_classes' => ['type-rich_html'],
            ],
            'recurring_canceling_donor_notification_emails_defer_by' => [
                'type' => 'number',
                'default' => 7,
                'title' => __('Number of days to defer emails sending', 'leyka'),
                'comment' => __('Set a time (in days) after which the notification email will be sent to donor.', 'leyka'),
                'required' => false,
                'min' => 0,
                'step' => 1,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 7),
            ],
            'non_init_recurring_donor_registration_emails_title' => [
                'type' => 'text',
                'default' => __('Your personal account was created', 'leyka'),
                'title' => __('A title of a notification email', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Your personal account was created', 'leyka')),
            ],
            'non_init_recurring_donor_registration_emails_text' => [
                'type' => 'rich_html',
                'default' => __('Hello, #DONOR_NAME#!<br><br>We created a personal account for you to manage your donations.<br><br>#DONOR_ACCOUNT_LOGIN_LINK#<br><br>If you do not wish to use it, just ignore this email.<br><br>Sincerely thank you,<br>#ORG_NAME#', 'leyka'),
                'title' => __('Notification email text', 'leyka'),
                'description' => $email_placeholders,
                'required' => true,
                'field_classes' => ['type-rich_html'],
            ],
            'email_campaign_target_reaching_title' => [
                'type' => 'text',
                'default' => __('Thanks to you, the campaign succeeded!', 'leyka'),
                'title' => __('A title of an email notification sent to each donor when campaign target reached', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Thanks to you, the campaign succeeded!', 'leyka')),
            ],
            'email_campaign_target_reaching_text' => [
                'type' => 'rich_html',
                'default' => __("Hello, #DONOR_NAME#!<br><br>You've donated #SUM# totally to the campaign: «#CAMPAIGN_NAME#».<br><br>We're glad to tell that just now this campaign successfully finished!<br><br>We heartfully thank you for your support,<br>#ORG_NAME#", 'leyka'),
                'title' => __('A text of a notification email sent to each donor when campaign target reached', 'leyka'),
                'comment' => __('A text of a notification email sent to each donor when campaign target reached.', 'leyka'),
                'description' => $campaign_target_reaching_email_placeholders,
                'required' => true,
                'field_classes' => ['type-rich_html'],
            ],
            'notify_donations_managers' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Send emails on single donations', 'leyka'),
                'comment' => __('Check to notify the website personnel (donations managers) of each incoming donation', 'leyka'),
                'short_format' => true,
            ],
            'notify_managers_on_recurrents' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Send emails on recurring donations', 'leyka'),
                'comment' => __('Check to notify the website personnel (donations managers) of each incoming recurrent donation', 'leyka'),
                'short_format' => true,
            ],
            'donations_managers_emails' => [
                'type' => 'text',
                'default' => get_bloginfo('admin_email').',',
                'title' => __('A comma-separated emails to notify of incoming donation', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('admin@daisyfoundation.org,yourmail@domain.com', 'leyka')),
            ],
            'email_notification_title' => [
                'type' => 'text',
                'default' => __('New donation incoming', 'leyka'),
                'title' => __('A title of new donation notification email', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('New donation incoming', 'leyka')),
            ],
            'email_notification_text' => [
                'type' => 'rich_html',
                'default' => __('Hello!<br><br>A new donation has been made on a #SITE_NAME#:<br><br>Campaign: #CAMPAIGN_NAME#.<br>Donation purpose: #PURPOSE#<br>Amount: #SUM#.<br>Payment method: #PAYMENT_METHOD_NAME#.<br>Date: #DATE#<br><br>Your Leyka', 'leyka'),
                'title' => __('A text of after-donation notification sended to a website personnel', 'leyka'),
                'comment' => __('A text of the notification email that would be sended to each email stated before right after donation is made.', 'leyka'),
                'description' => $email_placeholders,
                'field_classes' => ['type-rich_html'],
            ],
            'notify_tech_support_on_failed_donations' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Send emails to the website tech. support on failed donations', 'leyka'),
                'comment' => sprintf(
                    __('Check to notify the website technical support (see the "website technical support email" option) of each failed donation. Current tech. support email: <b>%s</b>', 'leyka'),
                    leyka_get_website_tech_support_email()
                ),
                'short_format' => true,
            ],
            'notify_donors_on_failed_donations' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Send emails to donors on failed donations', 'leyka'),
                'short_format' => true,
            ],
            'donation_error_donor_notification_title' => [
                'type' => 'text',
                'default' => __('Your donation failed', 'leyka'),
                'title' => __('A title of a failed donation notification email', 'leyka'),
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Your donation failed', 'leyka')),
            ],
            'donation_error_donor_notification_text' => [
                'type' => 'rich_html',
                'default' => __('Hello!<br><br>You just made a donation on #SITE_NAME# website, and it failed for some reason.<br><br>Campaign: #CAMPAIGN_NAME#.<br>Donation purpose: #PURPOSE#<br>Amount: #SUM#.<br>Payment method: #PAYMENT_METHOD_NAME#.<br>Date: #DATE#<br><br>Please, report this to website tech. support and try donating again later.<br><br>Sincerely thank you,<br>#ORG_NAME#', 'leyka'),
                'title' => __('A text of a failed donation notification email', 'leyka'),
                'comment' => __('A text of the notification email that would be sended to each email stated before right after donation is made.', 'leyka'),
                'description' => $email_placeholders,
                'field_classes' => ['type-rich_html'],
            ],
        ];

    }

    protected function _get_meta_templates() { // Keywords: template, display, show, widget, revo
        return [
            'donation_form_template' => [
                'type' => 'radio',
                'default' => 'star',
                'title' => __('Select a default template for all your donation forms', 'leyka'),
                'description' => __('Select one of the form templates.', 'leyka'),
                'required' => true,
                'list_entries' => 'leyka_get_form_templates_list',
            ],
            'allow_deprecated_form_templates' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Allow usage of the old donation forms templates', 'leyka'),
                'short_format' => true,
            ],
            'donation_sum_field_type' => [ // donation_sum_field_type -> templates_donation_sum_field_type
                'type' => 'radio',
                'default' => 'mixed',
                'title' => __('Select a type of the sum field for all your donation forms', 'leyka'),
                'description' => __('Select a type of the sum field. «Fixed» means a set of stable sum variants, while «flexible» is a free input field.', 'leyka'),
                'required' => true,
                'list_entries' => [
                    'flexible' => __('Flexible', 'leyka'),
                    'fixed' => __('Fixed', 'leyka'),
                    'mixed' => __('Fixed sum variants + flexible field', 'leyka')
                ],
            ],
            'donation_submit_text' => [ // donation_submit_text -> templates_donation_submit_text
                'type' => 'text',
                'default' => __('Donate', 'leyka'),
                'title' => __('Label of the button to submit a donation form', 'leyka'),
                'description' => __('Enter the text for a submit buttons on a donation forms.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Donate', 'leyka')),
            ],
            'do_not_display_donation_form' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Do not display payment form on campaign page automatically', 'leyka'),
                'description' => __("Do not display payment form", 'leyka'),
            ],
            'scale_widget_place' => [ // scale_widget_place -> templates_scale_widget_place
                'type' => 'radio',
                'default' => 'top',
                'title' => __('Select where Target completion widget will be placed at campaign pages', 'leyka'),
                'required' => true,
                'list_entries' => [
                    'top' => __('Above page content', 'leyka'),
                    'bottom' => __('Below page content', 'leyka'),
                    'both' => __('Both', 'leyka'),
                    '-' => __('Nowhere', 'leyka'),
                ],
            ],
            'donations_history_under_forms' => [ // donations_history_under_forms -> show_donations_history_under_forms
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Donations history widget below donation forms', 'leyka'),
                'description' => __('Display donations history below donation forms', 'leyka'),
            ],
            'show_campaign_sharing' => [
                'type' => 'checkbox',
                'default' => 1,
                'title' => __('Campaign sharing widget below donation forms', 'leyka'),
                'description' => __('Display sharing widget below donation forms', 'leyka'),
            ],
            'show_success_widget_on_success' => [
                'type' => 'checkbox',
                'default' => 1,
                'title' => __('Show an email subscription widget on the successful donation page', 'leyka'),
                'description' => __('Show a subscription form on the successful donation page', 'leyka'),
            ],
            'show_failure_widget_on_failure' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Show a failure notification widget on the donation failure page', 'leyka'),
                'description' => __('Display a failure notification widget on the donation failure page', 'leyka'),
            ],
            'revo_template_slider_max_sum' => [
                'type' => 'number',
                'default' => 3000,
                'title' => __('Maximum sum available for slider', 'leyka'),
                'description' => __('Please set the maximum sum available for slider control.', 'leyka'),
                'required' => true,
                'placeholder' => 3000,
                'min' => 1,
                'max' => 100000,
                'step' => 1,
            ],
            'revo_template_show_donors_list' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Display donors names list on donation forms', 'leyka'),
                'description' => __('Check to display a list of donors names on all donation forms', 'leyka'),
            ],
            'show_donation_comment_field' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Display a comment textarea field on donation forms', 'leyka'),
                'description' => __("Display comment field on donation form", 'leyka'),
            ],
            'donation_comment_max_length' => [ // donation_comment_max_length -> templates_donation_comment_max_length
                'type' => 'number',
                'default' => '',
                'title' => __('The maximum length of a donation comment value', 'leyka'),
                'description' => __('Set the maximum number of symbols allowed for donation comments. You may set "0" for the unlimited values length.', 'leyka'),
                'placeholder' => __('Maximum number of symbols', 'leyka'),
                'min' => 1,
                'step' => 1,
            ],
            'show_donation_comments_in_frontend' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Display donation comments in frontend', 'leyka'),
                'comment' => __('Check to show donors comments in the website frontend (e.g. in donation lists widgets)', 'leyka'),
                'short_format' => true,
            ],
            'revo_template_show_thumbnail' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Display a thumbnail in inline page blocks', 'leyka'),
                'description' => __('Check if you need to show a campaign thumbnail in inline page blocks.', 'leyka'),
            ],
            'recurring_donation_benefits_text' => [ // recurring_donation_benefits_text -> templates_recurring_donation_benefits_text
                'type' => 'textarea',
                'default' => __('We will be happy with a small but monthly help, this gives us confidence in the future and the ability to plan our activities.', 'leyka'),
                'title' => __('Explanation of benefits of regular donations', 'leyka'),
                'required' => false,
            ],
            'revo_donation_complete_button_text' => [
                'type' => 'text',
                'default' => __('Complete donation', 'leyka'),
                'title' => __('Label of the button to complete a donation', 'leyka'),
                'description' => __('Enter the text for a complete donation buttons on a donation forms.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Complete the donation', 'leyka')),
            ],
            'revo_thankyou_text' => [
                'type' => 'text',
                'default' => __("Thank you! We appreciate your help! Let's stay in touch.", 'leyka'),
                'title' => __('Text on "Thank you" screen', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __("Thank you! We appreciate your help! Let's stay in touch.", 'leyka')),
            ],
            'revo_thankyou_email_result_text' => [
                'type' => 'text',
                'default' => __('We will inform you about the result by email', 'leyka'),
                'title' => __('Text on "Donation process complete" page', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('We will inform you about the result by email', 'leyka')),
            ],
            'widgets_total_amount_usage' => [
                'type' => 'radio',
                'default' => 'display-total',
                'title' => __('Total amount display in plugin widgets and shortcodes', 'leyka'),
                'required' => true,
                'list_entries' => [
                    'none' => __('Do not display total amount', 'leyka'),
                    'display-total' => __('Display total amount values with original values', 'leyka'),
                    'display-total-only' => __('Display only total amount values', 'leyka'),
                ],
            ],
        ];
    }

    protected function _get_meta_analytics() { // Keywords: _ua, gtm_
        return [
            'use_gtm_ua_integration' => [
                'type' => 'radio',
                'title' => __('Google Tag Manager & Universal Analytics integration', 'leyka'),
                'list_entries' => [
                    '-' => __("Don't use UA e-commerce integration", 'leyka'),
                    'simple' => __('Use the GTM & simple UA e-commerce integration', 'leyka'),
                    'enchanced' => __('Use the UA enchanced e-commerce integration', 'leyka'),
                    'enchanced_ua_only' => __('Connect directly to the GUA enchanced e-commerce tracking', 'leyka'),
                ],
                'default' => '-',
            ],
            'gtm_ua_enchanced_events' => [
                'type' => 'multi_checkbox',
                'default' => ['purchase',],
                'title' => __('Google UA enchanced mode events support', 'leyka'),
                'comment' => __('Check the events types that should be triggered in donation process. The "eec.checkout" event will always trigger.<br><br>Be advised: this option is relevant only for GTM & UA enchanced e-commerce integration.', 'leyka'),
                'list_entries' => ['detail' => __('EEC.detail', 'leyka'), 'purchase' => __('EEC.purchase', 'leyka'),],
            ],
            'gtm_ua_tracking_id' => [
                'type' => 'text',
                'title' => __('Google UA Tracking ID ', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'UA-12345678-90'),
            ],
        ];
    }

    protected function _get_meta_terms() { // Keywords: terms

        $placeholders_controls = "<div class='placeholders-help-actions'>
    <a href='#' class='inner hide-available-tags'>Свернуть доступные теги</a>
    <a href='#' class='inner show-available-tags'>Посмотреть доступные теги</a>
    <a href='#' class='inner restore-original-doc'>Вернуть первоначальный текст</a>
</div>";

        $terms_placeholders = "<span class='placeholders-help'>
    <span class='item'>
        <code>#SITE_URL#</code><span class='description'>Адрес сайта</span>
    </span>
    <span class='item'>
        <code>#SITE_NAME#</code><span class='description'>Название сайта</span>
    </span>
    <span class='item'>
        <code>#ADMIN_EMAIL#</code><span class='description'>Email администратора сайта</span>
    </span>
    <span class='item'>
        <code>#LEGAL_NAME#</code><span class='description'>Полное официальное название организации</span>
    </span>
    <span class='item'>
        <code>#LEGAL_FACE#</code><span class='description'>Имя руководителя организации</span>
    </span>
    <span class='item'>
        <code>#LEGAL_FACE_POSITION#</code><span class='description'>Название должности руководителя организации</span>
    </span>
    <span class='item'>
        <code>#LEGAL_ADDRESS#</code><span class='description'>Юридический адрес организации</span>
    </span>
    <span class='item'>
        <code>#STATE_REG_NUMBER#</code><span class='description'>ОГРН организации</span>
    </span>
    <span class='item'>
        <code>#KPP#</code><span class='description'>КПП организации</span>
    </span>
    <span class='item'>
        <code>#INN#</code><span class='description'>ИНН организации</span>
    </span>
    <span class='item'>
        <code>#BANK_ACCOUNT#</code><span class='description'>Номер банковского счёта организации</span>
    </span>
    <span class='item'>
        <code>#BANK_NAME#</code><span class='description'>Название банка организации</span>
    </span>
    <span class='item'>
        <code>#BANK_BIC#</code><span class='description'>БИК банка организации</span>
    </span>
    <span class='item'>
        <code>#BANK_CORR_ACCOUNT#</code><span class='description'>Номер корр. счёта банка организации</span>
    </span>
    <span class='item'>
        <code>#TERMS_PAGE_URL#</code><span class='description'>Адрес страницы с текстом условий Договора оферты</span>
    </span>
</span>".$placeholders_controls;

        $pd_terms_placeholders = "<span class='placeholders-help'>
    <span class='item'>
        <code>#SITE_URL#</code><span class='description'>Адрес сайта</span>
    </span>
    <span class='item'>
        <code>#SITE_NAME#</code><span class='description'>Название сайта</span>
    </span>
    <span class='item'>
        <code>#ADMIN_EMAIL#</code><span class='description'>Email администратора сайта</span>
    </span>
    <span class='item'>
        <code>#LEGAL_NAME#</code><span class='description'>Полное официальное название организации</span>
    </span>
    <span class='item'>
        <code>#LEGAL_FACE#</code><span class='description'>Имя руководителя организации</span>
    </span>
    <span class='item'>
        <code>#LEGAL_FACE_POSITION#</code><span class='description'>Название должности руководителя организации</span>
    </span>
    <span class='item'>
        <code>#LEGAL_ADDRESS#</code><span class='description'>Юридический адрес организации</span>
    </span>
    <span class='item'>
        <code>#PD_TERMS_PAGE_URL#</code><span class='description'>Адрес страницы с текстом условий Соглашения о персональных данных</span>
    </span>
</span>".$placeholders_controls;

        return [
            'agree_to_terms_needed' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('To donate, donor must agree to Terms of Service', 'leyka'),
                'description' => __("Check if you must receive donor's agreement with some terms before his donation.", 'leyka'),
            ],
            'agree_to_terms_text_text_part' => [
                'type' => 'text',
                'default' => __('I agree with', 'leyka'),
                'title' => __('Terms acception checkbox label - the first (text) part', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('I agree with', 'leyka')),
            ],
            'agree_to_terms_text_link_part' => [
                'type' => 'text',
                'default' => __('Terms of the donation service', 'leyka'),
                'title' => __('Terms acception checkbox label - the second (link) part', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Terms of the donation service', 'leyka')),
            ],
            'terms_of_service_text' => [
                'type' => 'rich_html',
                'default' => __('Organization Terms of service text', 'leyka'),
                'title' => __('A text of Terms of donation service', 'leyka'),
                'required' => true,
                'description' => $terms_placeholders,
                'field_classes' => ['type-rich_html'],
            ],
            'person_terms_of_service_text' => [
                'type' => 'rich_html',
                'default' => __('Person Terms of service text'),
                'title' => __('A text of Terms of donation service', 'leyka'),
                'required' => true,
                'field_classes' => ['type-rich_html'],
            ],
            'terms_agreed_by_default' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __("Donor is agreed with Terms of Service by default", 'leyka'),
                'description' => __('When donor sees a donation form, Terms of Service agreement checkbox is already checked.', 'leyka'),
            ],
            'terms_of_service_page' => [
                'type' => 'select',
                'default' => leyka_get_default_service_terms_page(),
                'title' => __("Page of terms of service text", 'leyka'),
                'description' => __('Select a page with terms of the donation service full text.', 'leyka'),
                'list_entries' => 'leyka_get_pages_list',
            ],
            'agree_to_terms_link_action' => [
                'type' => 'radio',
                'default' => 'popup',
                'title' => __('Click on Terms of service link...', 'leyka'),
                'required' => true,
                'list_entries' => [
                    'popup' => __('Opens the Terms text in popup window', 'leyka'),
                    'page' => __('Opens the page of Terms text', 'leyka'),
                ],
            ],
            'agree_to_pd_terms_needed' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('To donate, donor must agree to Terms of personal data usage', 'leyka'),
                'description' => __("Check if you should have donor's agreement with some terms regarding his personal data usage.", 'leyka'),
            ],
            'agree_to_pd_terms_text_text_part' => [
                'type' => 'text',
                'default' => __('I agree with the processing of', 'leyka'),
                'title' => __('Personal data usage Terms checkbox label - the first (text) part', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('I agree with', 'leyka')),
            ],
            'agree_to_pd_terms_text_link_part' => [
                'type' => 'text',
                'default' => _x('my personal data', 'In instrumental case', 'leyka'),
                'title' => __('Personal data usage Terms checkbox label - the second (link) part', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), __('Terms of personal data usage', 'leyka')),
            ],
            'pd_terms_text' => [
                'type' => 'rich_html',
                'default' => __('Terms of personal data usage full text. Use <br> for line-breaks.', 'leyka'),
                'title' => __('A text of personal data usage Terms', 'leyka'),
                'required' => true,
                'description' => $pd_terms_placeholders,
                'field_classes' => ['type-rich_html'],
            ],
            'person_pd_terms_text' => [
                'type' => 'rich_html',
                'default' => __('Terms of personal data usage full text. Use <br> for line-breaks.', 'leyka'),
                'title' => __('A text of personal data usage Terms', 'leyka'),
                'required' => true,
            ],
            'pd_terms_agreed_by_default' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Donor is agreed with personal data usage Terms by default', 'leyka'),
                'description' => __('When donor sees a donation form, personal data usage Terms agreement checkbox is already checked.', 'leyka'),
            ],
            'pd_terms_page' => [
                'type' => 'select',
                'default' => leyka_get_default_pd_terms_page(),
                'title' => __("Page of personal data usage terms and policy", 'leyka'),
                'description' => __('Select a page with personal data usage terms and policy full text.', 'leyka'),
                'list_entries' => 'leyka_get_pages_list',
            ],
            'agree_to_pd_terms_link_action' => [
                'type' => 'radio',
                'default' => 'popup',
                'title' => __('Click on personal data usage Terms link...', 'leyka'),
                'required' => true,
                'list_entries' => [
                    'popup' => __('Opens the Terms text in popup window', 'leyka'),
                    'page' => __('Opens the page of Terms text', 'leyka'),
                ],
            ],
        ];
    }

    protected function _get_meta_admin() { // Keywords: admin, plugin
        return [
            'admin_donations_list_amount_display' => [
                'type' => 'radio',
                'default' => 'amount-column',
                'title' => __('Total amount display on the admin donations list page', 'leyka'),
                'required' => true,
                'list_entries' => [
                    'none' => __('Do not display total amount', 'leyka'),
                    'amount-column' => __('Display total amount in the amount column, with original amount value', 'leyka'),
                    'separate-column' => __('Display total amount in a separate column', 'leyka'),
                ],
            ],
            'send_plugin_stats' => [
                'type' => 'radio',
                'default' => 'y',
                'title' => __('Anonymous plugin usage data collection', 'leyka'),
                'required' => true,
                'list_entries' => [
                    'y' => __('Yes, I agree to send the data', 'leyka'),
                    'n' => __("No, I don't want to share the data", 'leyka'),
                ],
            ],
            'delete_plugin_options' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Remove all plugin settings upon plugin uninstall', 'leyka'),
                'description' => __('WARNING: checking this checkbox will cause loss of all Leyka settings upon plugin uninstall. Please, proceed with caution.', 'leyka'),
            ],
            'delete_plugin_data' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Remove all plugin data upon plugin uninstall', 'leyka'),
                'description' => __('WARNING: checking this checkbox will cause loss of ALL Leyka data, including all donation and campaign transaction history, upon plugin uninstall. Please, proceed with caution.', 'leyka'),
            ],
            'donors_data_editable' => [ // donors_data_editable -> admin_donors_data_editable
                'type' => 'checkbox',
                'title' => __("You can edit donors' data for all donation types", 'leyka'),
                'comment' => __("Check to allow donation administrators and managers to edit donors' data for all donations - even for non-correctional ones.", 'leyka'),
                'short_format' => true,
            ],
            'plugin_demo_mode' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Demo mode on', 'leyka'),
                'comment' => __('Check to turn on the plugin demonstration mode. While in it, no emails will be sent to the payment gateways.', 'leyka'),
                'short_format' => true,
            ],
            'plugin_debug_mode' => [
                'type' => 'checkbox',
                'default' => false,
                'title' => __('Debugging mode on', 'leyka'),
                'comment' => __('Check to turn on the plugin debugging mode. Warning: some of the logic checks will not be performed if the mode is on. Please, use the option with caution.', 'leyka'),
                'short_format' => true,
            ],
            'plugin_stats_sync_enabled' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('The plugin usage statistics data synchronization is on', 'leyka'),
                'short_format' => true,
            ],
//            'data_export_files_encoding' => [
//                'type' => 'select',
//                'default' => 'CP1251//TRANSLIT//IGNORE',
//                'title' => __('Data export files encoding', 'leyka'),
//                'comment' => __('An encoding for all data export files (e.g, donations CSV lists).', 'leyka'),
//                'list_entries' => [
//                    'UTF-8' => __('UTF-8', 'leyka'),
//                    'CP1251//TRANSLIT//IGNORE' => __('Windows-1251', 'leyka'),
//                ],
//            ],
            'platform_signature_on_form_enabled' => [
                'type' => 'checkbox',
                'default' => true,
                'title' => __('Add platform signature to donation form', 'leyka'),
                'short_format' => true
            ],
        ];
    }

    // The default implementation to get some country-specific options group:
    protected function _get_unknown_group_options_meta($options_group) {
        return [];
    }

    /** @todo */
//    public function get_terms_of_service_placeholders() {
//        return [
//            '#LEGAL_NAME#' => [
//                'title' => __('Org legal name', 'leyka'),
//                'value' => leyka_options()->opt('org_full_name'),
//            ],
//        ];
//    }

}