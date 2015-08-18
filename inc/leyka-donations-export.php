<?php if( !defined('WPINC') ) die;

function leyka_render_export_button() {

    global $pagenow;

    if(
        $pagenow == 'edit.php' &&
        isset($_GET['post_type']) &&
        $_GET['post_type'] == Leyka_Donation_Management::$post_type /*&&
        in_array('administrator', wp_get_current_user()->roles)*/
    ) {?>

    <span class="donations-export-form">
        <form action="#" method="get">
            <input type="hidden" name="post_status" value="<?php echo empty($_GET['post_status']) ? 0 : $_GET['post_status'];?>" />
            <input type="hidden" name="month-year" value="<?php echo empty($_GET['m']) ? 0 : $_GET['m'];?>" />
            <input type="hidden" name="payment_type" value="<?php echo empty($_GET['payment_type']) ? '' : $_GET['payment_type']; ?>" />
            <input type="hidden" name="gateway_pm" value="<?php echo empty($_GET['gateway_pm']) ? '' : $_GET['gateway_pm']; ?>" />
            <input type="hidden" name="campaign" value="<?php echo empty($_GET['campaign']) ? '' : $_GET['campaign']; ?>" />
            <?php foreach(apply_filters('leyka_donations_export_form_fields', array()) as $name => $value) {?>
            <input type="hidden" name="<?php echo $name;?>" value="<?echo $value;?>" />
            <?php }?>

            <input type="submit" name="leyka-donations-export-csv-excel" class="button-primary" value="<?php _e('Export (csv)', 'leyka');?>" />
            <div id="tech-export-wrapper"><input type="checkbox" name="export-tech" id="export-tech" value="1"><label for="export-tech"><?php _e('Technical export', 'leyka');?></label></div>
        </form>
    </span>
<?php }
}
add_action('admin_notices', 'leyka_render_export_button');

use SimpleExcel\SimpleExcel;

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
        $meta_query[] = array('key' => 'leyka_campaign_id', 'value' => (int)$_GET['campaign']);
    }

    if( !empty($_GET['payment_type']) ) {
        $meta_query[] = array('key' => 'leyka_payment_type', 'value' => $_GET['payment_type']);
    }

    if( !empty($_GET['gateway_pm']) ) {

        if(strpos($_GET['gateway_pm'], 'gateway__') !== false) {
            $meta_query[] = array(
                'key' => 'leyka_gateway', 'value' => str_replace('gateway__', '', $_GET['gateway_pm'])
            );
        } elseif(strpos($_GET['gateway_pm'], 'pm__') !== false) {
            $meta_query[] = array(
                'key' => 'leyka_payment_method', 'value' => str_replace('pm__', '', $_GET['gateway_pm'])
            );
        }
    }

    $args = array(
        'post_type' => Leyka_Donation_Management::$post_type,
        'post_status' =>
            isset($_GET['post_status']) && in_array($_GET['post_status'], array_keys(leyka()->get_donation_statuses())) ?
                $_GET['post_status'] : 'any',
        'm' => $_GET['month-year'], // Filter by month
        'meta_query' => $meta_query,
        'posts_per_page' => 200,
    );

    $donations = new WP_Query(apply_filters('leyka_donations_export_query_args', $args));
    $total_pages = $donations->found_posts/200;
    $total_pages = $total_pages - (int)$total_pages > 0 ? (int)$total_pages+1 : $total_pages;
    $posts_page = $total_pages > 0 ? 1 : 0;

    $donations = $donations->get_posts();

    require_once LEYKA_PLUGIN_DIR.'inc/excel-writer/SimpleExcel.php';

    $excel = new SimpleExcel('csv');
    $domain = str_replace(array('http:', 'https:'), '', home_url());
    function prep($text) {
        return '"'.str_replace(array(';', '"'), array('', ''), $text).'"';
    }

    if(isset($_GET['export-tech'])) { // Technical export mode column headings

        $excel->writer->addRow(array('hash', 'Domain', 'Org_name', 'Timestamp', 'Date', 'Email_hash', 'Donor_name hash', 'Sum', 'Currency', 'Gateway_pm', 'Donation_status', 'Campaign_title', 'Campaign_URL', 'Payment_title', 'Target_sum', 'Campaign_target_state', 'Campaign_is_finished'));

    } else { // Normal export mode column headings

        $excel->writer->addRow(array(apply_filters('leyka_donations_export_headers', array('ID', 'Имя донора', 'Email', 'Тип платежа', 'Способ платежа', 'Сумма', 'Дата пожертвования', 'Статус', 'Кампания'))));
    }

    while($posts_page && $posts_page <= $total_pages) { // Main loop too fill the export file

        foreach($donations as $donation) {

            $donation = new Leyka_Donation($donation);
            $campaign = new Leyka_Campaign($donation->campaign_id);

            if(isset($_GET['export-tech'])) {

                $excel->writer->addRow(array(
                    prep(wp_hash($domain.$donation->date_timestamp.$donation->sum.$donation->id)),
                    prep($domain),
                    prep(leyka_options()->opt('org_full_name')),
                    prep($donation->date_timestamp),
                    prep(date(get_option('date_format').', H:i', $donation->date_timestamp)),
                    prep(wp_hash($donation->donor_email)),
                    prep(wp_hash($donation->donor_name)),
                    prep((int)$donation->sum),
                    prep($donation->currency),
                    $donation->payment_type == 'correction' ?
                        prep($donation->pm_id) : prep($donation->gateway_label.'-'.$donation->pm_id),
                    prep($donation->status),
                    prep($campaign->title),
                    prep($campaign->url),
                    prep($campaign->payment_title),
                    prep((int)$campaign->target),
                    prep($campaign->target_state),
                    prep((int)$campaign->is_finished)
                ));

            } else {

                $excel->writer->addRow(apply_filters('leyka_donations_export_line', array(
                    $donation->id, $donation->donor_name, $donation->donor_email, $donation->payment_type_label,
                    $donation->payment_method_label, $donation->sum.' '.$donation->currency_label, $donation->date,
                    $donation->status_label, $campaign->title,
                )));

            }

        }

        $posts_page++;

        $args['paged'] = $posts_page;
        $donations = get_posts(apply_filters('leyka_donations_export_query_args', $args));

        wp_cache_flush();
    }

    if(isset($_GET['export-tech'])) {

        $excel->writer->setDelimiter(';');

        ob_clean();

        header('Content-type: application/vnd.ms-excel');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Pragma: no-cache');

        header('Content-Disposition: attachment; filename="donations-tech-'.$domain.'-'.date('d.m.Y-H.i.s').'.csv"');

        die(iconv( // Do iconv so Excel could open it
            'UTF-8',
            apply_filters('leyka_donations_export_content_charset', 'windows-1251'),
            "sep=;\n".$excel->writer->saveString()
        ));

//        ob_clean();
//
//        header('Content-type: application/vnd.ms-excel');
//        header('Content-Transfer-Encoding: binary');
//        header('Expires: 0');
//        header('Pragma: no-cache');
//        header('Content-Disposition: attachment; filename="donations-tech-'.$domain.'-'.date('d.m.Y-H.i.s').'.csv"');
//
//        die("sep=;\n".implode("\r\n", $file_lines));

    } else {

        $excel->writer->setDelimiter(',');

        ob_clean();

        header('Content-type: application/vnd.ms-excel');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Pragma: no-cache');

        header('Content-Disposition: attachment; filename="donations-'.date('d.m.Y-H.i.s').'.csv"');

        die(iconv( // Do iconv so Excel could open it
            'UTF-8',
            apply_filters('leyka_donations_export_content_charset', 'windows-1251'),
            "sep=,\n".$excel->writer->saveString()
        ));

    }
}
add_action('admin_init', 'leyka_do_donations_export');