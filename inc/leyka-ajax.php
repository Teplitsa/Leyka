<?php if( !defined('WPINC') ) die;
/** Different ajax handler functions */

function leyka_ajax_get_campaigns_list() {

    if(empty($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'leyka_get_campaigns_list_nonce')) {
        die(json_encode(array()));
    }

    $_REQUEST['term'] = empty($_REQUEST['term']) ? '' : trim($_REQUEST['term']);

    $campaigns = leyka_get_campaigns_list(array(
        'meta_query' => array(array(
            'key' => 'payment_title', 'value' => $_REQUEST['term'], 'compare' => 'LIKE', 'type' => 'CHAR',
        )),
    ), 0);

    $ids_found = array();
    $count = count($campaigns);
    for($i=0; $i<$count; $i++) {

        $ids_found[] = $campaigns[$i]->ID;
        $campaigns[$i] = array(
            'value' => $campaigns[$i]->ID,
            'label' => $campaigns[$i]->post_title,
            'payment_title' => get_post_meta($campaigns[$i]->ID, 'payment_title', true),
        );
    }

    foreach(leyka_get_campaigns_list(array('s' => $_REQUEST['term']), 0) as $campaign) { // Any criteria search - low priority
        if( !in_array($campaign->ID, $ids_found) ) {
            $campaigns[] = array(
                'value' => $campaign->ID,
                'label' => $campaign->post_title,
                'payment_title' => get_post_meta($campaign->ID, 'payment_title', true),
            );
        }
    }

    die(json_encode($campaigns));

}
add_action('wp_ajax_leyka_get_campaigns_list', 'leyka_ajax_get_campaigns_list');
add_action('wp_ajax_nopriv_leyka_get_campaigns_list', 'leyka_ajax_get_campaigns_list');

function leyka_recalculate_total_funded_action() {

    if( !wp_verify_nonce($_GET['nonce'], 'leyka_recalculate_total_funded_amount') ) {
        wp_die(__('Error: incorrect request parameters', 'leyka'));
    }

    if(empty($_GET['campaign_id'])) {
        wp_die(__('Error: campaign ID is missing', 'leyka'));
    }

    $campaign = new Leyka_Campaign((int)$_GET['campaign_id']);
    $campaign->update_total_funded_amount();

    wp_die($campaign->total_funded);

}
add_action('wp_ajax_leyka_recalculate_total_funded_amount', 'leyka_recalculate_total_funded_action');
add_action('wp_ajax_nopriv_leyka_recalculate_total_funded_amount', 'leyka_recalculate_total_funded_action');


function leyka_get_gateway_redirect_data() {

    leyka()->clear_session_errors(); // Clear all previous submits errors, if there are some

    $form_errors = Leyka_Payment_Form::is_form_fields_valid();
    if(is_array($form_errors) && count($form_errors) > 0) {

        $form_errors = reset($form_errors); // Return only the first error in the list

        /** @var WP_Error $form_errors */
        die(json_encode(array('status' => 1, 'message' => $form_errors->get_error_message(),)));

    }

    $pm = leyka_pf_get_payment_method_value();

    if(empty($_POST['without_form_submission'])) { // Normal donation submit procedure

        $donation_id = leyka()->log_submission();

        if( !is_wp_error($donation_id) ) {

            leyka_remember_donation_data(array('donation_id' => $donation_id));

            do_action(
                'leyka_payment_form_submission-'.$pm['gateway_id'],
                $pm['gateway_id'], implode('-', array_slice($pm, 1)), $donation_id, $_POST
            );

        }

        $payment_vars = array(
            'status' => 0,
            'payment_url' => apply_filters('leyka_submission_redirect_url-'.$pm['gateway_id'], '', $pm['payment_method_id']),
            'submission_redirect_type' => apply_filters(
                'leyka_submission_redirect_type-'.$pm['gateway_id'],
                'auto', $pm['payment_method_id'], $donation_id
            ),
        );

        if(is_wp_error($donation_id)) {

            $payment_vars['errors'] = $donation_id;
            $payment_vars['message'] = $donation_id->get_error_message();
            $payment_vars['status'] = 1;

        } else if(leyka()->payment_form_has_errors()) {

            $error = reset(leyka()->get_payment_form_errors());

            $payment_vars['errors'] = $error;
            $payment_vars['message'] = $error->get_error_message();
            $payment_vars['status'] = 1;

        } else { // Donation created
            $payment_vars['donation_id'] = $donation_id;
        }

        $payment_vars = array_merge(
            apply_filters('leyka_submission_form_data-'.$pm['gateway_id'], $_POST, $pm['payment_method_id'], $donation_id),
            $payment_vars
        );

        if(is_int($donation_id)) {
            leyka()->register_donor_account($donation_id);
        }

    } else { // Get payment vars without donation submit
        $payment_vars = array_merge(
            apply_filters('leyka_submission_form_data-'.$pm['gateway_id'], $_POST, $pm['payment_method_id'], false),
            array(
                'status' => 0,
                'payment_url' => apply_filters('leyka_submission_redirect_url-'.$pm['gateway_id'], '', $pm['payment_method_id']),
            )
        );
    }

    die(json_encode($payment_vars));

}
add_action('wp_ajax_leyka_ajax_get_gateway_redirect_data', 'leyka_get_gateway_redirect_data');
add_action('wp_ajax_nopriv_leyka_ajax_get_gateway_redirect_data', 'leyka_get_gateway_redirect_data');

function leyka_process_success_form() {

    if(empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'leyka_donor_subscription')) {
        die(json_encode(array(
            'status' => 1,
            'message' => __('Wrong nonce in the submitted data', 'leyka'),
        )));
    } else if(empty($_POST['leyka_donation_id'])) {
        die(json_encode(array(
            'status' => 1,
            'message' => __('No donation ID found in the submitted data', 'leyka'),
        )));
    }

    $donation = new Leyka_Donation((int)$_POST['leyka_donation_id']);
    if( !$donation ) {
        die(json_encode(array(
            'status' => 1,
            'message' => __('Wrong donation ID in the submitted data', 'leyka'),
        )));
    }

    if(isset($_POST['leyka_donor_name']) && leyka_validate_donor_name($_POST['leyka_donor_name'])) {
        $donation->donor_name = $_POST['leyka_donor_name'];
    }

    if(isset($_POST['leyka_donor_email']) && leyka_validate_email($_POST['leyka_donor_email'])) {

        $donation->donor_email = $donation->donor_email ? $donation->donor_email : $_POST['leyka_donor_email'];
        $donation->donor_subscription_email = $_POST['leyka_donor_email'];
        $donation->donor_subscribed = $donation->campaign_id;

    }

    leyka_remembered_data('donation_id', false, true); // Delete the donor data cookie

    die(json_encode(array(
        'status' => 0,
    )));

}
add_action('wp_ajax_leyka_donor_subscription', 'leyka_process_success_form');
add_action('wp_ajax_nopriv_leyka_donor_subscription', 'leyka_process_success_form');

function leyka_set_campaign_photo() {

    if(empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'set-campaign-photo')) {
        die(json_encode(array(
            'status' => 'error',
            'message' => __('Wrong nonce in the submitted data', 'leyka'),
        )));
    } else if(empty($_POST['campaign_id'])) {
        die(json_encode(array(
            'status' => 'error',
            'message' => __('Error: campaign ID is missing', 'leyka'),
        )));
    }

    $attachment_id = (int)$_POST['attachment_id'];
    $campaign_id = (int)$_POST['campaign_id'];

    update_post_meta($campaign_id, '_thumbnail_id', $attachment_id);
    sleep(1);

    die(json_encode(array(
        'status' => 'ok',
        'post' => $_POST,
    )));

}
add_action('wp_ajax_leyka_set_campaign_photo', 'leyka_set_campaign_photo');

function leyka_set_campaign_attachment() {

    $_POST['campaign_id'] = empty((int)$_POST['campaign_id']) ? false : (int)$_POST['campaign_id'];
    $_POST['attachment_id'] = empty((int)$_POST['attachment_id']) ? false : (int)$_POST['attachment_id'];

    if(empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'set-campaign-attachment')) {
        die(json_encode(array(
            'status' => 'error',
            'message' => __('Wrong nonce in the submitted data', 'leyka'),
        )));
    } else if(empty($_POST['campaign_id'])) {
        die(json_encode(array(
            'status' => 'error',
            'message' => __('Error: campaign ID is missing', 'leyka'),
        )));
    } else if(empty($_POST['field_name'])) {
        die(json_encode(array(
            'status' => 'error',
            'message' => __('Error: field name is missing', 'leyka'),
        )));
    }

    update_post_meta($_POST['campaign_id'], esc_attr($_POST['field_name']), $_POST['attachment_id']);
    sleep(1);

    die(json_encode(array(
        'status' => 'ok',
        'post' => $_POST,
    )));

}
add_action('wp_ajax_leyka_set_campaign_attachment', 'leyka_set_campaign_attachment');

function leyka_set_campaign_template() {

    if(empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'set-campaign-template')) {
        die(json_encode(array('status' => 'error', 'message' => __('Wrong nonce in the submitted data', 'leyka'),)));
    } else if(empty($_POST['campaign_id'])) {
        die(json_encode(array('status' => 'error', 'message' => __('Error: campaign ID is missing', 'leyka'),)));
    }

    update_post_meta((int)$_POST['campaign_id'], 'campaign_template', $_POST['template']);

    die(json_encode(array('status' => 'ok', 'post' => $_POST,)));

}
add_action('wp_ajax_leyka_set_campaign_template', 'leyka_set_campaign_template');

function leyka_edit_campaign_slug() {

    if(empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'leyka-edit-campaign-slug')) {
        die(json_encode(array('status' => 'error', 'message' => __('Wrong nonce in the submitted data', 'leyka'),)));
    } else if(empty($_POST['campaign_id']) || empty($_POST['slug'])) {
        die(json_encode(array('status' => 'error', 'message' => __('Error: the campaign data needed are missing', 'leyka'),)));
    }

    $campaign = get_post($_POST['campaign_id']);
    if( !$campaign || $campaign->post_type !== Leyka_Campaign_Management::$post_type ) {
        die(json_encode(array('status' => 'error', 'message' => __('Error: wrong campaign ID given', 'leyka'),)));
    }

    $_POST['slug'] = wp_unique_post_slug(sanitize_title($_POST['slug']), $_POST['campaign_id'], $campaign->post_status, $campaign->post_type, null);

    $res = wp_update_post(array(
        'ID' => (int)$_POST['campaign_id'],
        'post_name' => $_POST['slug'],
    ));

    if($res) {
        die(json_encode(array('status' => 'ok', 'slug' => $_POST['slug'],)));
    } else {
        die(json_encode(array('status' => 'error', 'message' => __("Error: the campaign slug wasn't updated", 'leyka'),)));
    }

}
add_action('wp_ajax_leyka_edit_campaign_slug', 'leyka_edit_campaign_slug');

function leyka_update_pm_list() {

    if(empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'leyka-update-pm-order')) {
        die(json_encode(array('status' => 'error', 'message' => __('Wrong nonce in the submitted data', 'leyka'),)));
    } else if(empty($_POST['pm_order'])) {
        die(json_encode(array('status' => 'error', 'message' => __('Error: PM order value is missing', 'leyka'),)));
    }

    leyka_options()->opt('pm_order', $_POST['pm_order']);
    leyka_options()->opt('pm_available', explode('&', str_replace('pm_order[]=', '', $_POST['pm_order'])));

    if( !empty($_POST['pm_labels']) && is_array($_POST['pm_labels']) ) {
        foreach($_POST['pm_labels'] as $pm_full_id => $pm_label) {
            leyka_options()->opt($pm_full_id, $pm_label);
        }
    }

    die(json_encode(array('status' => 'ok',)));

}
add_action('wp_ajax_leyka_update_pm_list', 'leyka_update_pm_list');

function leyka_upload_l10n() {

    $url = 'https://translate.wordpress.org/projects/wp-plugins/leyka/stable/ru/default/export-translations?format=mo';
    $file = download_url($url, 60);

    $res = null;

    if(is_wp_error($file)) {
        $res = array(
            'status' => 'error',
            'message' => "Ошибка! Не удалось скачать файл локализации. ".$file->get_error_message()
        );
    } else {

        if( !is_dir(WP_CONTENT_DIR."/languages") ) {
            $res = array('status' => 'error', 'message' => sprintf("Ошибка! Папка локализации не найдена: %s", WP_CONTENT_DIR . "/languages"));
        } elseif( !is_dir(WP_CONTENT_DIR.'/languages/plugins') ) {
            $res = array(
                'status' => 'error',
                'message' => sprintf("Ошибка! Папка локализации плагинов не найдена: %s", WP_CONTENT_DIR.'/languages/plugins')
            );
        } else {

            try {
                if(copy($file, WP_CONTENT_DIR.'/languages/plugins/leyka-ru_RU.mo')) {
                    unlink($file);
                } else {
                    $res = array(
                        'status' => 'error',
                        'message' => sprintf("Ошибка! Нет прав для записи в папку %s", WP_CONTENT_DIR . "/languages/plugins")
                    );
                }
            } catch(Exception $ex) {
                $res = array(
                    'status' => 'error',
                    'message' => "Ошибка! Не удалось установить файл локализации! ".$ex->getMessage()
                );
            }
        }

    }

    if( !$res ) {
        $res = array('status' => 'ok', 'message' => 'Перевод успешно загружен');
    }

    die(json_encode($res));

}
add_action('wp_ajax_leyka_upload_l10n', 'leyka_upload_l10n');

function leyka_ajax_get_env_and_options() {
    die('<pre>'.format_debug_data(humanaize_debug_data(leyka_get_env_and_options())).'</pre>');
}
add_action('wp_ajax_leyka_get_env_and_options', 'leyka_ajax_get_env_and_options');

function leyka_setup_donor_password() {

    $res = array('status' => 'ok', 'message' => __('The password is set. Welcome to your personal account!', 'leyka'));

    if(empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'leyka_account_password_setup')) {
        $res = array(
            'status' => 'error',
            'message' => sprintf(__('Wrong request. Please, <a href="mailto:%s" target="_blank">contact the website tech. support</a> about it.', 'leyka'), leyka()->opt('tech_support_email'))
        );
    } else if(
        empty($_POST['leyka_donor_pass'])
        || empty($_POST['leyka_donor_pass2'])
        || $_POST['leyka_donor_pass2'] !== $_POST['leyka_donor_pass2']
        || empty($_POST['donor_account_id'])
        || (int)$_POST['donor_account_id'] <= 0
    ) {
        $res = array(
            'status' => 'error',
            'message' => sprintf(__('Wrong request data. Please, <a href="mailto:%s" target="_blank">contact the website tech. support</a> about it.', 'leyka'), leyka()->opt('tech_support_email'))
        );
    } else {

        $donor_id = wp_update_user(array('ID' => (int)$_POST['donor_account_id'], 'user_pass' => $_POST['leyka_donor_pass']));
        if(is_wp_error($donor_id)) {
            $res = array(
                'status' => 'error',
                'message' => sprintf(__('The password cannot be updated. Please, <a href="mailto:%s" target="_blank">contact the website tech. support</a> about it.', 'leyka'), leyka()->opt('tech_support_email'))
            );
        } else {

            $donor_user = new WP_User($donor_id);
            update_user_meta($donor_user->ID, 'leyka_account_activation_code', false);

            if( !empty($_POST['auto-login']) ) { // Password initial setup (account activation)
                $donor_user = wp_signon(array(
                    'user_login' => $donor_user->user_login,
                    'user_password' => $_POST['leyka_donor_pass'],
                    'remember' => true,
                ));
            } else { // Password resetting
                $res = array('status' => 'ok', 'message' => sprintf(__('The password is changed. You may <a href="%s">log in</a>', 'leyka'), home_url('/donor-account/login/')));
            }

            if(is_wp_error($donor_user)) {
                $res = array('status' => 'error', 'message' => strip_tags($donor_user->get_error_message()),);
            }

        }

    }

    die(json_encode($res));

}
add_action('wp_ajax_leyka_setup_donor_password', 'leyka_setup_donor_password');
add_action('wp_ajax_nopriv_leyka_setup_donor_password', 'leyka_setup_donor_password');

function leyka_donor_login() {

    $res = array('status' => 'ok', 'message' => __('You are logged in and will be redirected in a moment. Welcome to your personal account :)', 'leyka'));

    if(empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'leyka_donor_login')) {
        $res = array(
            'status' => 'error',
            'message' => sprintf(__('Wrong request. Please, <a href="mailto:%s" target="_blank">contact the website tech. support</a> about it.', 'leyka'), leyka()->opt('tech_support_email'))
        );
    } else if(
        empty($_POST['leyka_donor_email'])
        || !is_email($_POST['leyka_donor_email'])
        || empty($_POST['leyka_donor_pass'])
    ) {
        $res = array(
            'status' => 'error',
            'message' => sprintf(__('Wrong request data. Please, <a href="mailto:%s" target="_blank">contact the website tech. support</a> about it.', 'leyka'), leyka()->opt('tech_support_email'))
        );
    } else {

        $donor = get_user_by('email', $_POST['leyka_donor_email']);
        if( !$donor ) {
            $res = array('status' => 'error', 'message' => __('Incorrect email or password.', 'leyka'),);
        } else {

            $donor_user = wp_signon(array(
                'user_login' => $donor->user_login,
                'user_password' => esc_sql($_POST['leyka_donor_pass']),
                'remember' => true,
            ));

            if(is_wp_error($donor_user)) {
                $res = array('status' => 'error', 'message' => strip_tags($donor_user->get_error_message()),);
            }

        }

    }

    die(json_encode($res));

}
add_action('wp_ajax_leyka_donor_login', 'leyka_donor_login');
add_action('wp_ajax_nopriv_leyka_donor_login', 'leyka_donor_login');

function leyka_donor_password_reset_request() {

    $res = array('status' => 'ok', 'message' => __('Your password is ready to reset! Check your email for the confirmation link.', 'leyka'));

    if(empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'leyka_donor_password_reset')) {
        $res = array(
            'status' => 'error',
            'message' => sprintf(__('Wrong request. Please, <a href="mailto:%s" target="_blank">contact the website tech. support</a> about it.', 'leyka'), leyka()->opt('tech_support_email'))
        );
    } else if(empty($_POST['leyka_donor_email']) || !is_email($_POST['leyka_donor_email'])) {
        $res = array(
            'status' => 'error',
            'message' => sprintf(__('Wrong request data. Please, <a href="mailto:%s" target="_blank">contact the website tech. support</a> about it.', 'leyka'), leyka()->opt('tech_support_email'))
        );
    } else {

        $donor = get_user_by('email', $_POST['leyka_donor_email']);
        if( !$donor ) {
            $res = array('status' => 'error', 'message' => __('Incorrect email.', 'leyka'),);
        } else {

            // get_password_reset_key(WP_User $user), check_password_reset_key( string $key, string $login )
            $pass_reset_key = get_password_reset_key($donor);
            $email_text = sprintf(
                __("Hello, %s!\n\nYou received this email because someone asked to reset your email on the <a href='%s'>%s</a> website.\n\nIf it was not you, just ignore this email and nothing will happen.\n\n If you really wish to reset your password, click <a href='%s' target='_blank'>here</a>.\n\nGood day to you!", 'leyka'),
                $donor->display_name,
                home_url(),
                get_bloginfo('name'),
                home_url('/donor-account/reset-password/?code='.$pass_reset_key.'&donor='.$_POST['leyka_donor_email'])
            );

            add_filter('wp_mail_content_type', 'leyka_set_html_content_type');

            $email_sent = wp_mail(
                $_POST['leyka_donor_email'],
                apply_filters('leyka_email_donor_password_reset_title', __('Your account access resetting', 'leyka'), $donor),
                wpautop(apply_filters('leyka_email_thanks_text', $email_text, $donor)),
                array('From: '.apply_filters(
                        'leyka_email_from_name',
                        leyka_options()->opt_safe('email_from_name'),
                        $donor
                    ).' <'.leyka_options()->opt_safe('email_from').'>',)
            );
            if( !$email_sent ) {
                $res = array(
                    'status' => 'error',
                    'message' => sprintf(__('Sorry, we could not send the password resetting email to you. Please, <a href="mailto:%s" target="_blank">contact the website tech. support</a> about it.', 'leyka'), leyka()->opt('tech_support_email'))
                );
            }

            remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');

        }

    }

    die(json_encode($res));

}
add_action('wp_ajax_leyka_donor_password_reset_request', 'leyka_donor_password_reset_request');
add_action('wp_ajax_nopriv_leyka_donor_password_reset_request', 'leyka_donor_password_reset_request');

function leyka_unsubscribe_persistent_campaign() {
    
    $res = array('status' => 'ok', 'message' => esc_html__('Your request to unsubscribe accepted', 'leyka'));
    
    if(empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'leyka_cancel_subscription')) {
        $res = array(
            'status' => 'error',
            'message' => sprintf(__('Wrong request. Please, <a href="mailto:%s" target="_blank">contact the website tech. support</a> about it.', 'leyka'), leyka()->opt('tech_support_email'))
        );
    } else if(empty($_POST['leyka_cancel_subscription_reason']) || empty($_POST['leyka_campaign_id'])) {
        $res = array(
            'status' => 'error',
            'message' => sprintf(__('Wrong request data. Please, <a href="mailto:%s" target="_blank">contact the website tech. support</a> about it.', 'leyka'), leyka()->opt('tech_support_email'))
        );
    } else {
        
        $campaign_id = (int)$_POST['leyka_campaign_id'];
        
        $donor = get_user_by('id', get_current_user_id());
        $campaign = new Leyka_Campaign($campaign_id);
        
        if( !$donor ) {
            $res = array('status' => 'error', 'message' => __('Operation allowed only for registered donors.', 'leyka'),);
            
        } elseif(!$campaign) {
            $res = array(
                'status' => 'error',
                'message' => sprintf(__('Campaign with ID %s not found. Please, <a href="mailto:%s" target="_blank">contact the website tech. support</a> about it.', 'leyka'), $campaign_id, leyka()->opt('tech_support_email'))
            );
            
        } else {
            
            $email_text = sprintf(
                __("Hello!\n\nDonor %s with email %s and ID %s would like to unsubscribe from campaign <a href='%s'>%s</a> with ID %s on the <a href='%s'>%s</a> website.\n\nIf it was not you, just ignore this email and nothing will happen.\n\n If you really wish to reset your password, click <a href='%s' target='_blank'>here</a>.\n\nGood day to you!", 'leyka'),
                $donor->display_name,
                $donor->user_email,
                $donor->ID,
                $campaign->permalink,
                $campaign->title,
                $campaign->ID,
                home_url(),
                get_bloginfo('name')
            );
            
            add_filter('wp_mail_content_type', 'leyka_set_html_content_type');
            
            foreach(explode(',', leyka_options()->opt('leyka_donations_managers_emails')) as $email) {
                $email_sent = wp_mail(
                    $email,
                    apply_filters('leyka_email_manager_cancel_subscription_title', __('New cancel campaign subscription request', 'leyka'), $donor, $campaign),
                    wpautop(apply_filters('leyka_email_manager_cancel_subscription_text', $email_text, $donor, $campaign)),
                    array('From: '.apply_filters(
                        'leyka_email_from_name',
                        leyka_options()->opt_safe('email_from_name'),
                        $donor
                        ).' <'.leyka_options()->opt_safe('email_from').'>',)
                    );
                
                if( !$email_sent ) {
                    $res = array(
                        'status' => 'error',
                        'message' => sprintf(__('Sorry, we could not send unsubscription request. Please, <a href="mailto:%s" target="_blank">contact the website tech. support</a> about it.', 'leyka'), leyka()->opt('tech_support_email'))
                    );
                    break;
                }
            }
            
            remove_filter('wp_mail_content_type', 'leyka_set_html_content_type');
        }
        
    }
    
    die(json_encode($res));
    
}
add_action('wp_ajax_leyka_unsubscribe_persistent_campaign', 'leyka_unsubscribe_persistent_campaign');
add_action('wp_ajax_nopriv_leyka_unsubscribe_persistent_campaign', 'leyka_unsubscribe_persistent_campaign');