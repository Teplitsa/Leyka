<?php if( !defined('WPINC') ) die;
function leyka_render_export_button() {

    if(
        get_current_screen()->id === 'edit-'.Leyka_Donation_Management::$post_type /** @todo Remove the line when both storage-typed Donations are rendered via list page */
        || (isset($_GET['page']) && $_GET['page'] === 'leyka_donations')
    ) {?>

    <span class="donations-export-form">
        <form action="<?php echo admin_url('/');?>" method="get">

            <?php wp_nonce_field('donations-export');?>

            <input type="hidden" name="status" value="<?php echo empty($_GET['status']) ? (empty($_GET['post_status']) ? '' : esc_attr($_GET['post_status'])) : esc_attr($_GET['status']);?>">
            <input type="hidden" name="month-year" value="<?php echo empty($_GET['m']) ? 0 : esc_attr($_GET['m']);?>">
            <input type="hidden" name="date-from" value="<?php echo empty($_GET['date-from']) ? '' : esc_attr($_GET['date-from']);?>">
            <input type="hidden" name="date-to" value="<?php echo empty($_GET['date-to']) ? '' : esc_attr($_GET['date-to']);?>">
            <input type="hidden" name="payment_type" value="<?php echo empty($_GET['payment_type']) ? '' : esc_attr($_GET['payment_type']);?>">
            <input type="hidden" name="gateway_pm" value="<?php echo empty($_GET['gateway_pm']) ? '' : esc_attr($_GET['gateway_pm']);?>">
            <input type="hidden" name="campaign" value="<?php echo empty($_GET['campaign']) ? '' : esc_attr($_GET['campaign']);?>">

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

    if(empty($_GET['leyka-donations-export-csv-excel']) || !wp_verify_nonce($_GET['_wpnonce'], 'donations-export')) {
        return;
    }

    // Just in case that export will require some time:
    ini_set('max_execution_time', 99999);
    set_time_limit(99999);

    ob_start();

    $params = array('get_all' => true,);

    if( !empty($_GET['status']) ) {
        $params['status'] = $_GET['status'];
    }

    if( !empty($_GET['campaign']) ) {
        $params['campaign_id'] = absint($_GET['campaign']);
    }

    if( !empty($_GET['payment_type']) ) {
        $params['payment_type'] = $_GET['payment_type'];
    }

    if( !empty($_GET['gateway_pm']) ) {
        if(strpos($_GET['gateway_pm'], '-') !== false) {
            $params['gateway_pm'] = $_GET['gateway_pm'];
        } else { // Only gateway given
            $params['gateway_id'] = $_GET['gateway_pm'];
        }
    }

    if( !empty($_GET['month-year']) ) { // A "YYYYMM" line given
        $params['date'] = $_GET['year_month'];
    } else {

        if( !empty($_GET['date-from']) ) {
            $params['date_from'] = $_GET['date-from'];
        }
        if( !empty($_GET['date-to']) ) {
            $params['date_to'] = $_GET['date-to'];
        }

    }

    $donations = apply_filters(
        'leyka_donations_pre_export',
        Leyka_Donations::get_instance()->get(apply_filters('leyka_donations_export_query_args', $params))
    );

    add_filter('leyka_donations_export_line', 'leyka_prepare_data_line_for_export', 10, 2);

    ob_clean();

    header('Content-type: application/vnd.ms-excel');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Pragma: no-cache');
    header('Content-Disposition: attachment; filename="donations-'.date('d.m.Y-H.i.s').'.csv"');

    echo @iconv( // @ to avoid notices about illegal chars that happen in the line sometimes
        'UTF-8',
        apply_filters('leyka_donations_export_content_charset', 'windows-1251'),
        "sep=;\n".implode(';', apply_filters('leyka_donations_export_headers', array(
            'ID', 'Имя донора', 'Email', 'Тип платежа', 'Плат. оператор', 'Способ платежа', 'Полная сумма', 'Итоговая сумма', 'Валюта', 'Дата пожертвования', 'Статус', 'Кампания', 'Подписка на рассылку', 'Email подписки', 'Комментарий'
        )))
    );

    foreach($donations as $donation) { /** @var $donation Leyka_Donation_Base */

        $campaign = new Leyka_Campaign($donation->campaign_id);

        $donor_subscription = 'Нет';
        if($donation->donor_subscribed === true) {
            $donor_subscription = 'Полная';
        } else if($donation->donor_subscribed > 0) {
            $donor_subscription = 'О кампании «'.$campaign->title.'»';
        }

        echo @iconv( // @ to avoid notices about illegal chars that happen in the line sometimes
            'UTF-8',
            apply_filters('leyka_donations_export_content_charset', 'windows-1251'),
            "\r\n".implode(';', apply_filters(
                'leyka_donations_export_line',
                array(
                    $donation->id,
                    $donation->donor_name,
                    $donation->donor_email,
                    $donation->payment_type_label,
                    $donation->gateway_label,
                    $donation->payment_method_label,
                    $donation->amount,
                    $donation->amount_total,
                    $donation->currency_label,
                    $donation->date,
                    $donation->status_label,
                    $campaign->title,
                    $donor_subscription,
                    $donation->donor_subscription_email,
                    $donation->donor_comment,
                ),
                $donation
            ))
        );

    }

    die();

}
add_action('admin_init', 'leyka_do_donations_export');