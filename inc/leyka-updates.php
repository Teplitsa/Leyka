<?php if( !defined('WPINC') ) die;

/** The plugin updates code wrappers. The code works on plugin activation if needed, dependent on the plugin new ver. */

function leyka_create_separate_donations_db_tables() {

    $leyka_last_ver = get_option('leyka_last_ver');
    if($leyka_last_ver && $leyka_last_ver == LEYKA_VERSION) { // Already at last version
        return;
    }

    global $wpdb;

    if($leyka_last_ver && version_compare($leyka_last_ver, '3.20.0.1', '<')) {

        $charset_collate = $wpdb->get_charset_collate();

        $have_innodb = $wpdb->get_var("SELECT COUNT(ENGINE)
            FROM INFORMATION_SCHEMA.ENGINES
            WHERE ENGINE LIKE 'innodb' AND SUPPORT != 'NO'", ARRAY_A
        );
        $use_innodb = $have_innodb ? 'ENGINE=InnoDB' : '';

        require_once(ABSPATH.'wp-admin/includes/upgrade.php');

        // About SQL format:
        // 1. Each field MUST be in separate line.
        // 2. There must be TWO spaces between PRIMARY KEY and its name. E.g.: PRIMARY KEY[space][space](id)
        // Otherwise dbDelta won't work.

        $table_name = $wpdb->prefix.'leyka_donations';
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

            dbDelta("CREATE TABLE $table_name (
                ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                campaign_id bigint(20) UNSIGNED NOT NULL,
                status varchar(20) NOT NULL,
                payment_type varchar(20) NOT NULL,
                date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                gateway_id varchar(30) NOT NULL,
                pm_id varchar(30) NOT NULL,
                currency_id varchar(10) NOT NULL,
                amount float NOT NULL,
                amount_total float NOT NULL,
                amount_in_main_currency float NOT NULL,
                amount_total_in_main_currency float NOT NULL,
                donor_name varchar(100) NOT NULL,
                donor_email varchar(100) NOT NULL,
                PRIMARY KEY  (ID),
                KEY campaign_id_index (campaign_id)
            ) {$charset_collate} {$use_innodb};");

        }

        $table_name = $wpdb->prefix.'leyka_donations_meta';
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

            dbDelta("CREATE TABLE $table_name (
                meta_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                donation_id bigint(20) UNSIGNED NOT NULL,
                meta_key varchar(120) NOT NULL,
                meta_value longtext,
                PRIMARY KEY  (meta_id),
                KEY donation_id_fk (donation_id),
                KEY meta_key_index (meta_key)
            ) {$charset_collate} {$use_innodb};");

            // Add foreign keys:
            dbDelta("ALTER TABLE {$table_name}
                ADD FOREIGN KEY (donation_id) REFERENCES {$wpdb->prefix}leyka_donations (ID) ON DELETE CASCADE;");

        }

    }

}

function leyka_handle_plugin_update() {

    $leyka_last_ver = get_option('leyka_last_ver');

    if($leyka_last_ver && $leyka_last_ver == LEYKA_VERSION) { // Already at last version
        return;
    }

    leyka_create_separate_donations_db_tables(); // Create plugin-specific DB tables if needed

    if($leyka_last_ver && version_compare($leyka_last_ver, '3.8.0.1', '<=')) { // CP IPs list fix
        if(get_option('leyka_cp_ip')) {
            update_option('leyka_cp_ip', '130.193.70.192, 185.98.85.109, 87.251.91.160/27, 185.98.81.0/28');
        }
    }

    if($leyka_last_ver && version_compare($leyka_last_ver, '3.15', '<=')) {
        update_option('leyka_yandex-yandex_money_label', __('YooMoney', 'leyka'));
    }

    if($leyka_last_ver && version_compare($leyka_last_ver, '3.20.0.1', '<=')) {

        global $wpdb;

        // Old (rur) to new (rub) currency ID transition:
        if(Leyka_Options_Controller::get_option_value('currency_main') == 'rur') {

            Leyka_Options_Controller::set_option_value('currency_main', 'rub'); // Rename the main currency option value

            // Migrate the old (RUR) currency options to the new (RUB) ones:
            $tmp_value = Leyka_Options_Controller::get_option_value('currency_rur_label');
            if($tmp_value) {
                Leyka_Options_Controller::set_option_value('currency_rub_label', $tmp_value);
            }
            delete_option('leyka_currency_rur_label');

            $tmp_value = Leyka_Options_Controller::get_option_value('currency_rur_min_sum');
            if($tmp_value) {
                Leyka_Options_Controller::set_option_value('currency_rub_min_sum', $tmp_value);
            }
            delete_option('leyka_currency_rur_min_sum');

            $tmp_value = Leyka_Options_Controller::get_option_value('currency_rur_max_sum');
            if($tmp_value) {
                Leyka_Options_Controller::set_option_value('currency_rub_max_sum', $tmp_value);
            }
            delete_option('leyka_currency_rur_max_sum');

            $tmp_value = Leyka_Options_Controller::get_option_value('currency_rur_flexible_default_amount');
            if($tmp_value) {
                Leyka_Options_Controller::set_option_value('currency_rub_flexible_default_amount', $tmp_value);
            }
            delete_option('leyka_currency_rur_fixed_amounts');

            $tmp_value = Leyka_Options_Controller::get_option_value('currency_rur_fixed_amounts');
            if($tmp_value) {
                Leyka_Options_Controller::set_option_value('currency_rub_fixed_amounts', $tmp_value);
            }
            delete_option('leyka_currency_rur_fixed_amounts');

        }

        // Rename "rur" postmeta value to "RUB", if needed:
        $update_needed = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = 'leyka_donation_currency' AND meta_value = 'rur'");
        if($update_needed) {
            $wpdb->update(
                $wpdb->prefix.'postmeta',
                ['meta_value' => 'RUB'],
                ['meta_key' => 'leyka_donation_currency', 'meta_value' => 'rur']
            );
        }

        // Rename "donor email date" postmeta value to a new value, if needed:
        $update_needed = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_leyka_donor_email_date' OR meta_key = 'donor_email_date'");
        if($update_needed) {

            $wpdb->update(
                $wpdb->prefix.'postmeta',
                ['meta_key' => 'leyka_donor_email_date'],
                ['meta_key' => '_leyka_donor_email_date']
            );
            $wpdb->update(
                $wpdb->prefix.'postmeta',
                ['meta_key' => 'leyka_donor_email_date'],
                ['meta_key' => 'donor_email_date']
            );

        }

        // Rename CloudPayments donations postmeta keys, if needed:
        $update_needed = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_cp_recurring_id'");
        if($update_needed) {
            $wpdb->update($wpdb->prefix.'postmeta',
                ['meta_key' => 'cp_recurring_id'],
                ['meta_key' => '_cp_recurring_id']
            );
        }

        $update_needed = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_cp_transaction_id'");
        if($update_needed) {
            $wpdb->update($wpdb->prefix.'postmeta',
                ['meta_key' => 'cp_transaction_id'],
                ['meta_key' => '_cp_transaction_id']
            );
        }

        // Rename RBK Money donations postmeta keys, if needed:
        $update_needed = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_leyka_rbk_invoice_id'");
        if($update_needed) {
            $wpdb->update($wpdb->prefix.'postmeta',
                ['meta_key' => 'rbk_invoice_id'],
                ['meta_key' => '_leyka_rbk_invoice_id']
            );
        }

        $update_needed = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_leyka_rbk_payment_id'");
        if($update_needed) {
            $wpdb->update($wpdb->prefix.'postmeta',
                ['meta_key' => 'rbk_payment_id'],
                ['meta_key' => '_leyka_rbk_payment_id']
            );
        }

        // Rename YooKassa donations postmeta keys, if needed:
        $update_needed = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_yandex_invoice_id'");
        if($update_needed) {
            $wpdb->update($wpdb->prefix.'postmeta',
                ['meta_key' => 'yandex_invoice_id'],
                ['meta_key' => '_yandex_invoice_id']
            );
        }

        // Chronopay RUR -> RUB options IDs transition:
        $tmp_value = Leyka_Options_Controller::get_option_value('chronopay_card_product_id_rur');
        if($tmp_value) {

            Leyka_Options_Controller::set_option_value('chronopay_card_product_id_rub', $tmp_value);
            delete_option('chronopay_card_product_id_rur');

        }

        $tmp_value = Leyka_Options_Controller::get_option_value('chronopay_card_rebill_product_id_rub');
        if($tmp_value) {

            Leyka_Options_Controller::set_option_value('chronopay_card_rebill_product_id_rub', $tmp_value);
            delete_option('chronopay_card_rebill_product_id_rur');

        }
        // Chronopay RUR -> RUB options IDs transition - END

    }

    if($leyka_last_ver && version_compare($leyka_last_ver, '3.24', '<=')) {

        // Delete the service user meta for the old banner:
        delete_user_meta(get_current_user_id(), 'leyka_dashboard_banner_closed-webinar-jan2022');

        // Turn off the "platform_signature_on_form_enabled" option for all updating (i.e. non-new) installations:
        Leyka_Options_Controller::set_option_value('platform_signature_on_form_enabled', 0);

    }

    do_action('leyka_plugin_update', $leyka_last_ver); // Warning: Extensions can't use this hook, as they are initialized later

    // Set a flag to flush permalinks (needs to be done a bit later than this activation itself):
    update_option('leyka_permalinks_flushed', 0);

    if( !$leyka_last_ver ) {
        update_option('leyka_init_wizard_redirect', true);
    }

    update_option('leyka_last_ver', LEYKA_VERSION);

}