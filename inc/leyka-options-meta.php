<?php if( !defined('WPINC') ) die;

$email_placeholders =
"<span class='placeholders-help'>
<code>#SITE_NAME#</code> — ".__('a website title', 'leyka')."<br>
<code>#SITE_EMAIL#</code> — ".__('a website email', 'leyka')."<br>
<code>#ORG_NAME#</code> — ".__('an organization official title', 'leyka')."<br>
<code>#DONATION_ID#</code> — ".__('an ID of current donation', 'leyka')."<br>
<code>#DONATION_TYPE#</code> — ".__('a type of current donation (single or recurring)', 'leyka')."<br>
<code>#DONOR_NAME#</code> — ".__("a donor's name", 'leyka')."<br>
<code>#DONOR_EMAIL#</code> — ".__("a donor's email", 'leyka')."<br>
<code>#SUM#</code> — ".__('a donation full amount (without payment commission)', 'leyka')."<br>
<code>#PAYMENT_METHOD_NAME#</code> — ".__('a payment method used', 'leyka')."<br>
<code>#CAMPAIGN_NAME#</code> — ".__('a campaign to which donation was made', 'leyka')."<br>
<code>#CAMPAIGN_TARGET#</code> — ".__('a campaign target amount', 'leyka')."<br>
<code>#PURPOSE#</code> — ".__('a campaign title for payment systems (see campaign settings)', 'leyka')."<br>
<code>#DATE#</code> — ".__('a donation date', 'leyka')."<br>
</span>";

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
</span>";

/** Possible field types are: text, textarea, number, html, rich_html, select, radio, checkbox, multi_checkbox, custom_XXX */

// For type='text':
// 'length' => '',
// 'placeholder' => '',
// 'required' => boolean,

// For type='number':
// 'length' => '',
// 'placeholder' => '',
// 'required' => 1 / 0
// 'min' => 0+ / false,
// 'max' => 0+ / false,
// 'step' => positive number,

// For select, radio, checkbox & multi_checkbox fields:
// 'list_entries' => array(
//  'value_variant' => 'Variant title',
//  or 'value_variant' => array('title' => 'Variant title', 'comment' => 'Variant comment')
//),
// 'required' => boolean / integer, // For multi_checkbox, any positive integer "N" means "at least N values"

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
        'title' => 'Выберите вашу страну', // __('Select your country:', 'leyka'),
        'required' => true,
        'list_entries' => array('-' => 'Не указано', 'ru' => 'Россия'), //'leyka_get_countries_list',
        'description' => 'Архитектура Лейки позволяет вам собирать деньги и в других странах. Узнайте, как подключить вашу страну <a href="//leyka.te-st.ru/instruction/">здесь</a>.',
    ),
    'receiver_legal_type' => array(
        'type' => 'radio',
        'title' => 'Тип получателя пожертвований', // __('The legal entity type', 'leyka'),
        'required' => true,
        'list_entries' => array(
            'legal' => array(
                'title' => 'НКО — юридическое лицо', // __('Legal entity', 'leyka'),
                'comment' => '',
            ),
            'physical' => array(
                'title' => 'Физическое лицо', // __('Physical entity', 'leyka'),
                'comment' => '',
            ),
        ),
        'description' => 'Помните, что как физическое лицо, вы должны сдать декларацию и оплатить подоходный налог — 13% от всех поступлений.',
    ),
    'org_full_name' => array(
        'type' => 'text',
        'title' => __('NGO name', 'leyka'),
        'description' => __('NGO full official name.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., Eastern charity foundation', 'leyka'),
    ),
    'org_short_name' => array(
        'type' => 'text',
        'title' => 'Сокращённое наименование организации', //__('NGO short name', 'leyka'),
//        'description' => __('NGO full official name.', 'leyka'),
        'required' => true,
//        'placeholder' => __('E.g., Eastern charity foundation', 'leyka'),
    ),
    'org_face_fio_ip' => array(
        'type' => 'text',
        'title' => 'Ф.И.О. руководителя', // __('Full name of a person representing the NGO', 'leyka'),
//        'description' => __("Enter a person's full name in subjective case.", 'leyka'),
        'required' => true,
//        'placeholder' => __('E.g., John Frederic Dow', 'leyka'),
    ),
    'org_contact_person_name' => array(
        'type' => 'text',
        'title' => 'Имя контактного лица', // __('Full name of a person representing the NGO', 'leyka'),
        'description' => 'Контактное лицо – это человек, курирующий настройку и установку Лейки, а также подключение к платежным операторам.', // __("Enter a person's full name in subjective case.", 'leyka'),
        'required' => false,
//        'placeholder' => __('E.g., John Frederic Dow', 'leyka'),
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
        'title' => 'Название должности руководителя', //__('Position of a person representing the NGO', 'leyka'),
        'default' => 'Директор', // __('director', 'leyka'),
        'description' => __('Enter an official position of a person representing the NGO.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., «Director»', 'leyka'),
    ),
    'org_address' => array(
        'type' => 'text',
        'title' => __('The NGO official address', 'leyka'),
        'description' => __('Enter the NGO official address.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., Malrose str., 4, Washington, DC, USA', 'leyka'),
    ),
    'org_state_reg_number' => array(
        'type' => 'text',
        'title' => __('The NGO state registration number', 'leyka'),
        'description' => __('Enter the NGO state registration number.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., 1023400056789', 'leyka'),
        'validation_rules' => array(),
    ),
    'org_kpp' => array(
        'type' => 'text',
        'title' => __('The NGO statement of the account number', 'leyka'),
        'description' => __("Enter the NGO statement of the account number.", 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., 780302015', 'leyka'),
    ),
    'org_inn' => array(
        'type' => 'text',
        'title' => __('The NGO taxpayer individual number', 'leyka'),
        'description' => __('Enter the NGO individual number of a taxpayer.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., 4283256127', 'leyka'),
    ),
    'org_bank_account' => array(
        'type' => 'text',
        'title' => __('The NGO bank account number', 'leyka'),
        'description' => __('Enter a bank account number of the NGO', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., 40123840529627089012', 'leyka'),
    ),
    'org_bank_name' => array(
        'type' => 'text',
        'title' => __('NGO bank name', 'leyka'),
        'description' => __('Enter a full name for the NGO bank.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., First Columbia Credit Bank', 'leyka'),
    ), 
    'org_bank_bic' => array(
        'type' => 'text',
        'title' => __('The NGO bank BIC number', 'leyka'),
        'description' => __("Enter a BIC of the NGO bank.", 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., 044180293', 'leyka'),
    ), 
    'org_bank_corr_account' => array(
        'type' => 'text',
        'title' => __('The NGO correspondent bank account number', 'leyka'),
        'description' => __('Enter a correspondent account number of the NGO.', 'leyka'),
        'required' => true,
        'placeholder' => __('E.g., 30101810270902010595', 'leyka'),
    ),
    'person_full_name' => array(
        'type' => 'text',
        'title' => 'Ваше Ф.И.О.', // __('NGO name', 'leyka'),
//        'description' => __('NGO full official name.', 'leyka'),
        'required' => false,
//        'placeholder' => __('E.g., John "the Unknown" Daw', 'leyka'),
        'comment' => 'Мы ни в коем случае не будем собирать информацию о вашем имени сами или передавать её третьим лицам.',
    ),
    'person_address' => array(
        'type' => 'text',
        'title' => 'Ваш адрес регистрации', // __('Your official address', 'leyka'),
        'required' => false,
        'placeholder' => __('E.g., Malrose str., 4, Washington, DC, USA', 'leyka'),
        'comment' => 'Мы ни в коем случае не будем собирать информацию о вашей почте сами или передавать её третьим лицам.',
    ),
    'person_inn' => array(
        'type' => 'text',
        'title' => 'ИНН', // __('The taxpayer individual number', 'leyka'),
        'required' => false,
        'placeholder' => __('E.g., 4283256127', 'leyka'),
    ),
    'person_bank_name' => array(
        'type' => 'text',
        'title' => 'Наименование банка', // __('NGO bank name', 'leyka'),
//        'description' => __('Enter a full name for the NGO bank.', 'leyka'),
        'placeholder' => __('E.g., First Columbia Credit Bank', 'leyka'),
    ),
    'person_bank_account' => array(
        'type' => 'text',
        'title' => 'Расчётный счёт', // __('The NGO bank account number', 'leyka'),
//        'description' => __('Enter a bank account number of the NGO', 'leyka'),
        'placeholder' => __('E.g., 40123840529627089012', 'leyka'),
    ),
    'person_bank_bic' => array(
        'type' => 'text',
        'title' => 'БИК банка', // __('The NGO bank BIC number', 'leyka'),
//        'description' => __("Enter a BIC of the NGO bank.", 'leyka'),
        'placeholder' => __('E.g., 044180293', 'leyka'),
    ),
    'person_bank_corr_account' => array(
        'type' => 'text',
        'title' => 'Корреспондентский счёт', // __('Your correspondent bank account number', 'leyka'),
//        'description' => __('Enter a correspondent account number of the NGO.', 'leyka'),
        'placeholder' => __('E.g., 30101810270902010595', 'leyka'),
    ),
    'send_plugin_stats' => array(
        'type' => 'radio',
        'default' => 'y',
        'title' => 'Сбор анонимных данных об использовании плагина', // __('Select a type of the sum field for all your donation forms', 'leyka'),
//        'description' => __('Select a type of the sum field. «Fixed» means a set of stable sum variants, while «flexible» is a free input field.', 'leyka'),
        'required' => true,
        'list_entries' => array(
            'y' => 'Да, согласен с отправкой данных', // __('Yes, I agree to send the data', 'leyka'),
            'n' => 'Нет, данные предоставлять не хочу', // __("No, I don't want to share the data", 'leyka'),
        ),
    ),
    'pm_available' => array(
        'type' => 'multi_checkbox',
        'default' => array('text-text_box'),
        'title' => __('Payment methods available on donation forms', 'leyka'),
        'description' => __("Check out payment methods through that you'd want to receive a donation payments.", 'leyka'),
        'required' => 1,
        'list_entries' => 'leyka_get_gateways_pm_list',
    ),
    'pm_order' => array(
        'type' => 'text', // It's intentionally of text type :) Option contains a serialized string of an array
        'default' => '', // PM will be ordered just as their gateways were added
        'title' => __('Payment methods order on donation forms', 'leyka'),
        'required' => 0,
    ),
    'auto_refresh_currency_rates' => array(
        'type' => 'checkbox',
        'default' => '1',
        'title' => __('Automatically refresh currency rates', 'leyka'),
        'description' => __('Check to enable auto-refresh of currency rates. It will be performed every 24 hours and will require connection with http://cbr.ru website.', 'leyka'),
        'required' => 0,
    ),
    'currency_rur2usd' => array(
        'type' => 'text',
        'title' => __('RUR to USD currency rate', 'leyka'),
        'description' => __('Please set the RUR to USD currency rate here.', 'leyka'),
        'required' => 1,
        'placeholder' => '70.01',
        'length' => 6,
    ),
    'currency_rur2eur' => array(
        'type' => 'text',
        'title' => __('RUR to EUR currency rate', 'leyka'),
        'description' => __('Please set the RUR to EUR currency rate here.', 'leyka'),
        'required' => 1,
        'placeholder' => '80.81',
        'length' => 6,
    ),
    'currency_rur_label' => array(
        'type' => 'text',
        'default' => __('RUR', 'leyka'),
        'title' => __('RUR label', 'leyka'),
        'description' => __('Please set the RUR currency label here.', 'leyka'),
        'required' => 1,
        'placeholder' => 'E.g., roub.',
        'length' => 6,
    ),
    'currency_rur_min_sum' => array(
        'type' => 'text',
        'default' => 100,
        'title' => __('Minimum sum available for RUR', 'leyka'),
        'description' => __('Please set minimum sum available for RUR donations.', 'leyka'),
        'required' => 1,
        'placeholder' => '100',
        'length' => 6,
    ),
    'currency_rur_max_sum' => array(
        'type' => 'text',
        'default' => 30000,
        'title' => __('Maximum sum available for RUR', 'leyka'),
        'description' => __('Please set maximum sum available for RUR donations.', 'leyka'),
        'required' => 1,
        'placeholder' => '30000',
        'length' => 6,
    ),
    'currency_rur_flexible_default_amount' => array(
        'type' => 'text',
        'default' => 500,
        'title' => __('Default amount of donation in RUR (for "flexible" donation type)', 'leyka'),
        'description' => __('Please, set a default amount of donation when RUR selected as currency.', 'leyka'),
        'required' => 1,
        'placeholder' => '500',
        'length' => 6,
    ),
    'currency_rur_fixed_amounts' => array(
        'type' => 'text',
        'default' => '100,300,500,1000',
        'title' => __('Possible amounts of donation in RUR (for "fixed" donation type)', 'leyka'),
        'description' => __('Please, set possible amounts of donation in RUR when "fixed" donation type is selected. Only an integer non-negative values, separated with commas.', 'leyka'),
        'required' => 1,
        'placeholder' => '100,300,500,1000',
        'length' => 25,
    ),
    'currency_usd_label' => array(
        'type' => 'text',
        'default' => __('$', 'leyka'),
        'title' => __('USD label', 'leyka'),
        'description' => __('Please set the USD currency label here.', 'leyka'),
        'required' => 1,
        'placeholder' => 'E.g., USD',
        'length' => 6,
    ),
    'currency_usd_min_sum' => array(
        'type' => 'text',
        'default' => 10,
        'title' => __('Minimum sum available for USD', 'leyka'),
        'description' => __('Please set minimum sum available for USD donations.', 'leyka'),
        'required' => 1,
        'placeholder' => '0',
        'length' => 6,
    ),
    'currency_usd_max_sum' => array(
        'type' => 'text',
        'default' => 1000,
        'title' => __('Maximum sum available for USD', 'leyka'),
        'description' => __('Please set maximum sum available for USD donations.', 'leyka'),
        'required' => 1,
        'placeholder' => '1000',
        'length' => 6,
    ),
    'currency_usd_flexible_default_amount' => array(
        'type' => 'text',
        'default' => 10,
        'title' => __('Default amount of donation in USD (for "flexible" donation type)', 'leyka'),
        'description' => __('Please, set a default amount of donation when RUR selected as currency.', 'leyka'),
        'required' => 1,
        'placeholder' => '10',
        'length' => 6,
    ),
    'currency_usd_fixed_amounts' => array(
        'type' => 'text',
        'default' => '3,5,10,15,50',
        'title' => __('Possible amounts of donation in USD (for «fixed» donation type)', 'leyka'),
        'description' => __('Please, set possible amounts of donation in USD when "fixed" donation type is selected. Only an integer non-negative values, separated with commas.', 'leyka'),
        'required' => 1,
        'placeholder' => '3,5,10,15,50',
        'length' => 25,
    ),
    'currency_eur_label' => array(
        'type' => 'text',
        'default' => __('euro', 'leyka'),
        'title' => __('EUR label', 'leyka'),
        'description' => __('Please set the EUR currency label here.', 'leyka'),
        'required' => 1,
        'placeholder' => 'E.g., euro',
        'length' => 6,
    ),
    'currency_eur_min_sum' => array(
        'type' => 'text',
        'default' => 3,
        'title' => __('Minimum sum available for EUR', 'leyka'),
        'description' => __('Please set minimum sum available for EUR donations.', 'leyka'),
        'required' => 1,
        'placeholder' => '0',
        'length' => 6,
    ),
    'currency_eur_max_sum' => array(
        'type' => 'text',
        'default' => 650,
        'title' => __('Maximum sum available for EUR', 'leyka'),
        'description' => __('Please set maximum sum available for EUR donations.', 'leyka'),
        'required' => 1,
        'placeholder' => '650',
        'length' => 6,
    ),
    'currency_eur_flexible_default_amount' => array(
        'type' => 'text',
        'default' => 5,
        'title' => __('Default amount of donation in EUR (for «flexible» donation type)', 'leyka'),
        'description' => __('Please, set a default amount of donation when EUR selected as currency.', 'leyka'),
        'required' => 1,
        'placeholder' => '5',
        'length' => 6,
    ),
    'currency_eur_fixed_amounts' => array(
        'type' => 'text',
        'default' => '3,5,10,100,500',
        'title' => __('Possible amounts of donation in EUR (for «fixed» donation type)', 'leyka'),
        'description' => __('Please, set possible amounts of donation in EUR when «fixed» donation type is selected. Only an integer non-negative values, separated with commas.', 'leyka'),
        'required' => 1,
        'placeholder' => '3,5,10,100,500',
        'length' => 25,
    ),
    'email_from_name' => array(
        'type' => 'text',
        'default' => get_bloginfo('name'),
        'title' => __('Notification emails sender name', 'leyka'),
        'description' => __('Enter the name that would be used in all notification emails as «from whom» field', 'leyka'),
        'placeholder' => __('E.g., Daisy Foundation website', 'leyka'),
        'comment' => 'Текст комментария к имени отправителя',
    ),
    'email_from' => array(
        'type' => 'text',
        'default' => leyka_get_default_email_from(),
        'title' => __("Notification emails sender's email", 'leyka'),
        'description' => __('Enter the email from which all Leyka emails would be sended', 'leyka'),
        'placeholder' => __('E.g., donations@daisyfoundation.org', 'leyka'),
        'comment' => 'Текст комментария к email отправителя',
    ),
    'email_thanks_title' => array(
        'type' => 'text',
        'default' => __('Thank you for your donation!', 'leyka'),
        'title' => __('A title of after-donation notice sended to a donor', 'leyka'),
        'description' => __('Enter the title of the notification (or thankful) email with donation data that would be sended to each donor right after his donation is made.', 'leyka'),
        'required' => 1,
        'placeholder' => __('E.g., Daisy Foundation thanks you for your kindness', 'leyka'),
    ),
    'email_thanks_text' => array(
        'type' => 'html',
        'default' => __('Hello, #DONOR_NAME#!<br><br>You have chosed to make a #SUM# donation to the following charity campaign: #CAMPAIGN_NAME#, using #PAYMENT_METHOD_NAME#.<br><br>Sincerely thank you,<br>#ORG_NAME#', 'leyka'),
        'title' => __('A text of after-donation notice sent to a donor', 'leyka'),
        'description' => __('Enter the text of the notification email that would be sended to each donor right after his donation is made. It may include the following special entries:', 'leyka').$email_placeholders,
        'required' => 1,
        'comment' => 'Текст комментария к тексту спасибо-письма',
    ),
    'email_recurring_init_thanks_title' => array(
        'type' => 'text',
        'default' => __('Thank you for your support!', 'leyka'),
        'title' => __('A title of an initial recurring donation notice sent to a donor', 'leyka'),
        'description' => __('Enter a title of a notification email with donation data that would be sended to each donor on each rebill donation.', 'leyka'),
        'required' => 1,
        'placeholder' => __('E.g., Daisy Foundation thanks you for your kindness', 'leyka'),
    ),
    'email_recurring_init_thanks_text' => array(
        'type' => 'html',
        'default' => __('Hello, #DONOR_NAME#!<br><br>We just took a #SUM# from your account as a regular donation to the campaign «#CAMPAIGN_NAME#», using #PAYMENT_METHOD_NAME#.<br><br>If you, regretfully, wish to stop future regular donations to this campaign, please #RECURRING_SUBSCRIPTION_CANCELLING_LINK#.<br><br>Sincerely thank you,<br>#ORG_NAME#', 'leyka'),
        'title' => __('A text of a recurring subscription donation notice sent to a donor', 'leyka'),
        'description' => __('Enter the text of the notification email that would be sended to each donor on each rebill donation. It may include the following special entries:', 'leyka').$email_placeholders,
        'required' => 1,
    ),
    'email_recurring_ongoing_thanks_title' => array(
        'type' => 'text',
        'default' => __('Thank you for your unwavering support!', 'leyka'),
        'title' => __('A title of an after-rebill donation notice for a donor', 'leyka'),
        'description' => __('Enter a title of a donor notification email with donation data that will be sent on each recurring auto-payment.', 'leyka'),
        'required' => 1,
    ),
    'email_recurring_ongoing_thanks_text' => array(
        'type' => 'html',
        'default' => __('Hello, #DONOR_NAME#!<br><br>We just took a #SUM# from your account as a regular donation to the campaign «#CAMPAIGN_NAME#», using #PAYMENT_METHOD_NAME#.<br><br>If you, regretfully, wish to stop future regular donations to this campaign, please #RECURRING_SUBSCRIPTION_CANCELLING_LINK#.<br><br>Sincerely thank you,<br>#ORG_NAME#', 'leyka'),
        'title' => __('A text of after-rebill donation notice sent to a donor', 'leyka'),
        'description' => __('Enter the text of the notification email that would be sended to each donor on each rebill donation. It may include the following special entries:', 'leyka').$email_placeholders,
        'required' => 1,
    ),
    'send_donor_thanking_emails' => array(
        'type' => 'checkbox',
        'default' => '1',
        'title' => __('Send a thankful email to a donor on each funded donation', 'leyka'),
        'description' => __('Check to send a thankful email to a donor on each funded donation', 'leyka'),
    ),
    'send_donor_emails_on_campaign_target_reaching' => array(
        'type' => 'checkbox',
        'default' => '1',
        'title' => __("Send campaign reaching email notifications to all it's donors", 'leyka'),
        'description' => __('Check to send a special thankful email to each donor when campaign target reached', 'leyka'),
    ),
    'email_campaign_target_reaching_title' => array(
        'type' => 'text',
        'default' => __('Thanks to you, the campaign succeeded!', 'leyka'),
        'title' => __('A title of an email notification sent to each donor when campaign target reached', 'leyka'),
        'description' => __('Enter the title of an email.', 'leyka'),
        'required' => 1,
        'placeholder' => __('E.g., Thanks to you, the campaign succeeded!', 'leyka'),
    ),
    'email_campaign_target_reaching_text' => array(
        'type' => 'html',
        'default' => __("Hello, #DONOR_NAME#!<br><br>You've donated #SUM# totally to the campaign: «#CAMPAIGN_NAME#».<br><br>We're glad to tell that just now this campaign successfully finished!<br><br>We heartfully thank you for your support,<br>#ORG_NAME#", 'leyka'),
        'title' => __('A text of a notification email sent to each donor when campaign target reached', 'leyka'),
        'description' => __('Enter the text of a notification email sent to each donor when campaign target reached. The text may include the following special entries:', 'leyka').$campaign_target_reaching_email_placeholders,
        'required' => 1,
    ),
    'notify_donations_managers' => array(
        'type' => 'checkbox',
        'default' => '1',
        'title' => __('Notify website personal of each incoming donation', 'leyka'),
        'description' => __('Check to notify some website personnel (donations managers) of each incoming donation', 'leyka'),
    ),
    'notify_managers_on_recurrents' => array(
        'type' => 'checkbox',
        'default' => '1',
        'title' => __('Notify website personal of each incoming recurrent donation', 'leyka'),
        'description' => __('Check to notify some website personnel (donations managers) of each incoming recurrent donation', 'leyka'),
    ),
    'donations_managers_emails' => array(
        'type' => 'text',
        'default' => leyka_get_default_dm_list(), 
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
        'type' => 'html',
        'default' => __('Hello!<br><br>A new donation has been made on a #SITE_NAME#:<br><ul><li>Campaign: #CAMPAIGN_NAME#.</li><li>Donation purpose: #PURPOSE#</li><li>Amount: #SUM#.</li><li>Payment method: #PAYMENT_METHOD_NAME#.</li><li>Date: #DATE#</li></ul><br><br>Your Leyka', 'leyka'),
        'title' => __('A text of after-donation notification sended to a website personnel', 'leyka'),
        'description' => __("Enter the text of the notification email that would be sended to each email stated before right after donation is made. It may include the following special entries:", 'leyka').$email_placeholders,
    ),
    'tech_support_email' => array(
        'type' => 'text',
        'title' => __('Website technical support email', 'leyka'),
        'description' => __('E-mail that you want to use to collect technical support requests from the donors.', 'leyka'),
        'placeholder' => __('E.g. «techsupport@email.com»', 'leyka'),
    ),
    'donation_form_template' => array(
        'type' => 'radio',
        'default' => 'radios',
        'title' => __('Select a default template for all your donation forms', 'leyka'),
        'description' => __('Select one of the form templates.', 'leyka'),
        'required' => 1,
        'list_entries' => 'leyka_get_form_templates_list',
    ),
    'donation_sum_field_type' => array(
        'type' => 'radio',
        'default' => 'flexible',
        'title' => __('Select a type of the sum field for all your donation forms', 'leyka'),
        'description' => __('Select a type of the sum field. «Fixed» means a set of stable sum variants, while «flexible» is a free input field.', 'leyka'),
        'required' => 1,
        'list_entries' => array(
            'flexible' => __('Flexible', 'leyka'),
            'fixed' => __('Fixed', 'leyka'),
            'mixed' => __('Fixed sum variants + flexible field', 'leyka')
        ),
    ),
    'donation_form_mode' => array(
        'type' => 'checkbox',
        'default' => 1,
        'title' => __('Display all payment elements on campaign page automatically', 'leyka'),
        'description' => __("When unchecked, all payment elements like donation forms or target completion level widgets will not be displayed automatically. To output them manually, use shortcodes inside campaign content or template tags in campaign template file.", 'leyka'),
        'required' => 1,
    ),
    'scale_widget_place' => array(
        'type' => 'radio',
        'default' => 'top',
        'title' => __('Select where Target completion widget will be placed at campaign pages', 'leyka'),
        'required' => 1,
        'list_entries' => array(
            'top' => __('Above page content', 'leyka'),
            'bottom' => __('Below page content', 'leyka'),
            'both' => __('Both', 'leyka'),
            '-' => __('Nowhere', 'leyka'),
        ),
    ),
    'donations_history_under_forms' => array(
        'type' => 'checkbox',
        'default' => 1,
        'title' => __('Donations history widget below donation forms', 'leyka'),
        'description' => __('Display the widget automatically', 'leyka'),
    ),
    'show_campaign_sharing' => array(
        'type' => 'checkbox',
        'default' => 1,
        'title' => __('Campaign sharing widget below donation forms', 'leyka'),
        'description' => __('Display the widget automatically', 'leyka'),
    ),
    'show_success_widget_on_success' => array(
        'type' => 'checkbox',
        'default' => 1,
        'title' => __('Show an email subscription widget on the successful donation page', 'leyka'),
        'description' => __('Display the widget automatically', 'leyka'),
    ),
    'show_failure_widget_on_failure' => array(
        'type' => 'checkbox',
        'default' => 1,
        'title' => __('Show a failure notification widget on the donation page', 'leyka'),
        'description' => __('Display the widget automatically', 'leyka'),
    ),
    'revo_template_slider_max_sum' => array(
        'type' => 'text',
        'default' => 3000,
        'title' => __('Maximum sum available for slider', 'leyka'),
        'description' => __('Please set the maximum sum available for slider control.', 'leyka'),
        'required' => 1,
        'placeholder' => '3000',
        'length' => 6,
    ),
    'show_donation_comment_field' => array(
        'type' => 'checkbox',
        'default' => false,
        'title' => __('Display a comment textarea field on donation forms', 'leyka'),
        'description' => __("Check to include an additional textarea field (a donor's comment) on all donation forms", 'leyka'),
    ),
    'donation_comment_max_length' => array(
        'type' => 'number',
        'default' => 140,
        'title' => __('The maximum length of a donation comment value', 'leyka'),
        'description' => __('Set the maximum number of symbols allowed for donation comments. You may set "0" for the unlimited values length.', 'leyka'),
        'placeholder' => __('E.g., 140', 'leyka'),
        'min' => 0,
        'max' => false,
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
        'default' => 1,
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
        'placeholder' => __('E.g., «I agree with»', 'leyka'),
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
        'default' => __('Terms of donation service text. Use <br> for line-breaks, please.', 'leyka'),
        'title' => __('A text of Terms of donation service', 'leyka'),
        'description' => __('Enter a Terms of Service text. The text may include following special entries:', 'leyka')."<span class='placeholders-help'>
            <code>#LEGAL_NAME#</code> — ".__("the organization legal name", 'leyka')."<br>
            <code>#LEGAL_FACE#</code> — ".__("the organization legal representative", 'leyka')."<br>
            <code>#LEGAL_FACE_RP#</code> — ".__("the organization legal representative (in genitive case)", 'leyka')."<br>
            <code>#LEGAL_FACE_POSITION#</code> — ".__("an official position of the organization legal representative", 'leyka')."<br>
            <code>#LEGAL_ADDRESS#</code> — ".__("the organization legal address", 'leyka')."<br>
            <code>#STATE_REG_NUMBER#</code> — ".__("the organization state registration number", 'leyka')."<br>
            <code>#KPP#</code> — ".__("the organization statement of the account number", 'leyka')."<br>
            <code>#INN#</code> — ".__("the organization individual taxpayer number", 'leyka')."<br>
            <code>#BANK_ACCOUNT#</code> — ".__("the organization bank account number", 'leyka')."<br>
            <code>#BANK_NAME#</code> — ".__("the organization bank name", 'leyka')."<br>
            <code>#BANK_BIC#</code> — ".__("the organization bank indentification code", 'leyka')."<br>
            <code>#BANK_CORR_ACCOUNT#</code> — ".__("the organization bank correspondent account", 'leyka')."<br>
            </span>",
        'required' => true,
    ),
    'person_terms_of_service_text' => array(
        'type' => 'rich_html',
        'default' => __('Terms of donation service text. Use <br> for line-breaks, please.', 'leyka'),
        'title' => __('A text of Terms of donation service', 'leyka'),
        'required' => true,
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
            'page' => __('Opens the page of Terms text', 'leyka')
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
        'description' => __("Enter a donors' personal data usage Terms text. The text may include following special entries:", 'leyka')."<span class='placeholders-help'>
            <code>#LEGAL_NAME#</code> — ".__("the organization legal name", 'leyka')."<br>
            <code>#LEGAL_ADDRESS#</code> — ".__("the organization legal address", 'leyka')."<br>
            <code>#SITE_URL#</code> — ".__("the website homepage URL", 'leyka')."<br>
            <code>#PD_TERMS_PAGE_URL#</code> — ".__("the website personal data terms page URL", 'leyka')."<br>
            <code>#ADMIN_EMAIL#</code> — ".__("the website administrator email", 'leyka')."<br>
            </span>",
        'required' => true,
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
            'page' => __('Opens the page of Terms text', 'leyka')
        ),
    ),
    'donation_submit_text' => array(
        'type' => 'text',
        'default' => __('Donate', 'leyka'),
        'title' => __('Label of the button to submit a donation form', 'leyka'),
        'description' => __('Enter the text for a submit buttons on a donation forms.', 'leyka'),
        'required' => 1,
        'placeholder' => __('E.g., "Donate" or "Support"', 'leyka'),
    ),
    'revo_donation_complete_button_text' => array(
        'type' => 'text',
        'default' => __('Complete donation', 'leyka'),
        'title' => __('Label of the button to complete a donation', 'leyka'),
        'description' => __('Enter the text for a complete donation buttons on a donation forms.', 'leyka'),
        'required' => 1,
        'placeholder' => __('E.g., "Complete the donation" or "Close the form"', 'leyka'),
    ),
    'revo_thankyou_text' => array(
        'type' => 'text',
        'default' => __('Thank you! We appreciate your help! Let\'s stay in touch.', 'leyka'),
        'title' => __('Text on "Thank you" screen', 'leyka'),
        'required' => 1,
        'placeholder' => __('E.g., "Thank you! We appreciate your help! Let\'s stay in touch."', 'leyka'),
    ),
    'revo_thankyou_email_result_text' => array(
        'type' => 'text',
        'default' => __('We will inform you about the result by email', 'leyka'),
        'title' => __('Text on "Donation process complete" page', 'leyka'),
        'required' => 1,
        'placeholder' => __('E.g., We will inform you about the result by email', 'leyka'),
    ),
    'commission' => array(
        'type' => 'custom_gateways_commission', // Special option type
        'title' => __('Payment operators commission', 'leyka'),
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
    'load_scripts_if_need' => array(
        'type' => 'checkbox',
        'default' => true,
        'title' => __('Load plugin scripts only if necessary', 'leyka'),
        'description' => __("Check this to load Leyka scripts and styles only on an applicable pages. If this box is unchecked, plugin will load it's scripts on every website page.", 'leyka'),
    ),
    'delete_plugin_options' => array(
        'type' => 'checkbox',
        'default' => 0,
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
        'required' => 1,
        'list_entries' => array('rur' => __('RUR', 'leyka'), 'usd' => __('$', 'leyka'), 'eur' => __('euro', 'leyka'),),
    ),
);