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
    $settings['download_link_expiration'], $settings['disable_redownload']
    );

    $settings['redirect_on_add']['desc'] = __('Redirect to the checkout after adding the donation to the cart.', 'leyka');
    $settings['show_agree_to_terms']['desc'] = __('Show agreement to the terms checkbox. It will have to be checked to make a donation.', 'leyka');
    $settings['agree_label']['std'] = __('I agree to the terms of donation making service.', 'leyka');

    $settings['agree_text']['std'] = '
    1. Предмет договора*
    1. Благотворитель безвозмездно передает, а Благополучатель принимает товарно-материальные ценности и денежные средства для Целевого использования.

2. Условия выполнения договора
2.1. Благотворитель:
2.1.1. Производит целевое пожертвование в адрес Благополучателя в согласованном размере путем передачи товарно-материальных ценностей и денежных средств посредством их вручения, символической передачи.

2.2. Благополучатель:
2.2.1. Благополучатель в праве в любое время до передачи ему пожертвования от него отказаться. Отказ Благополучателя от пожертвования должен быть совершен в письменной форме. В этом случае договор оказания благотворительной помощи считается расторгнутым с момента получения Благотворителем отказа.

Благополучатель пожертвования обязуется использовать денежные средства, полученные по настоящему Договору, строго по целевому назначению в течение одного года с момента их поступления на расчетный счет Получателя пожертвования.

3. Ответственность Сторон, разрешение споров.
3.1. За неисполнение или ненадлежащее исполнение своих обязательств Стороны несут ответственность в соответствии с законодательством Российской Федерации.
3.2. Все споры и разногласия, возникающие в ходе исполнения настоящего Договора, Стороны будут стремиться решать путем переговоров.
3.3. Споры и разногласия, не разрешенные путем переговоров, подлежат разрешению в соответствии с действующим законодательством Российской Федерации.

4. Срок действия договора и прочие условия.
4.1. При выполнении пожертвования средствами web-сайта, отметка о согласии с условиями пожертвования подразумевает принятие условий публичной оферты.
4.2. Вопросы, не урегулированные настоящим Договором, регулируются действующим законодательством Российской Федерации.
4.3. Настоящий Договор составлен в 2-х подлинных экземплярах, имеющих одинаковую юридическую силу.

5. Адреса и реквизиты сторон'; //__('', 'leyka');

    $settings['checkout_label']['name'] = __('A text on a button to complete a donation', 'leyka');
    $settings['checkout_label']['desc'] = __('A text on a button to complete a donation.', 'leyka');
    $settings['checkout_label']['std'] = __('Make the donations', 'leyka');

    $settings['add_to_cart_text']['name'] = __('A text on "add to cart" button', 'leyka');
    $settings['add_to_cart_text']['std'] = __('Add donation to cart', 'leyka');

    return $settings;
}
add_filter('edd_settings_misc', 'leyka_misc_settings');