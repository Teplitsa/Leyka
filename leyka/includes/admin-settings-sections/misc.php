<?php
/**
 * @package Leyka
 * @subpackage Settings -> Misc tab modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

// Changes in the Settings->Misc admin section:
function leyka_misc_settings($settings){
    unset(
        $settings['live_cc_validation'], $settings['logged_in_only'], $settings['show_register_form'],
        $settings['download_link_expiration'], $settings['disable_redownload'], $settings['symlink_file_downloads'],
        $settings['symlink_file_downloads'], $settings['file_download_limit'], $settings['field_downloads'],
        $settings['download_method'], $settings['accounting_settings'], $settings['enable_skus'],
        $settings['item_quantities'], $settings['allow_multiple_discounts']
    );

    array_unshift(
        $settings,
        array(
            'id' => 'leyka_donations_receiver_header',
            'name' => '<strong>'.__('Donations receiver', 'leyka').'</strong>',
            'desc' => __('Configure donations receiver options', 'leyka'),
            'type' => 'header'
        ), array(
            'id' => 'leyka_receiver_is_private',
            'name' => '',
            'desc' => '',
            'type' => 'radio',
            'options' => array('1' => __('A private person', 'leyka'), '0' => __('A legal entity', 'leyka'))
        ), array(
            'id' => 'leyka_receiver_legal_name',
            'name' => __('Official name of your organization', 'leyka'),
            'desc' => __("Full official name of your organization, as in it's Rules.", 'leyka'),
            'type' => 'text',
        ), array(
            'id' => 'leyka_receiver_legal_face',
            'name' => __('Juristic representative of the organization', 'leyka'),
            'desc' => '',
            'type' => 'text',
        ), array(
            'id' => 'leyka_receiver_legal_face_rp',
            'name' => __('Juristic representative of the organization (in genitive case)', 'leyka'),
            'desc' => '',
            'type' => 'text'
        ), array(
            'id' => 'leyka_receiver_legal_face_position',
            'name' => __('Official position of the juristic representative', 'leyka'),
            'desc' => '',
            'type' => 'text',
        ), /*array(
            'id' => 'leyka_receiver_legal_donations_purpose',
            'name' => __('Purpose of donations collecting', 'leyka'),
            'desc' => __('A purpose of donations, most often "For the duties required by the Rules".', 'leyka'),
            'type' => 'text',
            'std' => __('For the duties required by the Rules', 'leyka'),
        ),*/ array(
            'id' => 'leyka_receiver_legal_state_reg_number',
            'name' => __('State registration number of your organization', 'leyka'),
            'desc' => __('State registration number of your organization.', 'leyka'),
            'type' => 'text',
        ), array(
            'id' => 'leyka_receiver_legal_kpp',
            'name' => __("Organization's statement of the account number", 'leyka'),
            'desc' => '',
            'type' => 'text',
        ), array(
            'id' => 'leyka_receiver_legal_bank_essentials',
            'name' => __("Organization's bank account essentials", 'leyka'),
            'desc' => '',
            'type' => 'text',
        ), array(
            'id' => 'leyka_receiver_legal_address',
            'name' => __('Official organization address', 'leyka'),
            'desc' => '',
            'type' => 'text',
        ), array(
            'id' => 'leyka_receiver_private_settings',
            'name' => '<div id="leyka_receiver_private_settings" style="display:none;">'.__("All donations you collected as a private person will be taxed by 13%. Don't forget to pay the taxes! :)", 'leyka').'</div>',
            'desc' => '',
            'type' => 'header',
        )
    );

    $settings['redirect_on_add']['desc'] = __('Redirect to the checkout after adding the donation to the cart.', 'leyka');
    $settings['show_agree_to_terms']['desc'] = __('Show agreement to the terms checkbox. It will have to be checked to make a donation.', 'leyka');
    $settings['agree_label']['name'] = __('Link to the terms of agreement.', 'leyka');
    $settings['agree_label']['desc'] = __('Text of the link to the terms of agreement.', 'leyka');
    $settings['agree_label']['std'] = __('I agree to the terms of donation making service.', 'leyka');
    
    $settings['agree_text']['std'] = 'Публичная оферта о заключении договора пожертвования

#LEGAL_NAME#, в лице #LEGAL_FACE_RP#,
предлагает гражданам сделать пожертвование на ниже приведенных услових:

1. Общие положения
1.1. В соответствии с п. 2 ст. 437 Гражданского кодекса Российской Федерации данное предложение является публичной офертой (далее – Оферта).
1.2. В настоящей Оферте употребляются термины, имеющие следующее значение:
«Пожертвование» - «дарение вещи или права в общеполезных целях»;
«Жертвователь» - «граждане, делающие пожертвования»;
«Получатель пожертвования» - «#LEGAL_NAME#».

1.3. Оферта действует бессрочно с момента размещения ее на сайте Получателя пожертвования.
1.4. Получатель пожертвования вправе отменить Оферту в любое время путем удаления ее со страницы своего сайта в Интернете.
1.5. Недействительность одного или нескольких условий Оферты не влечет недействительность всех остальных условий Оферты.

2. Существенные условия договора пожертвования 
2.1. Пожертвование используется на содержание и ведение уставной деятельности Получателя пожертвования.
2.2. Сумма пожертвования определяется Жертвователем.

3. Порядок заключения договора пожертвования
3.1. В соответствии с п. 3 ст. 434 Гражданского кодекса Российской Федерации договор пожертвования заключается в письменной форме путем акцепта Оферты Жертвователем.
3.2. Оферта может быть акцептована путем перечисления Жертвователем денежных средств в пользу Получателя пожертвования платежным поручением по реквизитам, указанным в разделе 5 Оферты, с указанием в строке «назначение платежа»: «пожертвование на содержание и ведение уставной деятельности», а также с использованием пластиковых карт, электронных платежных систем и других средств и систем, позволяющих Жертвователю перечислять Получателю пожертвования денежных средств.
3.3. Совершение Жертвователем любого из действий, предусмотренных п. 3.2. Оферты, считается акцептом Оферты в соответствии с п. 3 ст. 438 Гражданского кодекса Российской Федерации.
3.4. Датой акцепта Оферты – датой заключения договора пожертвования является дата поступления пожертвования в виде денежных средств от Жертвователя на расчетный счет Получателя пожертвования.

4. Заключительные положения
4.1. Совершая действия, предусмотренные настоящей Офертой, Жертвователь подтверждает, что ознакомлен с условиями Оферты, целями деятельности Получателя пожертвования, осознает значение своих действий и имеет полное право на их совершение, полностью и безоговорочно принимает условия настоящей Оферты.
4.2. Настоящая Оферта регулируется и толкуется в соответствии с действующим российском законодательством.

5. Подпись и реквизиты Получателя пожертвования

#LEGAL_NAME#

ОГРН: #LEGAL_STATE_REG_NUMBER#
ИНН/КПП: #LEGAL_KPP#
Адрес места нахождения: #LEGAL_ADDRESS#

Банковские реквизиты:
#LEGAL_BANK_ESSENTIALS#


#LEGAL_FACE_POSITION#
#LEGAL_FACE#

_________________/Инициалы, Фамилия/
        (подпись)';
    $settings['agree_text']['desc'] = __('#LEGAL_NAME# - official organization title,<br />#LEGAL_FACE# - juristic representative of the organization<br />#LEGAL_FACE_RP# - juristic representative of the organization (in genitive case)<br />#LEGAL_FACE_POSITION# - official position of the juristic representative<br />#LEGAL_STATE_REG_NUMBER# - state registration number of your organization<br />#LEGAL_KPP# - statement of the account number<br />#LEGAL_ADDRESS# - official organization address<br />#LEGAL_BANK_ESSENTIALS# - organization bank account essentials', 'leyka');

    $settings['checkout_label']['name'] = __('A text on a button to complete a donation', 'leyka');
    $settings['checkout_label']['desc'] = __('A text on a button to complete a donation.', 'leyka');
    $settings['checkout_label']['std'] = __('Make the donations', 'leyka');

    $settings['add_to_cart_text']['name'] = __('A text on "add to cart" button', 'leyka');
    $settings['add_to_cart_text']['std'] = __('Add donation to cart', 'leyka');

    return $settings;
}
add_filter('edd_settings_misc', 'leyka_misc_settings');