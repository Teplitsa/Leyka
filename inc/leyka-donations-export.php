<?php if( !defined('WPINC') ) die;
function leyka_render_export_button() {

    if(get_current_screen()->id == 'edit-'.Leyka_Donation_Management::$post_type) {?>

    <span class="donations-export-form">
        <form action="<?php echo admin_url('/');?>" method="get">
            <input type="hidden" name="post_status" value="<?php echo empty($_GET['post_status']) ? 0 : $_GET['post_status'];?>">
            <input type="hidden" name="month-year" value="<?php echo empty($_GET['m']) ? 0 : $_GET['m'];?>">
            <input type="hidden" name="payment_type" value="<?php echo empty($_GET['payment_type']) ? '' : $_GET['payment_type'];?>">
            <input type="hidden" name="gateway_pm" value="<?php echo empty($_GET['gateway_pm']) ? '' : $_GET['gateway_pm'];?>">
            <input type="hidden" name="campaign" value="<?php echo empty($_GET['campaign']) ? '' : $_GET['campaign'];?>">
            <input type="hidden" name="search_string" value="<?php echo empty($_GET['s']) ? '' : $_GET['s'];?>">

            <?php foreach(apply_filters('leyka_donations_export_form_fields', array()) as $name => $value) {?>
                <input type="hidden" name="<?php echo $name;?>" value="<?php echo $value;?>">
            <?php }?>

            <input type="submit" name="leyka-donations-export-csv-excel" class="button-primary" value="<?php _e('Export (csv)', 'leyka');?>">
        </form>
    </span>

    <?php }

}
add_action('admin_notices', 'leyka_render_export_button');

function leyka_do_donations_export() {

    if(empty($_GET['leyka-donations-export-csv-excel'])) {
        return;
    }

    // Just in case that export will require some time:
    ini_set('max_execution_time', 99999);
    set_time_limit(99999);

    ob_start();

    $meta_query = array('relation' => 'AND');

    if( !empty($_GET['campaign']) ) {

        $_GET['campaign'] = absint($_GET['campaign']);

        $meta_query[] = array('key' => 'leyka_campaign_id', 'value' => $_GET['campaign']);

    }

    if( !empty($_GET['payment_type']) ) {
        $meta_query[] = array('key' => 'leyka_payment_type', 'value' => $_GET['payment_type']);
    }

    if( !empty($_GET['gateway_pm']) ) {

        if(strpos($_GET['gateway_pm'], 'gateway__') !== false) {
            $meta_query[] = array('key' => 'leyka_gateway', 'value' => str_replace('gateway__', '', $_GET['gateway_pm']));
        } else if(strpos($_GET['gateway_pm'], 'pm__') !== false) {

            $_GET['gateway_pm'] = explode('-', str_replace('pm__', '', $_GET['gateway_pm']));

            $meta_query[] = array('key' => 'leyka_gateway', 'value' => $_GET['gateway_pm'][0]);
            $meta_query[] = array('key' => 'leyka_payment_method', 'value' => $_GET['gateway_pm'][1]);

        }

    }

    $args = array(
        'post_type' => Leyka_Donation_Management::$post_type,
        'post_status' =>
            isset($_GET['post_status']) && in_array($_GET['post_status'], array_keys(leyka()->get_donation_statuses())) ?
                $_GET['post_status'] : 'any',
        'm' => $_GET['month-year'],
        's' => $_GET['search_string'],
        'meta_query' => $meta_query,
        'nopaging' => true,
    );

    $donations = apply_filters(
        'leyka_donations_pre_export',
        get_posts(apply_filters('leyka_donations_export_query_args', $args))
    );

    add_filter('leyka_donations_export_line', 'leyka_prepare_data_line_for_export', 10, 2);

    ob_clean();

    header('Content-type: application/vnd.ms-excel');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Pragma: no-cache');
    header('Content-Disposition: attachment; filename="donations-'.date('d.m.Y-H.i.s').'.csv"');

    $table_headers = array(
        'ID', 'Имя донора', 'Email', 'Тип платежа', 'Плат. оператор', 'Способ платежа', 'Полная сумма', 'Итоговая сумма', 'Валюта', 'Дата пожертвования', 'Статус', 'Кампания', 'Подписка на рассылку', 'Email подписки', 'Комментарий', 'Доп. поля кампании',
    );

    // "All Campaigns" additional donation form fields headers:
    $all_campaigns_additional_fields_settings = array();
    foreach(leyka_options()->opt('additional_donation_form_fields_library') as $field_id => $field_settings) {
        if($field_settings['for_all_campaigns']) {

            $all_campaigns_additional_fields_settings[$field_id] = $field_settings;
            $table_headers[] = '[Общее доп. поле]['.$field_id.'] '.$field_settings['title'];

        }
    }

    echo @iconv( // @ to avoid notices about illegal chars that happen in the line sometimes
        'UTF-8',
        apply_filters('leyka_donations_export_content_charset', 'CP1251//TRANSLIT//IGNORE'),
        "sep=;\n".implode(';', apply_filters('leyka_donations_export_headers', $table_headers))
    );

    foreach($donations as $donation) {

        $donation = new Leyka_Donation($donation);
        $campaign = new Leyka_Campaign($donation->campaign_id);

        $currency = $donation->currency_label;
        $currency_label_encoded = @iconv( // Sometimes currency sighs can't be encoded, so check for it
            'UTF-8',
            apply_filters('leyka_recurring_subscriptions_export_content_charset', 'CP1251//TRANSLIT//IGNORE'),
            $currency
        );
        $currency = $currency_label_encoded ? $currency : $donation->currency_id;

        $donor_subscription = 'Нет';
        if($donation->donor_subscribed === true) {
            $donor_subscription = 'Полная';
        } else if($donation->donor_subscribed > 0) {
            $donor_subscription = 'О кампании «'.$campaign->title.'»';
        }

        $line_values = array(
            $donation->id,
            $donation->donor_name,
            $donation->donor_email,
            $donation->payment_type_label,
            $donation->gateway_label,
            $donation->payment_method_label,
            str_replace('.', ',', $donation->sum),
            str_replace('.', ',', $donation->amount_total),
            $currency,
            $donation->date,
            $donation->status_label,
            $campaign->title,
            $donor_subscription,
            $donation->donor_subscription_email,
            $donation->donor_comment,
        );

        if($donation->additional_fields && is_array($donation->additional_fields)) {

            // Campaign-specific additional fields column:
            $campaign_additional_fields_column_value = '';
            $campaign_additional_fields_settings = Leyka_Campaign::get_additional_fields_settings($donation->campaign_id);

            foreach($donation->additional_fields as $field_id => $field_value) {

                if( // Add field to the campaign-specific column only if it isn't in common columns:
                    isset($campaign_additional_fields_settings[$field_id])
                    && !isset($all_campaigns_additional_fields_settings[$field_id])
                ) {

                    $campaign_additional_fields_column_value .=
                        '['.$field_id.'] '
                        .(
                            empty($campaign_additional_fields_settings[$field_id]['title']) ?
                                '' : $campaign_additional_fields_settings[$field_id]['title']
                        ).': '.$field_value."\n";

                }

            }

            $line_values[] = rtrim($campaign_additional_fields_column_value, "\n");

            // Common additional fields columns:
            foreach($all_campaigns_additional_fields_settings as $field_id => $field_settings) {
                $line_values[] = isset($donation->additional_fields[$field_id]) ? $donation->additional_fields[$field_id] : '';
            }

        }

        echo @iconv( // @ to avoid notices about illegal chars that happen in the line sometimes
            'UTF-8',
            apply_filters('leyka_donations_export_content_charset', 'CP1251//TRANSLIT//IGNORE'),
            "\r\n".implode(';', apply_filters('leyka_donations_export_line', $line_values, $donation))
        );

    }

    die();

}
add_action('admin_init', 'leyka_do_donations_export');