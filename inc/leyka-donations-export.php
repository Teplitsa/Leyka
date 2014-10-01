<?php
add_action('restrict_manage_posts', function(){
    global $pagenow;

    if(
        $pagenow == 'edit.php' &&
        isset($_GET['post_type']) &&
        $_GET['post_type'] == 'leyka_donation' /*&&
        in_array('administrator', wp_get_current_user()->roles)*/
    ) {?>

        <span class="donations-export-form">
            <form action="" method="get">
                <input type="hidden" name="month-year" value="<?php echo empty($_GET['m']) ? 0 : $_GET['m'];?>" />

                <input type="submit" name="leyka-donations-export-csv-excel" class="button-primary" value="<?php _e('Export donations history (csv)', 'leyka');?>" />
            </form>
        </span>
    <?php }
}, 100);

use SimpleExcel\SimpleExcel;

add_action('admin_init', function(){

    if(empty($_GET['leyka-donations-export-csv-excel']) )
        return;

    ob_start();

    $donation_statuses = array('submitted', 'funded', 'refunded', 'failed');
    $args = array(
        'post_type' => 'leyka_donation',
        'post_status' =>
            isset($_GET['post_status']) && in_array($_GET['post_status'], $donation_statuses) ?
                $_GET['post_status'] : $donation_statuses,
        'm' => $_GET['month-year'], // Filter by month
        'nopaging' => true,
    );

    $donations = get_posts(apply_filters('leyka_donations_export_query_args', $args));

    $file_lines = array(apply_filters('leyka_donations_export_headers', array('ID', 'Имя донора', 'Email', 'Тип платежа', 'Способ платежа', 'Сумма', 'Дата пожертвования', 'Статус', 'Кампания')));
    foreach($donations as $donation) {

        $donation = new Leyka_Donation($donation);
        $campaign = new Leyka_Campaign($donation->campaign_id);

        $donation_fields = apply_filters('leyka_donations_export_line', array(
            $donation->id,
            $donation->donor_name,
            $donation->donor_email,
            $donation->payment_type_label,
            $donation->payment_method_label,
            $donation->sum.' '.$donation->currency_label,
            $donation->date,
            $donation->status_label,
            $campaign->title,
        ));

        $file_lines[] = $donation_fields;
    }

    require_once LEYKA_PLUGIN_DIR.'inc/excel-writer/SimpleExcel.php';

    $excel = new SimpleExcel('csv');
    $excel->writer->setData($file_lines);
    $excel->writer->setDelimiter(",");

    ob_clean();

    header('Content-type: application/vnd.ms-excel');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Pragma: no-cache');

    header('Content-Disposition: attachment; filename="donations-'.date('d.m.Y-H.i.s').'.csv"');

    // Do iconv so Excel could open it:
    die(iconv(
        'UTF-8',
        apply_filters('leyka_donations_export_content_charset', 'windows-1251'),
        "sep=,\n".$excel->writer->saveString()
    ));
});