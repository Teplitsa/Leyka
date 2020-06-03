<?php if( !defined('WPINC') ) die;

$email_placeholders =
"<span class='placeholders-help'>
    <span class='item'>
        <code>#SITE_NAME#</code><span class='description'>Название сайта</span>
    </span>
    <span class='item'>
        <code>#SITE_EMAIL#</code><span class='description'>Email сайта</span>
    </span>
    <span class='item'>
        <code>#ORG_NAME#</code><span class='description'>Официальное название организации</span>
    </span>
    <span class='item'>
        <code>#DONATION_ID#</code><span class='description'>Идентификатор текущего пожертвования</span>
    </span>
    <span class='item'>
        <code>#DONATION_TYPE#</code><span class='description'>Тип пожертвования</span>
    </span>
    <span class='item'>
        <code>#DONOR_NAME#</code><span class='description'>Имя донора</span>
    </span>
    <span class='item'>
        <code>#DONOR_EMAIL#</code><span class='description'>Email донора</span>
    </span>
    <span class='item'>
        <code>#DONOR_COMMENT#</code><span class='description'>Комментарий донора к пожертвованию</span>
    </span>
    <span class='item'>
        <code>#SUM#</code><span class='description'>Полная сумма пожертвования (без учёта комиссий)</span>
    </span>
    <span class='item'>
        <code>#PAYMENT_METHOD_NAME#</code><span class='description'>Название способа оплаты</span>
    </span>
    <span class='item'>
        <code>#CAMPAIGN_NAME#</code><span class='description'>Кампания, на которую было сделано пожертвование</span>
    </span>
    <span class='item'>
        <code>#CAMPAIGN_URL#</code><span class='description'>Адрес страницы кампании, на которую было сделано пожертвование</span>
    </span>
    <span class='item'>
        <code>#CAMPAIGN_TARGET#</code><span class='description'>Официальная цель пожертвования (см. настройки кампании, опция «заголовок для платёжной системы»)</span>
    </span>
    <span class='item'>
        <code>#PURPOSE#</code><span class='description'>Название кампании для платежных систем</span>
    </span>
    <span class='item'>
        <code>#DATE#</code><span class='description'>Дата пожертвования</span>
    </span>
    <span class='item'>
        <code>#DONOR_ACCOUNT_LOGIN_LINK#</code><span class='description'>Приглашение войти в Личный кабинет донора</span>
    </span>
    <span class='item'>
        <code>#RECURRING_SUBSCRIPTION_CANCELLING_LINK#</code><span class='description'>Отменить рекуррентную подписку донора</span>
    </span>
    <span class='item'>
        <code>#DONOR_ACCOUNT_LOGIN_LINK#</code><span class='description'>Ссылка на активацию/вход в Личный кабинет донора</span>
    </span>
</span>
<div class='placeholders-help-actions'>
    <a href='#' class='inner hide-available-tags'>Свернуть доступные теги</a>
    <a href='#' class='inner show-available-tags'>Посмотреть доступные теги</a>
    <a href='#' class='inner restore-original-doc'>Вернуть первоначальный текст</a>
</div>";

$campaign_target_reaching_email_placeholders =
"<span class='placeholders-help'>
    <code>#SITE_NAME#</code> — ".__('a website title', 'leyka')."<br>
    <code>#ORG_NAME#</code> — ".__('an organization official title', 'leyka')."<br>
    <code>#DONOR_NAME#</code> — ".__('a donor name', 'leyka')."<br>
    <code>#DONOR_EMAIL#</code> — ".__('a donor email', 'leyka')."<br>
    <code>#SUM#</code> — ".__('a full donations amount (without payment commission)', 'leyka')."<br>
    <code>#CAMPAIGN_NAME#</code> — ".__('a campaign to which donation was made', 'leyka')."<br>
    <code>#CAMPAIGN_TARGET#</code> — ".__('a campaign target amount', 'leyka')."<br>
    <code>#PURPOSE#</code> — ".__('a campaign title for payment systems (see campaign settings)', 'leyka')."<br>
</span>
<div class='placeholders-help-actions'>
    <a href='#' class='inner hide-available-tags'>Свернуть доступные теги</a>
    <a href='#' class='inner show-available-tags'>Посмотреть доступные теги</a>
    <a href='#' class='inner restore-original-doc'>Вернуть первоначальный текст</a>
</div>";

/** Possible field types are: text, textarea, number, html, rich_html, select, radio, checkbox, multi_checkbox, custom_XXX */

// For type='text':
// 'length' => '',
// 'placeholder' => '',
// 'required' => boolean (default false),

// For type='number':
// 'length' => '',
// 'placeholder' => '',
// 'required' => boolean (default false),
// 'min' => 0+ / false,
// 'max' => 0+ / false,
// 'step' => positive number,

// For select, radio, checkbox & multi_checkbox fields:
// 'list_entries' => array(
//  'value_variant' => 'Variant title',
//  or 'value_variant' => array('title' => 'Variant title', 'comment' => 'Variant comment')
//),
// 'required' => boolean / integer, // For multi_checkbox, any positive integer "N" means "at least N values selected"

// For all:
// 'default' => '',
// 'title' => '',
// 'description' => '',
// 'comment' => '',
// 'validation_rules' => array('regexp to check value against' => 'Check failure description text', ...)

/** @var self Leyka_Options_Controller */
self::$_options_meta = array(
    'receiver_country' => array(
        'type' => 'select',
        'default' => 'ru', // leyka_get_default_receiver_country(),
        'title' => __('Select your country', 'leyka'),
        'required' => true,
        'list_entries' => array('-' => __('Not set', 'leyka'), 'ru' => __('Russia', 'leyka')),
        'description' => __('Leyka architecture allows you to collect funds even in other countries. Read more about it <a href="//leyka.te-st.ru/docs/translating-leyka/" target="_blank">here</a>.', 'leyka'),
    ),
    'receiver_legal_type' => array(
        'type' => 'radio',
        'title' => __('Donations receiver legal type', 'leyka'),
        'required' => true,
        'list_entries' => array(
            'legal' => array(
                'title' => __('NGO - legal person', 'leyka'),
                'comment' => '',
            ),
            'physical' => array(
                'title' => __('Physical person', 'leyka'),
                'comment' => '',
            ),
        ),
        'description' => __('If you plan to collect funds as a physical person, please <a href="https://te-st.ru/2019/09/03/donations-to-individuals/" target="_blank">read this</a>.', 'leyka'),
    ),
    'org_full_name' => array(
        'type' => 'text',
        'title' => __('The organization full name', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., Eastern charity foundation of st. John the Merciful', 'leyka'),
    ),
    'org_short_name' => array(
        'type' => 'text',
        'title' => __('The organization short name', 'leyka'),
        'required' => true,
        'placeholder' => __("E.g., St. John's foundation", 'leyka'),
    ),
    'org_face_fio_ip' => array(
        'type' => 'text',
        'title' => __('Full name of the organization head person', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., John Frederic Daw', 'leyka'),
    ),
    'org_contact_person_name' => array(
        'type' => 'text',
        'title' => __('Full name of the organization contact person', 'leyka'),
        'description' => __('Contact person is a person who watch over Leyka installation, setup and plugin connection to the payment gateways.', 'leyka'),
        'required' => false,
        'placeholder' => __('E.g., James Frederic Daw Jr.', 'leyka'),
    ),
    'org_face_fio_rp' => array(
        'type' => 'text',
        'title' => __('Full name of a person representing the NGO, in genitive case', 'leyka'),
        'description' => __("Enter a person's full name in genitive case.", 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., John Frederic Dow (in genitive case, if it exists)', 'leyka'),
    ),
    'org_face_position' => array(
        'type' => 'text',
        'title' => __("Organization head's position", 'leyka'),
        'default' => __('Director', 'leyka'),
        'description' => __('Enter an official position of a person representing the NGO.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., Director', 'leyka'),
    ),
    'org_address' => array(
        'type' => 'text',
        'title' => __('The organization official address', 'leyka'),
        'description' => __('Enter the organization official address.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., Malrose str., 4, Washington, DC, USA', 'leyka'),
    ),
    'org_state_reg_number' => array(
        'type' => 'text',
        'title' => __('The organization state registration number', 'leyka'),
        'description' => __('Enter the organization state registration number.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., 1023400056789', 'leyka'),
        'mask' => "'mask': '9{13}'",
    ),
    'org_kpp' => array(
        'type' => 'text',
        'title' => __('The organization statement of the account number', 'leyka'),
        'description' => __("Enter the organization statement of the account number.", 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., 780302015', 'leyka'),
        'mask' => "'mask': '9{9}'",
    ),
    'org_inn' => array(
        'type' => 'text',
        'title' => __('The organization taxpayer individual number', 'leyka'),
        'description' => __('Enter the organization individual number of a taxpayer.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., 4283256127', 'leyka'),
        'mask' => "'mask': '9{10}'",
    ),
    'org_bank_account' => array(
        'type' => 'text',
        'title' => __('The organization bank account number', 'leyka'),
        'description' => __('Enter a bank account number of the organization', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., 40123840529627089012', 'leyka'),
        'mask' => "'mask': '9{20}'",
    ),
    'org_bank_name' => array(
        'type' => 'text',
        'title' => __('The organization bank name', 'leyka'),
        'description' => __('Enter a full name for the organization bank.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., First Columbia Credit Bank', 'leyka'),
    ),
    'org_bank_bic' => array(
        'type' => 'text',
        'title' => __('The organization bank BIC number', 'leyka'),
        'description' => __("Enter a BIC of the organization bank.", 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., 044180293', 'leyka'),
        'mask' => "'mask': '9{9}'",
    ),
    'org_bank_corr_account' => array(
        'type' => 'text',
        'title' => __('The organization bank correspondent account number', 'leyka'),
        'description' => __('Enter a correspondent account number of the organization.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., 30101810270902010595', 'leyka'),
        'mask' => "'mask': '9{20}'",
    ),
    'person_full_name' => array(
        'type' => 'text',
        'title' => __('Your full name', 'leyka'),
        'required' => false,
        'placeholder' => __('E.g., John Frederic Daw', 'leyka'),
        'comment' => __('This info is needed only to use in your report documents on the site. We are not going to collect this info ourselves or pass it to the third party.', 'leyka'),
    ),
    'person_address' => array(
        'type' => 'text',
        'title' => _x('Your official address', 'For a physical person', 'leyka'),
        'required' => false,
        'placeholder' => __('E.g., Malrose str., 4, Washington, DC, USA', 'leyka'),
        'comment' => 'This info is needed only to use in your report documents on the site. We are not going to collect this info ourselves or pass it to the third party.',
    ),
    'person_inn' => array(
        'type' => 'text',
        'title' => _x('Your taxpayer individual number', 'For a physical person', 'leyka'),
        'required' => false,
        'placeholder' => __('E.g., 4283256127', 'leyka'),
        'mask' => "'mask': '9{12}'",
    ),
    'person_bank_name' => array(
        'type' => 'text',
        'title' => __('Your bank name', 'leyka'),
        'placeholder' => _x('E.g., First Columbia Credit Bank', 'For a physical person', 'leyka'),
    ),
    'person_bank_account' => array(
        'type' => 'text',
        'title' => _x('Your bank account number', 'For a physical person', 'leyka'),
        'placeholder' => _x('E.g., 40123840529627089012', 'For a physical person', 'leyka'),
        'mask' => "'mask': '9{20}'",
    ),
    'person_bank_bic' => array(
        'type' => 'text',
        'title' => _x('Your bank BIC number', 'For a physical person', 'leyka'),
        'placeholder' => _x('E.g., 044180293', 'For a physical person', 'leyka'),
        'mask' => "'mask': '9{9}'",
    ),
    'person_bank_corr_account' => array(
        'type' => 'text',
        'title' => _x('Your correspondent bank account number', 'For a physical person', 'leyka'),
        'placeholder' => _x('E.g., 30101810270902010595', 'For a physical person', 'leyka'),
        'mask' => "'mask': '9{20}'",
    ),
    'send_plugin_stats' => array(
        'type' => 'radio',
        'default' => 'y',
        'title' => __('Anonymous plugin usage data collection', 'leyka'),
        'required' => true,
        'list_entries' => array(
            'y' => __('Yes, I agree to send the data', 'leyka'),
            'n' => __("No, I don't want to share the data", 'leyka'),
        ),
    ),
    'pm_available' => array(
        'type' => 'multi_checkbox',
        'default' => array('text-text_box'),
        'title' => __('Payment methods available on donation forms', 'leyka'),
        'description' => __("Check out payment methods through that you'd want to receive a donation payments.", 'leyka'),
        'required' => true,
//        'list_entries' => 'leyka_get_gateways_pm_list', // The option never showed in UI via standard means
    ),
    'pm_order' => array(
        'type' => 'text', // It's intentionally of text type - the option contains a serialized array
        'default' => '', // PM will be ordered just as their gateways were added
        'title' => __('Payment methods order on donation forms', 'leyka'),
    ),
    'extensions_active' => array(
        'type' => 'multi_checkbox',
        'default' => array(),
        'title' => __('Extensions', 'leyka'),
//        'required' => true,
//        'list_entries' => 'leyka_get_extensions_list', // The option never showed in UI via standard means
    ),
    'auto_refresh_currency_rates' => array(
        'type' => 'checkbox',
        'default' => '1',
        'title' => __('Automatically refresh currency rates', 'leyka'),
        'description' => __('Check to enable auto-refresh of currency rates. It will be performed every 24 hours and will require connection with http://cbr.ru website.', 'leyka'),
    ),
//    'auto_refresh_currency_rate_usd' => array( // disabled, waithing for multicurrency system
//        'type' => 'radio',
//        'default' => 'y',
//        'title' => __('Automatically refresh currency rates', 'leyka'),
//        'description' => __('Check to enable auto-refresh of currency rates. It will be performed every 24 hours and will require connection with http://cbr.ru website.', 'leyka'),
//        'list_entries' => array(
//            'y' => __('Automatically refresh currency rates using central bank rate', 'leyka'),
//            'n' => __("Manual currency rate input", 'leyka'),
//        ),
//    ),
    'currency_rur2usd' => array(
        'type' => 'number',
        'title' => __('Exchange rate', 'leyka'),
        'description' => __('Please set the RUR to USD currency rate here.', 'leyka'),
        'required' => true,
        'placeholder' => __('Enter rate value (e.g., 70)', 'leyka'),
        'length' => 6,
    ),
    'currency_rur2eur' => array(
        'type' => 'number',
        'title' => __('Exchange rate', 'leyka'),
        'description' => __('Please set the RUR to EUR currency rate here.', 'leyka'),
        'required' => true,
        'placeholder' => __('Enter rate value (e.g., 80)', 'leyka'),
        'length' => 6,
    ),
    'currency_rur_label' => array(
        'type' => 'text',
        'default' => __('RUB', 'leyka'),
        'title' => __('RUB label', 'leyka'),
        'description' => __('Please set the RUB currency label here.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., roub.', 'leyka'),
        'length' => 6,
    ),
    'currency_rur_min_sum' => array(
        'type' => 'number',
        'default' => 100,
        'title' => __('Minimum sum available for RUB', 'leyka'),
        'description' => __('Please set minimum sum available for RUB donations.', 'leyka'),
        'required' => true,
        'placeholder' => '100',
        'length' => 6,
    ),
    'currency_rur_max_sum' => array(
        'type' => 'number',
        'default' => 30000,
        'title' => __('Maximum sum available for RUB', 'leyka'),
        'description' => __('Please set maximum sum available for RUB donations.', 'leyka'),
        'required' => true,
        'placeholder' => '30000',
        'length' => 6,
    ),
    'currency_rur_flexible_default_amount' => array(
        'type' => 'number',
        'default' => 500,
        'title' => __('Default amount of donation in RUB (for "flexible" donation type)', 'leyka'),
        'description' => __('Please, set a default amount of donation when RUB selected as currency.', 'leyka'),
        'required' => true,
        'placeholder' => '500',
        'length' => 6,
    ),
    'currency_rur_fixed_amounts' => array(
        'type' => 'text',
        'default' => '100,300,500,1000',
        'title' => __('Possible amounts of donation in RUB (for "fixed" donation type)', 'leyka'),
        'description' => __('Please, set possible amounts of donation in RUR when "fixed" donation type is selected. Only an integer non-negative values, separated with commas.', 'leyka'),
        'required' => true,
        'placeholder' => '100,300,500,1000',
        'length' => 25,
    ),
    'currency_usd_label' => array(
        'type' => 'text',
        'default' => __('$', 'leyka'),
        'title' => __('USD label', 'leyka'),
        'description' => __('Please set the USD currency label here.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., USD', 'leyka'),
        'length' => 6,
    ),
    'currency_usd_min_sum' => array(
        'type' => 'number',
        'default' => 10,
        'title' => __('Minimum sum available for USD', 'leyka'),
        'description' => __('Please set minimum sum available for USD donations.', 'leyka'),
        'required' => true,
        'placeholder' => '0',
        'length' => 6,
    ),
    'currency_usd_max_sum' => array(
        'type' => 'number',
        'default' => 1000,
        'title' => __('Maximum sum available for USD', 'leyka'),
        'description' => __('Please set maximum sum available for USD donations.', 'leyka'),
        'required' => true,
        'placeholder' => '1000',
        'length' => 6,
    ),
    'currency_usd_flexible_default_amount' => array(
        'type' => 'number',
        'default' => 10,
        'title' => __('Default amount of donation in USD (for "flexible" donation type)', 'leyka'),
        'description' => __('Please, set a default amount of donation when RUR selected as currency.', 'leyka'),
        'required' => true,
        'placeholder' => '10',
        'length' => 6,
    ),
    'currency_usd_fixed_amounts' => array(
        'type' => 'text',
        'default' => '3,5,10,15,50',
        'title' => __('Possible amounts of donation in USD (for «fixed» donation type)', 'leyka'),
        'description' => __('Please, set possible amounts of donation in USD when "fixed" donation type is selected. Only an integer non-negative values, separated with commas.', 'leyka'),
        'required' => true,
        'placeholder' => '3,5,10,15,50',
        'length' => 25,
    ),
    'currency_eur_label' => array(
        'type' => 'text',
        'default' => __('euro', 'leyka'),
        'title' => __('EUR label', 'leyka'),
        'description' => __('Please set the EUR currency label here.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., euro', 'leyka'),
        'length' => 6,
    ),
    'currency_eur_min_sum' => array(
        'type' => 'number',
        'default' => 3,
        'title' => __('Minimum sum available for EUR', 'leyka'),
        'description' => __('Please set minimum sum available for EUR donations.', 'leyka'),
        'required' => true,
        'placeholder' => '0',
        'length' => 6,
    ),
    'currency_eur_max_sum' => array(
        'type' => 'number',
        'default' => 650,
        'title' => __('Maximum sum available for EUR', 'leyka'),
        'description' => __('Please set maximum sum available for EUR donations.', 'leyka'),
        'required' => true,
        'placeholder' => '650',
        'length' => 6,
    ),
    'currency_eur_flexible_default_amount' => array(
        'type' => 'number',
        'default' => 5,
        'title' => __('Default amount of donation in EUR (for «flexible» donation type)', 'leyka'),
        'description' => __('Please, set a default amount of donation when EUR selected as currency.', 'leyka'),
        'required' => true,
        'placeholder' => '5',
        'length' => 6,
    ),
    'currency_eur_fixed_amounts' => array(
        'type' => 'text',
        'default' => '3,5,10,100,500',
        'title' => __('Possible amounts of donation in EUR (for «fixed» donation type)', 'leyka'),
        'description' => __('Please, set possible amounts of donation in EUR when «fixed» donation type is selected. Only an integer non-negative values, separated with commas.', 'leyka'),
        'required' => true,
        'placeholder' => '3,5,10,100,500',
        'length' => 25,
    ),
    'email_from_name' => array(
        'type' => 'text',
        'default' => get_bloginfo('name'),
        'title' => __('Sender', 'leyka'),
        'placeholder' => __('E.g., Daisy Foundation website', 'leyka'),
        'comment' => __('What your donors will see in the "from whom" email field. For the most of the cases, your organization name will do fine.', 'leyka'),
    ),
    'email_from' => array(
        'type' => 'text',
        'default' => leyka_get_default_email_from(),
        'title' => __('Sender email', 'leyka'),
        'placeholder' => __('E.g., donations@daisyfoundation.org', 'leyka'),
        'comment' => __('What your donors will see in the "from email" field. For the most of the cases, your organization contact email will do fine.', 'leyka'),
    ),
    'email_thanks_title' => array(
        'type' => 'text',
        'default' => __('Thank you for your donation!', 'leyka'),
        'title' => __('A title of after-donation notice sended to a donor', 'leyka'),
        'description' => __('Enter the title of the notification (or thankful) email with donation data that would be sended to each donor right after his donation is made.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., Daisy Foundation thanks you for your kindness', 'leyka'),
    ),
    'email_thanks_text' => array(
        'type' => 'rich_html',
        'default' => __('Hello, #DONOR_NAME#!<br><br>You have chosed to make a #SUM# donation to the following charity campaign: #CAMPAIGN_NAME#, using #PAYMENT_METHOD_NAME#.<br><br>Sincerely thank you,<br>#ORG_NAME#', 'leyka'),
        'title' => __('Email text', 'leyka'),
        'description' => $email_placeholders,
        'required' => true,
        'comment' => __('An email text that your donor will see. You may use the special tags in the text.', 'leyka'),
        'field_classes' => array('type-rich_html'),
    ),
    'email_recurring_init_thanks_title' => array(
        'type' => 'text',
        'default' => __('Thank you for your support!', 'leyka'),
        'title' => __('A title of an initial recurring donation notice sent to a donor', 'leyka'),
        'description' => __('Enter a title of a notification email with donation data that would be sended to each donor on each rebill donation.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., Daisy Foundation thanks you for your kindness', 'leyka'),
    ),
    'email_recurring_init_thanks_text' => array(
        'type' => 'rich_html',
        'default' => __('Hello, #DONOR_NAME#!<br><br>We just took a #SUM# from your account as a regular donation to the campaign «#CAMPAIGN_NAME#», using #PAYMENT_METHOD_NAME#.<br><br>#DONOR_ACCOUNT_LOGIN_LINK#<br><br>If you, regretfully, wish to stop future regular donations to this campaign, please #RECURRING_SUBSCRIPTION_CANCELLING_LINK#.<br><br>Sincerely thank you,<br>#ORG_NAME#', 'leyka'),
        'title' => __('A text of a recurring subscription donation notice sent to a donor', 'leyka'),
        'description' => __('Enter the text of the notification email that would be sended to each donor on each rebill donation. It may include the following special entries:', 'leyka').$email_placeholders,
        'required' => true,
        'field_classes' => array('type-rich_html'),
    ),
    'email_recurring_ongoing_thanks_title' => array(
        'type' => 'text',
        'default' => __('Thank you for your unwavering support!', 'leyka'),
        'title' => __('A title of an after-rebill donation notice for a donor', 'leyka'),
        'description' => __('Enter a title of a donor notification email with donation data that will be sent on each recurring auto-payment.', 'leyka'),
        'required' => true,
    ),
    'email_recurring_ongoing_thanks_text' => array(
        'type' => 'rich_html',
        'default' => __('Hello, #DONOR_NAME#!<br><br>We just took a #SUM# from your account as a regular donation to the campaign «#CAMPAIGN_NAME#», using #PAYMENT_METHOD_NAME#.<br><br>#DONOR_ACCOUNT_LOGIN_LINK#<br><br>If you, regretfully, wish to stop future regular donations to this campaign, please #RECURRING_SUBSCRIPTION_CANCELLING_LINK#.<br><br>Sincerely thank you,<br>#ORG_NAME#', 'leyka'),
        'title' => __('A text of after-rebill donation notice sent to a donor', 'leyka'),
        'description' => __('Enter the text of the notification email that would be sended to each donor on each rebill donation. It may include the following special entries:', 'leyka').$email_placeholders,
        'required' => true,
        'field_classes' => array('type-rich_html'),
    ),
    'send_donor_thanking_emails' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('Send the emails', 'leyka'),
        'comment' => __('Check to send a thankful email to a donor on each funded donation', 'leyka'),
        'short_format' => true,
    ),
    'send_donor_thanking_emails_on_recurring_init' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('Send the emails', 'leyka'),
        'comment' => __('Check to send a thankful email to a donor on each recurring donations subscription', 'leyka'),
        'short_format' => true,
    ),
    'send_donor_thanking_emails_on_recurring_ongoing' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('Send the emails', 'leyka'),
        'comment' => __('Check to send a thankful email to a donor on each non-initial recurring donation', 'leyka'),
        'short_format' => true,
    ),
    'send_recurring_canceling_donor_notification_email' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __('Send the emails', 'leyka'),
        'comment' => __('Check to send an email to a donor if he canceled a recurring subscription', 'leyka'),
        'short_format' => true,
    ),
    'send_donor_emails_on_campaign_target_reaching' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('Send the emails', 'leyka'),
        'comment' => __('Check to send a special thankful email to each donor when campaign target reached', 'leyka'),
        'short_format' => true,
    ),
    'recurring_canceling_donor_notification_emails_title' => array(
        'type' => 'text',
        'default' => __('Thank you for being with us!', 'leyka'),
        'title' => __('A title of a notification email', 'leyka'),
        'description' => __('Enter the email title.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., Thank you for being with us!', 'leyka'),
    ),
    'recurring_canceling_donor_notification_emails_text' => array(
        'type' => 'rich_html',
        'default' => __('Hello, #DONOR_NAME#!<br><br>You canceled the recurring donations on the "#CAMPAIGN_NAME#" campaign. We thank you for support, and we will miss it greatly.<br><br>Recurring donations are very important for us, as they allow us to plan stable future works. You may subscribe to monthly recurring donations again on the <a href="#CAMPAIGN_URL#">campaign page</a>.<br><br>Sincerely thank you for your support,<br>#ORG_NAME#', 'leyka'),
        'title' => __('Notification email text', 'leyka'),
        'description' => __('Enter the text of notification email. It may include the following special entries:', 'leyka').$email_placeholders,
        'required' => true,
        'field_classes' => array('type-rich_html'),
    ),
    'recurring_canceling_donor_notification_emails_defer_by' => array(
        'type' => 'number',
        'default' => 7,
        'title' => __('Number of days to defer emails sending', 'leyka'),
        'comment' => __('Set a time (in days) after which the notification email will be sent to donor.', 'leyka'),
        'required' => false,
        'min' => 0,
        'step' => 1,
        'placeholder' => __('E.g., 7', 'leyka'),
    ),
    'non_init_recurring_donor_registration_emails_title' => array(
        'type' => 'text',
        'default' => __('Your personal account was created', 'leyka'),
        'title' => __('A title of a notification email', 'leyka'),
        'description' => __('Enter the email title.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., Your personal account was created', 'leyka'),
    ),
    'non_init_recurring_donor_registration_emails_text' => array(
        'type' => 'rich_html',
        'default' => __('Hello, #DONOR_NAME#!<br><br>We created a personal account for you to manage your donations.<br><br>#DONOR_ACCOUNT_LOGIN_LINK#<br><br>If you do not wish to use it, just ignore this email.<br><br>Sincerely thank you,<br>#ORG_NAME#', 'leyka'),
        'title' => __("Notification email text", 'leyka'),
        'description' => __("Enter the text of notification email. It may include the following special entries:", 'leyka').$email_placeholders,
        'required' => true,
        'field_classes' => array('type-rich_html'),
    ),
    'email_campaign_target_reaching_title' => array(
        'type' => 'text',
        'default' => __('Thanks to you, the campaign succeeded!', 'leyka'),
        'title' => __('A title of an email notification sent to each donor when campaign target reached', 'leyka'),
        'description' => __('Enter the title of an email.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., Thanks to you, the campaign succeeded!', 'leyka'),
    ),
    'email_campaign_target_reaching_text' => array(
        'type' => 'rich_html',
        'default' => __("Hello, #DONOR_NAME#!<br><br>You've donated #SUM# totally to the campaign: «#CAMPAIGN_NAME#».<br><br>We're glad to tell that just now this campaign successfully finished!<br><br>We heartfully thank you for your support,<br>#ORG_NAME#", 'leyka'),
        'title' => __('A text of a notification email sent to each donor when campaign target reached', 'leyka'),
        'description' => __('Enter the text of a notification email sent to each donor when campaign target reached. The text may include the following special entries:', 'leyka').$campaign_target_reaching_email_placeholders,
        'required' => true,
        'field_classes' => array('type-rich_html'),
    ),
    'notify_donations_managers' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('Send emails on single donations', 'leyka'),
        'comment' => __('Check to notify the website personnel (donations managers) of each incoming donation', 'leyka'),
        'short_format' => true,
    ),
    'notify_managers_on_recurrents' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('Send emails on recurring donations', 'leyka'),
        'comment' => __('Check to notify the website personnel (donations managers) of each incoming recurrent donation', 'leyka'),
        'short_format' => true,
    ),
    'donations_managers_emails' => array(
        'type' => 'text',
        'default' => get_bloginfo('admin_email').',',
        'title' => __('A comma-separated emails to notify of incoming donation', 'leyka'),
        'placeholder' => __('E.g., admin@daisyfoundation.org,yourmail@domain.com', 'leyka'),
    ),
    'email_notification_title' => array(
        'type' => 'text',
        'default' => __('New donation incoming', 'leyka'),
        'title' => __('A title of new donation notification email', 'leyka'),
        'placeholder' => __('E.g., new donation incoming', 'leyka'),
    ),
    'email_notification_text' => array(
        'type' => 'rich_html',
        'default' => __('Hello!<br><br>A new donation has been made on a #SITE_NAME#:<br><br>Campaign: #CAMPAIGN_NAME#.<br>Donation purpose: #PURPOSE#<br>Amount: #SUM#.<br>Payment method: #PAYMENT_METHOD_NAME#.<br>Date: #DATE#<br><br>Your Leyka', 'leyka'),
        'title' => __('A text of after-donation notification sended to a website personnel', 'leyka'),
        'description' => __("Enter the text of the notification email that would be sended to each email stated before right after donation is made. It may include the following special entries:", 'leyka').$email_placeholders,
        'field_classes' => array('type-rich_html'),
    ),
    'notify_tech_support_on_failed_donations' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('Send error reporting emails to the tech. support on failed donations', 'leyka'),
        'comment' => __('Check to notify the website technical support (see the "website technical support email" option) of each failed donation.', 'leyka'),
        'short_format' => true,
    ),
    'tech_support_email' => array(
        'type' => 'email',
        'title' => __('Website technical support email', 'leyka'),
        'description' => __('E-mail that you want to use to collect technical support requests from the donors.', 'leyka'),
        'placeholder' => __('E.g., techsupport@email.com', 'leyka'),
    ),
    'donation_form_template' => array(
        'type' => 'radio',
        'default' => 'star',
        'title' => __('Select a default template for all your donation forms', 'leyka'),
        'description' => __('Select one of the form templates.', 'leyka'),
        'required' => true,
        'list_entries' => 'leyka_get_form_templates_list',
    ),
    'allow_deprecated_form_templates' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __('Allow usage of the old donation forms templates', 'leyka'),
        'short_format' => true,
    ),
    'donation_sum_field_type' => array(
        'type' => 'radio',
        'default' => 'flexible',
        'title' => __('Select a type of the sum field for all your donation forms', 'leyka'),
        'description' => __('Select a type of the sum field. «Fixed» means a set of stable sum variants, while «flexible» is a free input field.', 'leyka'),
        'required' => true,
        'list_entries' => array(
            'flexible' => __('Flexible', 'leyka'),
            'fixed' => __('Fixed', 'leyka'),
            'mixed' => __('Fixed sum variants + flexible field', 'leyka')
        ),
    ),
    'do_not_display_donation_form' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __('Do not display payment form on campaign page automatically', 'leyka'),
        'description' => __("Do not display payment form", 'leyka'),
    ),
    'scale_widget_place' => array(
        'type' => 'radio',
        'default' => 'top',
        'title' => __('Select where Target completion widget will be placed at campaign pages', 'leyka'),
        'required' => true,
        'list_entries' => array(
            'top' => __('Above page content', 'leyka'),
            'bottom' => __('Below page content', 'leyka'),
            'both' => __('Both', 'leyka'),
            '-' => __('Nowhere', 'leyka'),
        ),
    ),
    'donations_history_under_forms' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('Donations history widget below donation forms', 'leyka'),
        'description' => __('Display donations history below donation forms', 'leyka'),
    ),
    'show_campaign_sharing' => array(
        'type' => 'checkbox',
        'default' => 1,
        'title' => __('Campaign sharing widget below donation forms', 'leyka'),
        'description' => __('Display sharing widget below donation forms', 'leyka'),
    ),
    'show_success_widget_on_success' => array(
        'type' => 'checkbox',
        'default' => 1,
        'title' => __('Show an email subscription widget on the successful donation page', 'leyka'),
        'description' => __('Show a subscription form on the successful donation page', 'leyka'),
    ),
    'show_failure_widget_on_failure' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('Show a failure notification widget on the donation failure page', 'leyka'),
        'description' => __('Display a failure notification widget on the donation failure page', 'leyka'),
    ),
    'use_gtm_ua_integration' => array( // 'use_gtm_ua_integration' ('-'|'simple'|'enchanced')
        'type' => 'radio',
        'title' => __('Google Tag Manager & Universal Analytics integration', 'leyka'),
        'list_entries' => array(
            '-' => __("Don't use UA e-commerce integration", 'leyka'),
            'simple' => __('Use the GTM & simple UA e-commerce integration', 'leyka'),
            'enchanced' => __('Use the UA enchanced e-commerce integration', 'leyka'),
            'enchanced_ua_only' => __('Connect directly to the GUA enchanced e-commerce tracking', 'leyka'),
        ),
        'default' => '-',
    ),
    'gtm_ua_enchanced_events' => array(
        'type' => 'multi_checkbox',
        'default' => array('purchase',),
        'title' => __('Google UA enchanced mode events support', 'leyka'),
        'comment' => __('Check the events types that should be triggered in donation process. The "eec.checkout" event will always trigger.<br><br>Be advised: this option is relevant only for GTM & UA enchanced e-commerce integration.', 'leyka'),
        'list_entries' => array('detail' => __('EEC.detail', 'leyka'), 'purchase' => __('EEC.purchase', 'leyka'),),
    ),
    'gtm_ua_tracking_id' => array(
        'type' => 'text',
        'title' => __('Google UA Tracking ID ', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., UA-12345678-90', 'leyka'),
    ),
    'revo_template_slider_max_sum' => array(
        'type' => 'number',
        'default' => 3000,
        'title' => __('Maximum sum available for slider', 'leyka'),
        'description' => __('Please set the maximum sum available for slider control.', 'leyka'),
        'required' => true,
        'placeholder' => 3000,
        'min' => 1,
        'max' => 100000,
        'step' => 1,
    ),
    'revo_template_show_donors_list' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __("Display a donors names list on donation forms", 'leyka'),
        'description' => __("Check to display a list of donors names on all donation forms", 'leyka'),
    ),
    'show_donation_comment_field' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __('Display a comment textarea field on donation forms', 'leyka'),
        'description' => __("Display comment field on donation form", 'leyka'),
    ),
    'donation_comment_max_length' => array(
        'type' => 'number',
        'default' => '',
        'title' => __('The maximum length of a donation comment value', 'leyka'),
        'description' => __('Set the maximum number of symbols allowed for donation comments. You may set "0" for the unlimited values length.', 'leyka'),
        'placeholder' => __('Maximum number of symbols', 'leyka'),
        'min' => 1,
        'step' => 1,
    ),
    'show_donation_comments_in_frontend' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __('Display donation comments in frontend', 'leyka'),
        'description' => __('Check to show donors comments in the website frontend (e.g. in donation lists widgets)', 'leyka'),
    ),
    'revo_template_show_thumbnail' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('Display a thumbnail in inline page blocks', 'leyka'),
        'description' => __('Check if you need to show a campaign thumbnail in inline page blocks.', 'leyka'),
    ),
    'agree_to_terms_needed' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('To donate, donor must agree to Terms of Service', 'leyka'),
        'description' => __("Check if you must receive donor's agreement with some terms before his donation.", 'leyka'),
    ),
    'agree_to_terms_text_text_part' => array(
        'type' => 'text',
        'default' => __('I agree with', 'leyka'),
        'title' => __('Terms acception checkbox label - the first (text) part', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., I agree with', 'leyka'),
    ),
    'agree_to_terms_text_link_part' => array(
        'type' => 'text',
        'default' => __('Terms of the donation service', 'leyka'),
        'title' => __('Terms acception checkbox label - the second (link) part', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., Terms of the donation service', 'leyka'),
    ),
    'terms_of_service_text' => array(
        'type' => 'rich_html',
        'default' => 'Публичная оферта о заключении договора пожертвования<br>
<br>
#LEGAL_NAME# (#LEGAL_FACE_POSITION#: #LEGAL_FACE#),<br>
предлагает гражданам сделать пожертвование на ниже приведенных условиях:<br>
<br>
1. Общие положения<br>1.1. В соответствии с п. 2 ст. 437 Гражданского кодекса Российской Федерации данное предложение является публичной офертой (далее – Оферта).<br>
1.2. В настоящей Оферте употребляются термины, имеющие следующее значение:<br>«Пожертвование» - «дарение вещи или права в общеполезных целях»;<br>
«Жертвователь» - «граждане, делающие пожертвования»;<br>
«Получатель пожертвования» - «#LEGAL_NAME#».<br>
<br>
1.3. Оферта действует бессрочно с момента размещения ее на сайте Получателя пожертвования.<br>
1.4. Получатель пожертвования вправе отменить Оферту в любое время путем удаления ее со страницы своего сайта в Интернете.<br>
1.5. Недействительность одного или нескольких условий Оферты не влечет недействительность всех остальных условий Оферты.<br>
<br>
2. Существенные условия договора пожертвования:<br>
2.1. Пожертвование используется на содержание и ведение уставной деятельности Получателя пожертвования.<br>
2.2. Сумма пожертвования определяется Жертвователем.<br>
<br>
3. Порядок заключения договора пожертвования:<br>
3.1. В соответствии с п. 3 ст. 434 Гражданского кодекса Российской Федерации договор пожертвования заключается в письменной форме путем акцепта Оферты Жертвователем.<br>
3.2. Оферта может быть акцептована путем перечисления Жертвователем денежных средств в пользу Получателя пожертвования платежным поручением по реквизитам, указанным в разделе 5 Оферты, с указанием в строке «назначение платежа»: «пожертвование на содержание и ведение уставной деятельности», а также с использованием пластиковых карт, электронных платежных систем и других средств и систем, позволяющих Жертвователю перечислять Получателю пожертвования денежных средств.<br>
3.3. Совершение Жертвователем любого из действий, предусмотренных п. 3.2. Оферты, считается акцептом Оферты в соответствии с п. 3 ст. 438 Гражданского кодекса Российской Федерации.<br>
3.4. Датой акцепта Оферты – датой заключения договора пожертвования является дата поступления пожертвования в виде денежных средств от Жертвователя на расчетный счет Получателя пожертвования.<br>
<br>
4. Заключительные положения:<br>
4.1. Совершая действия, предусмотренные настоящей Офертой, Жертвователь подтверждает, что ознакомлен с условиями Оферты, целями деятельности Получателя пожертвования, осознает значение своих действий и имеет полное право на их совершение, полностью и безоговорочно принимает условия настоящей Оферты.<br>
4.2. Настоящая Оферта регулируется и толкуется в соответствии с действующим российском законодательством.<br>
<br>
5. Подпись и реквизиты Получателя пожертвования<br>
<br>
#LEGAL_NAME#<br>
<br>
ОГРН: #STATE_REG_NUMBER#<br>
ИНН/КПП: #INN#/#KPP#<br>
Адрес места нахождения: #LEGAL_ADDRESS#<br>
<br>
Банковские реквизиты:<br>
Номер банковского счёта: #BANK_ACCOUNT#<br>
Банк: #BANK_NAME#<br>
БИК банка: #BANK_BIC#<br>
Номер корреспондентского счёта банка: #BANK_CORR_ACCOUNT#<br>
<br>
<br>
#LEGAL_FACE_POSITION#<br>
#LEGAL_FACE#',
        'title' => __('A text of Terms of donation service', 'leyka'),
        'required' => true,
        'field_classes' => array('type-rich_html'),
    ),
    'person_terms_of_service_text' => array(
        'type' => 'rich_html',
        'default' => 'Публичная оферта о заключении договора пожертвования<br>
<br>
#LEGAL_NAME#,<br>
предлагает гражданам сделать пожертвование на ниже приведенных условиях:<br>
<br>
1. Общие положения<br>1.1. В соответствии с п. 2 ст. 437 Гражданского кодекса Российской Федерации данное предложение является публичной офертой (далее – Оферта).<br>
1.2. В настоящей Оферте употребляются термины, имеющие следующее значение:<br>«Пожертвование» - «дарение вещи или права в общеполезных целях»;<br>
«Жертвователь» - «граждане, делающие пожертвования»;<br>
«Получатель пожертвования» - «#LEGAL_NAME#».<br>
<br>
1.3. Оферта действует бессрочно с момента размещения ее на сайте Получателя пожертвования.<br>
1.4. Получатель пожертвования вправе отменить Оферту в любое время путем удаления ее со страницы своего сайта в Интернете.<br>
1.5. Недействительность одного или нескольких условий Оферты не влечет недействительность всех остальных условий Оферты.<br>
<br>
2. Существенные условия договора пожертвования:<br>
2.1. Пожертвование используется на содержание и ведение уставной деятельности Получателя пожертвования.<br>
2.2. Сумма пожертвования определяется Жертвователем.<br>
<br>
3. Порядок заключения договора пожертвования:<br>
3.1. В соответствии с п. 3 ст. 434 Гражданского кодекса Российской Федерации договор пожертвования заключается в письменной форме путем акцепта Оферты Жертвователем.<br>
3.2. Оферта может быть акцептована путем перечисления Жертвователем денежных средств в пользу Получателя пожертвования платежным поручением по реквизитам, указанным в разделе 5 Оферты, с указанием в строке «назначение платежа»: «пожертвование на содержание и ведение уставной деятельности», а также с использованием пластиковых карт, электронных платежных систем и других средств и систем, позволяющих Жертвователю перечислять Получателю пожертвования денежных средств.<br>
3.3. Совершение Жертвователем любого из действий, предусмотренных п. 3.2. Оферты, считается акцептом Оферты в соответствии с п. 3 ст. 438 Гражданского кодекса Российской Федерации.<br>
3.4. Датой акцепта Оферты – датой заключения договора пожертвования является дата поступления пожертвования в виде денежных средств от Жертвователя на расчетный счет Получателя пожертвования.<br>
<br>
4. Заключительные положения:<br>
4.1. Совершая действия, предусмотренные настоящей Офертой, Жертвователь подтверждает, что ознакомлен с условиями Оферты, целями деятельности Получателя пожертвования, осознает значение своих действий и имеет полное право на их совершение, полностью и безоговорочно принимает условия настоящей Оферты.<br>
4.2. Настоящая Оферта регулируется и толкуется в соответствии с действующим российском законодательством.<br>
<br>
5. Подпись и реквизиты Получателя пожертвования<br>
<br>
#LEGAL_NAME#<br>
<br>
ИНН: #INN#<br>
Адрес места нахождения: #LEGAL_ADDRESS#<br>
<br>
Банковские реквизиты:<br>
Номер банковского счёта: #BANK_ACCOUNT#<br>
Банк: #BANK_NAME#<br>
БИК банка: #BANK_BIC#<br>
Номер корреспондентского счёта банка: #BANK_CORR_ACCOUNT#<br>
<br>
#LEGAL_FACE#',
        'title' => __('A text of Terms of donation service', 'leyka'),
        'required' => true,
        'field_classes' => array('type-rich_html'),
    ),
    'terms_agreed_by_default' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __("Donor is agreed with Terms of Service by default", 'leyka'),
        'description' => __('When donor sees a donation form, Terms of Service agreement checkbox is already checked.', 'leyka'),
    ),
    'terms_of_service_page' => array(
        'type' => 'select',
        'default' => leyka_get_default_service_terms_page(),
        'title' => __("Page of terms of service text", 'leyka'),
        'description' => __('Select a page with terms of the donation service full text.', 'leyka'),
        'list_entries' => 'leyka_get_pages_list',
    ),
    'agree_to_terms_link_action' => array(
        'type' => 'radio',
        'default' => 'popup',
        'title' => __('Click on Terms of service link...', 'leyka'),
        'required' => true,
        'list_entries' => array(
            'popup' => __('Opens the Terms text in popup window', 'leyka'),
            'page' => __('Opens the page of Terms text', 'leyka'),
        ),
    ),
    'agree_to_pd_terms_needed' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('To donate, donor must agree to Terms of personal data usage', 'leyka'),
        'description' => __("Check if you should have donor's agreement with some terms regarding his personal data usage.", 'leyka'),
    ),
    'agree_to_pd_terms_text_text_part' => array(
        'type' => 'text',
        'default' => __('I agree with', 'leyka'),
        'title' => __('Personal data usage Terms checkbox label - the first (text) part', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., I agree with', 'leyka'),
    ),
    'agree_to_pd_terms_text_link_part' => array(
        'type' => 'text',
        'default' => _x('Terms of personal data usage', 'In instrumental case', 'leyka'),
        'title' => __('Personal data usage Terms checkbox label - the second (link) part', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., Terms of personal data usage', 'leyka'),
    ),
    'pd_terms_text' => array(
        'type' => 'rich_html',
        'default' => __('Terms of personal data usage full text. Use <br> for line-breaks.', 'leyka'),
        'title' => __('A text of personal data usage Terms', 'leyka'),
        'required' => true,
        'field_classes' => array('type-rich_html'),
    ),
    'person_pd_terms_text' => array(
        'type' => 'rich_html',
        'default' => __('Terms of personal data usage full text. Use <br> for line-breaks.', 'leyka'),
        'title' => __('A text of personal data usage Terms', 'leyka'),
        'required' => true,
    ),
    'pd_terms_agreed_by_default' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __('Donor is agreed with personal data usage Terms by default', 'leyka'),
        'description' => __('When donor sees a donation form, personal data usage Terms agreement checkbox is already checked.', 'leyka'),
    ),
    'pd_terms_page' => array(
        'type' => 'select',
        'default' => leyka_get_default_pd_terms_page(),
        'title' => __("Page of personal data usage terms and policy", 'leyka'),
        'description' => __('Select a page with personal data usage terms and policy full text.', 'leyka'),
        'list_entries' => 'leyka_get_pages_list',
    ),
    'agree_to_pd_terms_link_action' => array(
        'type' => 'radio',
        'default' => 'popup',
        'title' => __('Click on personal data usage Terms link...', 'leyka'),
        'required' => true,
        'list_entries' => array(
            'popup' => __('Opens the Terms text in popup window', 'leyka'),
            'page' => __('Opens the page of Terms text', 'leyka'),
        ),
    ),
    'donation_submit_text' => array(
        'type' => 'text',
        'default' => __('Donate', 'leyka'),
        'title' => __('Label of the button to submit a donation form', 'leyka'),
        'description' => __('Enter the text for a submit buttons on a donation forms.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., "Donate" or "Support"', 'leyka'),
    ),
    'recurring_donation_benefits_text' => array(
        'type' => 'textarea',
        'default' => __('We will be happy with a small but monthly help, this gives us confidence in the future and the ability to plan our activities.', 'leyka'),
        'title' => __('Explanation of benefits of egular donations', 'leyka'),
        'required' => false,
    ),
    'revo_donation_complete_button_text' => array(
        'type' => 'text',
        'default' => __('Complete donation', 'leyka'),
        'title' => __('Label of the button to complete a donation', 'leyka'),
        'description' => __('Enter the text for a complete donation buttons on a donation forms.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., "Complete the donation" or "Close the form"', 'leyka'),
    ),
    'revo_thankyou_text' => array(
        'type' => 'text',
        'default' => __('Thank you! We appreciate your help! Let\'s stay in touch.', 'leyka'),
        'title' => __('Text on "Thank you" screen', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., "Thank you! We appreciate your help! Let\'s stay in touch."', 'leyka'),
    ),
    'revo_thankyou_email_result_text' => array(
        'type' => 'text',
        'default' => __('We will inform you about the result by email', 'leyka'),
        'title' => __('Text on "Donation process complete" page', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., "We will inform you about the result by email"', 'leyka'),
    ),
    'commission' => array(
        'type' => 'custom_gateways_commission', // Special option type
        'title' => __('Payment operators commission', 'leyka'),
    ),
    'extensions_available' => array(
        'type' => 'custom_extensions', // Special option type
        'title' => __('Extensions available', 'leyka'),
    ),
    'admin_donations_list_display' => array(
        'type' => 'radio',
        'default' => 'amount-column',
        'title' => __('Total amount display on the admin donations list page', 'leyka'),
        'required' => true,
        'list_entries' => array(
            'none' => __('Do not display total amount', 'leyka'),
            'amount-column' => __('Display total amount in the amount column, with original amount value', 'leyka'),
            'separate-column' => __('Display total amount in the separate column', 'leyka'),
        ),
    ),
    'widgets_total_amount_usage' => array(
        'type' => 'radio',
        'default' => 'display-total',
        'title' => __('Total amount display in plugin widgets and shortcodes', 'leyka'),
        'required' => true,
        'list_entries' => array(
            'none' => __('Do not display total amount', 'leyka'),
            'display-total' => __('Display total amount values with original values', 'leyka'),
            'display-total-only' => __('Display only total amount values', 'leyka'),
        ),
    ),
    'success_page' => array(
        'type' => 'select',
        'default' => leyka_get_default_success_page(),
        'title' => __('Page of successful donation', 'leyka'),
        'description' => __('Select a page for donor to redirect to when payment is successful.', 'leyka'),
        'list_entries' => 'leyka_get_pages_list',
    ),
    'failure_page' => array(
        'type' => 'select',
        'default' => leyka_get_default_failure_page(),
        'title' => __('Page of failed donation', 'leyka'),
        'description' => __('Select a page for donor to redirect to when payment is failed for some reason.', 'leyka'),
        'list_entries' => 'leyka_get_pages_list',
    ),
    'donor_management_available' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __('Donor management available', 'leyka'),
        'comment' => __("Check to turn on the donors logging for all donations. It allows CRM functions and adds additional donors management pages to the plugin administation area.", 'leyka'),
        'short_format' => true,
    ),
    'donor_accounts_available' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __('Donor accounts available', 'leyka'),
        'comment' => __("Check to turn on the donors accounts features. It include donor's personal desktop pages and recurring donations management functions.", 'leyka'),
        'short_format' => true,
    ),
    'load_scripts_if_need' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('Load plugin scripts only if necessary', 'leyka'),
        'description' => __("Check this to load Leyka scripts and styles only on an applicable pages. If this box is unchecked, plugin will load it's scripts on every website page.", 'leyka'),
    ),
    'lang2upload' => array(
        'type' => 'select',
        'default' => 'ru',
        'title' => __('Select a language to load', 'leyka'),
        'description' => '<div class="field-errors"><span></span></div><div class="field-success"><span>'.esc_html__('The translation downloaded successfully. Reloading the page...', 'leyka').'</span></div>',
        'list_entries' => array(
            'ru' => __('Russian', 'leyka'),
        ),
    ),
    'delete_plugin_options' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __('Remove all plugin settings upon plugin uninstall', 'leyka'),
        'description' => __('WARNING: checking this checkbox will cause loss of all Leyka settings upon plugin uninstall. Please, proceed with caution.', 'leyka'),
    ),
    'delete_plugin_data' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __('Remove all plugin data upon plugin uninstall', 'leyka'),
        'description' => __('WARNING: checking this checkbox will cause loss of ALL Leyka data, including all donation and campaign transaction history, upon plugin uninstall. Please, proceed with caution.', 'leyka'),
    ),
    'donors_data_editable' => array(
        'type' => 'checkbox',
        'title' => __("You can edit donors' data for all donation types", 'leyka'),
        'description' => __("Donation administrators and managers are allowed to edit donors' data for non-correctional donations.", 'leyka'),
    ),
    'main_currency' => array(
        'type' => 'select',
        'default' => 'rur',
        'title' => __('Primary currency', 'leyka'),
        'required' => true,
        'list_entries' => array('rur' => __('RUB', 'leyka'), 'usd' => __('$', 'leyka'), 'eur' => __('euro', 'leyka'),),
    ),
    'plugin_demo_mode' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __('Demo mode on', 'leyka'),
        'comment' => __('Check to turn on the plugin demonstration mode. While in it, no emails will be sent to the payment gateways.', 'leyka'),
        'short_format' => true,
    ),
    'plugin_debug_mode' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __('Debugging mode on', 'leyka'),
        'comment' => __('Check to turn on the plugin debugging mode. Warning: some of the logic checks will not be performed if the mode is on. Please, use the option with caution.', 'leyka'),
        'short_format' => true,
    ),
    'plugin_stats_sync_enabled' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('The plugin usage statistics data synchronization is on', 'leyka'),
        'short_format' => true,
    ),
);
