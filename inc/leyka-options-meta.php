<?php
/** @var $options_meta An array of initial options set, with default values of each field */
global $options_meta;

$email_placeholders =
"<span class='placeholders-help'>".
"<code>#SITE_NAME#</code> - ".__('a title of the website', 'leyka')."<br/>".
"<code>#ORG_NAME#</code> - ".__('an official title of the organization', 'leyka')."<br/>".
"<code>#DONATION_ID#</code> - ".__('an ID of current donation', 'leyka')."<br/>".
"<code>#DONOR_NAME#</code> - ".__('a name of the donor', 'leyka')."<br/>".
"<code>#SUM#</code> - ".__('a full sum of donation (without taking into account some payment commissions)', 'leyka')."<br/>".
"<code>#PAYMENT_METHOD_NAME#</code> - ".__('a name of payment method used', 'leyka')."<br/>".
"<code>#CAMPAIGN_NAME#</code> - ".__('a campaign to which donation was made', 'leyka')."<br/>".
"<code>#PURPOSE#</code> - ".__('a campaign title meant for payment system (see campaign settings)', 'leyka')."<br/>".
"<code>#DATE#</code> - ".__('a date of donation', 'leyka')."<br/>".
"</span>";

$agreement_placeholders =
"<span class='placeholders-help'>".
"<code>#LEGAL_NAME#</code> - ". __("a legal representative of the organization", 'leyka')."<br/>".
"<code>#LEGAL_FACE#</code> - ". __("a legal representative of the organization", 'leyka')."<br/>".
"<code>#LEGAL_FACE_RP#</code> - ". __("a legal representative of the organization (in genitive case)", 'leyka')."<br/>".
"<code>#LEGAL_FACE_POSITION#</code> - ". __("an official position of the legal representative", 'leyka')."<br/>".
"<code>#LEGAL_ADDRESS#</code> - ". __("an official organization's address", 'leyka')."<br/>".
"<code>#STATE_REG_NUMBER#</code> - ". __("a state registration number of your organization", 'leyka')."<br/>".
"<code>#KPP#</code> - ". __("an organization's statement of the account number", 'leyka')."<br/>".
"<code>#INN#</code> - ". __("an organization's individual taxpayer number", 'leyka')."<br/>".
"<code>#BANK_ACCOUNT#</code> - ". __("an organization's bank account number", 'leyka')."<br/>".
"<code>#BANK_NAME#</code> - ". __("an organization's bank name", 'leyka')."<br/>".
"<code>#BANK_BIC#</code> - ". __("an organization's bank indentification code", 'leyka')."<br/>".
"<code>#BANK_CORR_ACCOUNT#</code> - ". __("an organization's bank correspondent account", 'leyka')."<br/>".
"</span>";

$options_meta = apply_filters('leyka_core_options_meta', array(
    'org_full_name' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '',
        'title' => __('NGO name', 'leyka'),
        'description' => __('Full official name of an NGO.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => __('For ex., Eastern charity foundation', 'leyka'), // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'org_face_fio_ip' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '',
        'title' => __('A full name of person representing the NGO', 'leyka'),
        'description' => __("Enter a person's full name in nominative case.", 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => __('For ex., John Frederic Dow', 'leyka'), // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'org_face_fio_rp' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '',
        'title' => __('A full name of person representing the NGO, in genitive case', 'leyka'),
        'description' => __("Enter a person's full name in genitive case.", 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => __('For ex., John Frederic Dow', 'leyka'), // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'org_face_position' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '',
        'title' => __('Position of a person representing an NGO', 'leyka'),
        'description' => __('Enter an official position of a person representing the NGO.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => __('For ex., director', 'leyka'), // For text fields
        'length' => '60', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'org_address' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '',
        'title' => __("NGO's official address", 'leyka'),
        'description' => __('Enter an official address of an NGO.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => __('For ex., Malrose str., 4, Washington, DC, USA', 'leyka'), // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'org_state_reg_number' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '',
        'title' => __('A state registration number of an NGO', 'leyka'),
        'description' => __('Enter a state registration number of an NGO.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'org_kpp' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '',
        'title' => __("NGO's statement of the account number", 'leyka'),
        'description' => __("Enter a statement of the account number of an NGO.", 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'org_inn' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '',
        'title' => __("NGO's individual number of a taxpayer", 'leyka'),
        'description' => __('Enter an individual number of a taxpayer of an NGO.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'org_bank_account' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '',
        'title' => __("NGO's bank account number", 'leyka'),
        'description' => __('Enter a bank account number of an NGO', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'org_bank_name' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '',
        'title' => __("NGO's bank name", 'leyka'),
        'description' => __('Enter a full name for a bank of an NGO.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => __('For ex., First Columbia Credit Bank', 'leyka'), // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ), 
    'org_bank_bic' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '',
        'title' => __("NGO's bank BIC number", 'leyka'),
        'description' => __("Enter a BIC of the NGO's bank.", 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ), 
    'org_bank_corr_account' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '',
        'title' => __("NGO's correspondent bank account number", 'leyka'),
        'description' => __('Enter a correspondent account number of an NGO.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'donation_purpose_text' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => __('Charity donation', 'leyka'),
        'title' => __('A payment purpose text in the bank order', 'leyka'),
        'description' => '',
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => __('For ex., Charity donation', 'leyka'), // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'pm_available' => array(
        'type' => 'multi_checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => array(),
        'title' => __('Payment methods available on the donation forms', 'leyka'),
        'description' => __("Check out the payment methods through that you'd want to receive a donation payments.", 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise. For checkbox, 1 means "at least 1 value"
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => 'leyka_get_gateways_pm_list',
        'validation_rules' => array(), // List of regexp?..
    ),
    /** @todo Решили убрать эти опции за ненужностью. Если ненужность подтвердится, удалить совсем. */
//    'default_pm' => array(
//        'type' => 'select', // html, rich_html, select, radio, checkbox, multi_checkbox
//        'default' => 'yandex',
//        'title' => __('Default payment method selected on all donation forms', 'leyka'),
//        'description' => __("A payment method that'd be selected by default on all donation forms.", 'leyka'),
//        'required' => 0, // If no gateway selected as a default, there is no default selection on donation forms
//        'placeholder' => '', // For text fields
//        'length' => '', // For text fields
//        'list_entries' => 'leyka_get_gateways_pm_list',
//        'validation_rules' => array(), // List of regexp?..
//    ), 
//    'currencies_available' => array(
//        'type' => 'multi_select', // html, rich_html, select, multi_select, radio, checkbox, multi_checkbox
//        'default' => array(),
//        'title' => __('Currencies available for donations', 'leyka'),
//        'description' => __('Select a currencies that would be available for your donors', 'leyka'),
//        'required' => 1, // At least 1 must be selected
//        'placeholder' => '', // For text fields
//        'length' => 5, // For text and multi-select fields
//        'list_entries' => 'leyka_get_available_currencies', // callback returns the list of available currencies
//        'validation_rules' => array(), // List of regexp?..
//    ), 
//    'currency_main' => array(
//        'type' => 'select', // html, rich_html, select, radio, checkbox, multi_checkbox  
//         /** @todo Need to pass a callback here. Must think, how to do it */
//        'default' => '',
//        'title' => __('Main currency of your donations', 'leyka'),
//        'description' => __("Select the main currency of your donations. Most of the time, it would be your country's national one.", 'leyka'),
//        'required' => 0, // 1 if field is required, 0 otherwise
//        'placeholder' => '', // For text fields
//        'length' => '', // For text fields
//        'list_entries' => 'leyka_get_available_currencies', // callback returns currencies selected in prev. option
//        'validation_rules' => array(), // List of regexp?..
//    ),
//    'currency_position' => array(
//        'type' => 'radio', // html, rich_html, select, radio, checkbox, multi_checkbox
//        'default' => 'after',
//        'title' => __('Currency position in the money sum', 'leyka'),
//        'description' => __('Select a currency position that would be used while displaying sums of money.', 'leyka'),
//        'required' => 1, // 1 if field is required, 0 otherwise
//        'placeholder' => '', // For text fields
//        'length' => '', // For text fields
//        'list_entries' => array(
//            'before' => __('Before a sum (like $100)', 'leyka'),
//            'after' => __('After a sum (like 100¥)', 'leyka'),
//        ), // For select, radio & checkbox fields
//        'validation_rules' => array(), // List of regexp?..
//    ),
    'currency_rur_label' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => __('RUR', 'leyka'),
        'title' => __('RUR label', 'leyka'),
        'description' => __('Please set the RUR currency label here.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => 'For ex., «Roub.»', // For text fields
        'length' => 6, // For text fields
        'list_entries' => '', // callback returns currencies selected in prev. option
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_rur_min_sum' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => 100,
        'title' => __('Minimum sum available for RUR', 'leyka'),
        'description' => __('Please set minimum sum available for RUR donations.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '0', // For text fields
        'length' => 6, // For text fields
        'list_entries' => '', // callback returns currencies selected in prev. option
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_rur_max_sum' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => 30000,
        'title' => __('Maximum sum available for RUR', 'leyka'),
        'description' => __('Please set maximum sum available for RUR donations.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '30000', // For text fields
        'length' => 6, // For text fields
        'list_entries' => '', // callback returns currencies selected in prev. option
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_rur_flexible_default_amount' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => 500,
        'title' => __('Default amount of donation in RUR (for «flexible» donation type)', 'leyka'),
        'description' => __('Please, set a default amount of donation when RUR selected as currency.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '500', // For text fields
        'length' => 6, // For text fields
        'list_entries' => '', // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_rur_fixed_amounts' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '100,300,500,1000',
        'title' => __('Possible amounts of donation in RUR (for «fixed» donation type)', 'leyka'),
        'description' => __('Please, set possible amounts of donation in RUR when «fixed» donation type is selected. Only an integer non-negative values, separated with commas.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '100,300,500,1000', // For text fields
        'length' => 15, // For text fields
        'list_entries' => '', // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_usd_label' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => __('$', 'leyka'),
        'title' => __('USD label', 'leyka'),
        'description' => __('Please set the USD currency label here.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => 'For ex., «USD»', // For text fields
        'length' => 6, // For text fields
        'list_entries' => '', // callback returns currencies selected in prev. option
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_usd_min_sum' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => 10,
        'title' => __('Minimum sum available for USD', 'leyka'),
        'description' => __('Please set minimum sum available for USD donations.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '0', // For text fields
        'length' => 6, // For text fields
        'list_entries' => '', // callback returns currencies selected in prev. option
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_usd_max_sum' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => 1000,
        'title' => __('Maximum sum available for USD', 'leyka'),
        'description' => __('Please set maximum sum available for USD donations.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '1000', // For text fields
        'length' => 6, // For text fields
        'list_entries' => '', // callback returns currencies selected in prev. option
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_usd_flexible_default_amount' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => 10,
        'title' => __('Default amount of donation in USD (for «flexible» donation type)', 'leyka'),
        'description' => __('Please, set a default amount of donation when RUR selected as currency.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '10', // For text fields
        'length' => 6, // For text fields
        'list_entries' => '', // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_usd_fixed_amounts' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '3,5,10,15,50',
        'title' => __('Possible amounts of donation in USD (for «fixed» donation type)', 'leyka'),
        'description' => __('Please, set possible amounts of donation in USD when «fixed» donation type is selected. Only an integer non-negative values, separated with commas.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '3,5,10,15,50', // For text fields
        'length' => 15, // For text fields
        'list_entries' => '', // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_eur_label' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => __('euro', 'leyka'),
        'title' => __('EUR label', 'leyka'),
        'description' => __('Please set the EUR currency label here.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => 'For ex., «euro»', // For text fields
        'length' => 6, // For text fields
        'list_entries' => '', // callback returns currencies selected in prev. option
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_eur_min_sum' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => 3,
        'title' => __('Minimum sum available for EUR', 'leyka'),
        'description' => __('Please set minimum sum available for EUR donations.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '0', // For text fields
        'length' => 6, // For text fields
        'list_entries' => '', // callback returns currencies selected in prev. option
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_eur_max_sum' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => 650,
        'title' => __('Maximum sum available for EUR', 'leyka'),
        'description' => __('Please set maximum sum available for EUR donations.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '650', // For text fields
        'length' => 6, // For text fields
        'list_entries' => '', // callback returns currencies selected in prev. option
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_eur_flexible_default_amount' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => 5,
        'title' => __('Default amount of donation in EUR (for «flexible» donation type)', 'leyka'),
        'description' => __('Please, set a default amount of donation when EUR selected as currency.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '5', // For text fields
        'length' => 6, // For text fields
        'list_entries' => '', // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'currency_eur_fixed_amounts' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '3,5,10,100,500',
        'title' => __('Possible amounts of donation in EUR (for «fixed» donation type)', 'leyka'),
        'description' => __('Please, set possible amounts of donation in EUR when «fixed» donation type is selected. Only an integer non-negative values, separated with commas.', 'leyka'),
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '3,5,10,100,500', // For text fields
        'length' => 15, // For text fields
        'list_entries' => '', // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'email_from_name' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => get_bloginfo('name'),
        'title' => __('Notification emails sender name', 'leyka'),
        'description' => __('Enter the name that would be used in all notification emails as «from whom» field', 'leyka'),
        'required' => 0, // If it does't set, we're using the site title 
        'placeholder' => __('Ex., Daisy Foundation website', 'leyka'), // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'email_from' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => leyka_get_default_email_from(),
        'title' => __("Notification emails sender's email", 'leyka'),
        'description' => __('Enter the email from which all Leyka emails would be sended', 'leyka'),
        'required' => 0, // If it does't set, we're using the site email
        'placeholder' => __('Ex., donations@daisyfoundation.org', 'leyka'), // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'email_thanks_title' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => __('Thank you for your donation!', 'leyka'),
        'title' => __('A title of after-donation notice sended to a donor', 'leyka'),
        'description' => __('Enter the title of the notification (or thankful) email with donation data that would be sended to each donor right after his donation is made.', 'leyka'),
        'required' => 1,
        'placeholder' => __('Ex., Daisy Foundation thanks you for your kindness', 'leyka'), // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'email_thanks_text' => array(
        'type' => 'html', // Maybe, rich_html
        'default' => __('Hello, #DONOR_NAME#!<br /><br />You have chosed to make a #SUM# donation to the following charity campaign: #CAMPAIGN_NAME#, using #PAYMENT_METHOD_NAME#.<br /><br />Sincerely thank you, #SITE_NAME#', 'leyka'),
        'title' => __('A text of after-donation notice sent to a donor', 'leyka'),
        'description' => __('Enter the text of the notification email that would be sended to each donor right after his donation is made. It may include the following special entries:', 'leyka') . $email_placeholders,
        'required' => 1,
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'notify_donations_managers' => array(
        'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => '1',
        'title' => __('Notify website personal of each incoming donation', 'leyka'),
        'description' => __('Check to notify some website personnel (donations managers) of each incoming donation.', 'leyka'),
        'required' => 0, // 1 if field is required, 0 otherwise
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'donations_managers_emails' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => leyka_get_default_dm_list(), 
        'title' => __('A comma-separated emails to notify of incoming donation', 'leyka'),
        'description' => '',
        'required' => 0, // 1 if field is required, 0 otherwise
        'placeholder' => __('For ex., admin@daisyfoundation.org,yourmail@domain.com', 'leyka'),
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'email_notification_title' => array(
        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => __('New donation incoming', 'leyka'),
        'title' => __('A title of new donation notification email', 'leyka'),
        'description' => '',
        'required' => 0, // 1 if field is required, 0 otherwise
        'placeholder' => __('For ex., new donation incoming', 'leyka'),
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'email_notification_text' => array(
        'type' => 'html',
        'default' => __('Hello!<br /><br />A new donation has been made on a #SITE_NAME#:<br /><ul><li>Campaign: #CAMPAIGN_NAME#.</li><li>Amount: #SUM#.</li><li>Payment method: #PAYMENT_METHOD_NAME#.</li><li>Date: #DATE#</li></ul>', 'leyka'),
        'title' => __('A text of after-donation notification sended to a website personnel', 'leyka'),
        'description' => __("Enter the text of the notification email that would be sended to each email stated before right after donation is made. It may include the following special entries:", 'leyka').$email_placeholders,
        'required' => 0, // 1 if field is required, 0 otherwise
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'donation_form_template' => array(
        'type' => 'radio', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => 'radios',
        'title' => __('Select a template for all your donation forms', 'leyka'),
        'description' => __('Select one of the templates.', 'leyka'),
        'required' => 1,
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => 'leyka_get_form_templates_list',
        'validation_rules' => array(), // List of regexp?..
    ),
    'donation_sum_field_type' => array(
        'type' => 'radio', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => 'flexible',
        'title' => __('Select a type of the sum field for all your donation forms', 'leyka'),
        'description' => __('Select a type of the sum field. «Fixed» means a set of stable sum variants, while «flexible» is a free input field.', 'leyka'),
        'required' => 1,
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => array('flexible' => __('Flexible', 'leyka'), 'fixed' => __('Fixed', 'leyka')),
        'validation_rules' => array(), // List of regexp?..
    ),
	'donation_form_mode' => array(
        'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => 1,
        'title' => __('Display payment forms on campaign page automatically', 'leyka'),
        'description' => __('When unchecked payment forms will not be displayed automatically. Use following template tag somewhere in your campaign\'s template file - ', 'leyka')."&lt;?php leyka_get_donation_form();?&gt;",
        'required' => 1,
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => array(),
        'validation_rules' => array(), // List of regexp?..
    ),
    'argee_to_terms_needed' => array(
        'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
        'default' => 1,
        'title' => __('To donate, donor must agree to the Terms of service', 'leyka'),
        'description' => __('Check if you must have donor to accept some terms before donating.', 'leyka'),
        'required' => 0,
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'agree_to_terms_text' => array(
        'type' => 'text',
        'default' => __('I agree to the Terms of this donation service', 'leyka'),
        'title' => __('Label of checkbox of Terms acception', 'leyka'),
        'description' => __('Enter the text to show next to the checkbox to accept the Terms of service.', 'leyka'),
        'required' => 1,
        'placeholder' => __('For ex., I agree to the Terms of this donation service', 'leyka'),
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    'terms_of_service_text' => array(
        'type' => 'rich_html',
        'default' => __('Terms of donation service text', 'leyka'),
        'title' => __('A text of the Terms of donation service', 'leyka'),
        'description' => __('Enter a text that will be shown to the donors to read the Terms of service. It have to include the following special entries:', 'leyka') . $agreement_placeholders,
        'required' => 1, // 1 if field is required, 0 otherwise
        'placeholder' => '',
        'length' => '', // For text fields
        'list_entries' => array(), // For select, radio & checkbox fields
        'validation_rules' => array(), // List of regexp?..
    ),
    /** @todo Решили убрать эту опцию за ненужностью. Если ненужность подтвердится, удалить совсем. */
//    'test_mode_on' => array(
//        'type' => 'checkbox', // html, rich_html, select, radio, checkbox, multi_checkbox
//        'default' => '',
//        'title' => __('Test mode is on', 'leyka'),
//        'description' => __('Check to enable the test mode. While in test mode, the real payments will not be committed.', 'leyka'),
//        'required' => 0, // 1 if field is required, 0 otherwise
//        'placeholder' => '', // For text fields
//        'length' => '', // For text fields
//        'list_entries' => array(), // For select, radio & checkbox fields
//        'validation_rules' => array(), // List of regexp?..
//    ),
    'success_page' => array(
        'type' => 'select',
        'default' => leyka_get_default_success_page(),
        'title' => __('Page of successful donation', 'leyka'),
        'description' => __('Select a page for donor to redirect to when payment is successful.', 'leyka'),
        'required' => 0, // 1 if field is required, 0 otherwise
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => leyka_get_pages_list(),
        'validation_rules' => array(), // List of regexp?..
    ),
    'failure_page' => array(
        'type' => 'select',
        'default' => leyka_get_default_failure_page(),
        'title' => __('Page of failed donation', 'leyka'),
        'description' => __('Select a page for donor to redirect to when payment is failed for some reason.', 'leyka'),
        'required' => 0, // 1 if field is required, 0 otherwise
        'placeholder' => '', // For text fields
        'length' => '', // For text fields
        'list_entries' => leyka_get_pages_list(),
        'validation_rules' => array(), // List of regexp?..
    ),
    /** @todo Решили убрать эти опции за ненужностью. Если ненужность подтвердится, удалить совсем. */
//    'default_donation_status' => array(
//        'type' => 'select', // html, rich_html, select, radio, checkbox, multi_checkbox
//        'default' => 'submitted',
//        'title' => __('Default status for a new donation', 'leyka'),
//        'description' => __('Select a status that any new donations will be created with.', 'leyka'),
//        'required' => 1, // 1 if field is required, 0 otherwise
//        'placeholder' => '', // For text fields
//        'length' => '', // For text fields
//        'list_entries' => 'leyka_get_donation_status_list',
//        'validation_rules' => array(), // List of regexp?..
//    ),
//    'donate_submit_text' => array(
//        'type' => 'text', // html, rich_html, select, radio, checkbox, multi_checkbox
//        'default' => __('Donate!', 'leyka'),
//        'title' => __('A text for a button to make a donation', 'leyka'),
//        'description' => __('Enter the text for a button that must be pressed to make a donation.', 'leyka'),
//        'required' => 1, // 1 if field is required, 0 otherwise
//        'placeholder' => 'For ex., Donate!', // For text fields
//        'length' => '15', // For text fields
//        'list_entries' => array(), // For select, radio & checkbox fields
//        'validation_rules' => array(), // List of regexp?..
//    ),
));