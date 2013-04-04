<?php
/**
 * @package Leyka
 * @subpackage Admin reports page modifications
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

/** Renders the base Reports page. */
function leyka_reports_page(){
    global $edd_options;

    $current_page = admin_url('edit.php?post_type=download&page=edd-reports');
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'reports';?>
<div class="wrap">
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo add_query_arg(array('tab' => 'reports', 'settings-updated' => false), $current_page);?>" class="nav-tab <?php echo $active_tab == 'reports' ? 'nav-tab-active' : '';?>"><?php _e('Reports', 'edd');?></a>
        <a href="<?php echo add_query_arg(array('tab' => 'export', 'settings-updated' => false), $current_page);?>" class="nav-tab <?php echo $active_tab == 'export' ? 'nav-tab-active' : '';?>"><?php _e('Export', 'edd');?></a>
        <?php /** @todo Uncomment this logs lab when start the task 415 */ ?>
        <a href="<?php echo add_query_arg(array('tab' => 'logs', 'settings-updated' => false), $current_page);?>" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : '';?>"><?php _e('Logs', 'edd');?></a>
        <?php do_action('edd_reports_tabs');?>
    </h2>

    <?php
    do_action('edd_reports_page_top');
    do_action('edd_reports_tab_'.$active_tab);
    do_action('edd_reports_page_bottom');
    ?>
</div><!-- .wrap -->
<?php
}

function leyka_report_views($labels){
    $labels['customers'] = __('Donors', 'leyka');
    return $labels;
}
add_filter('edd_report_views', 'leyka_report_views');

/** Changes in the Donates -> Reports -> Stats tab page. */
// Common stats fields:
function leyka_stats_views($views){
    $views['earnings'] = __('Incoming funds', 'leyka');
    return $views;
}
add_filter('edd_report_views', 'leyka_stats_views');

// Stats -> Reports -> Incoming funds report:
function leyka_reports_earnings(){?>
<div class="tablenav top">
    <div class="alignleft actions"><?php edd_report_views();?></div>
</div>
<?php
    leyka_reports_graph();
}
remove_action('edd_reports_view_earnings', 'edd_reports_earnings');
add_action('edd_reports_view_earnings', 'leyka_reports_earnings');

// Render the incoming funds view:
function leyka_reports_graph(){
    $dates = edd_get_report_dates(); // Retrieve the queried dates

    // Determine graph options
    switch( $dates['range'] ) :
        case 'today' :
            $time_format 	= '%d/%b';
            $tick_size		= 'hour';
            $day_by_day		= true;
            break;
        case 'last_year' :
            $time_format 	= '%b';
            $tick_size		= 'month';
            $day_by_day		= false;
            break;
        case 'this_year' :
            $time_format 	= '%b';
            $tick_size		= 'month';
            $day_by_day		= false;
            break;
        case 'last_quarter' :
            $time_format	= '%b';
            $tick_size		= 'month';
            $day_by_day 	= false;
            break;
        case 'this_quarter' :
            $time_format	= '%b';
            $tick_size		= 'month';
            $day_by_day 	= false;
            break;
        case 'other' :
            if( ( $dates['m_end'] - $dates['m_start'] ) >= 2 ) {
                $time_format	= '%b';
                $tick_size		= 'month';
                $day_by_day 	= false;
            } else {
                $time_format 	= '%d/%b';
                $tick_size		= 'day';
                $day_by_day 	= true;
            }
            break;
        default:
            $time_format 	= '%d/%b'; 	// Show days by default
            $tick_size		= 'day'; 	// Default graph interval
            $day_by_day 	= true;
            break;
    endswitch;

    $time_format 	= apply_filters( 'edd_graph_timeformat', $time_format );
    $tick_size 		= apply_filters( 'edd_graph_ticksize', $tick_size );
    $totals 		= (float) 0.00; // Total earnings for time period shown
    $sales_totals   = 0;            // Total sales for time period shown

    ob_start(); ?>
<script type="text/javascript">
    jQuery( document ).ready( function($) {
        $.plot(
                $("#edd_monthly_stats"),
                [{
                    data: [
                        <?php

                        if( $dates['range'] == 'today' ) {
                            // Hour by hour
                            $hour  = 1;
                            $month = date( 'n' );
                            while ( $hour <= 23 ) :
                                $sales = edd_get_sales_by_date( $dates['day'], $month, $dates['year'], $hour );
                                $sales_totals += $sales;
                                $date = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] ); ?>
                                [<?php echo $date * 1000; ?>, <?php echo $sales; ?>],
                                <?php
                                $hour++;
                            endwhile;

                        } elseif( $dates['range'] == 'this_week' || $dates['range'] == 'last_week'  ) {

                            //Day by day
                            $day     = $dates['day'];
                            $day_end = $dates['day_end'];
                            $month   = $dates['m_start'];
                            while ( $day <= $day_end ) :
                                $sales = edd_get_sales_by_date( $day, $month, $dates['year'] );
                                $sales_totals += $sales;
                                $date = mktime( 0, 0, 0, $month, $day, $dates['year'] ); ?>
                                [<?php echo $date * 1000; ?>, <?php echo $sales; ?>],
                                <?php
                                $day++;
                            endwhile;

                        } else {

                            $i = $dates['m_start'];
                            while ( $i <= $dates['m_end'] ) :
                                if ( $day_by_day ) :
                                    $num_of_days 	= cal_days_in_month( CAL_GREGORIAN, $i, $dates['year'] );
                                    $d 				= 1;
                                    while ( $d <= $num_of_days ) :
                                        $sales = edd_get_sales_by_date( $d, $i, $dates['year'] );
                                        $sales_totals += $sales;
                                        $date = mktime( 0, 0, 0, $i, $d, $dates['year'] ); ?>
                                        [<?php echo $date * 1000; ?>, <?php echo $sales; ?>],
                                        <?php
                                        $d++;
                                    endwhile;
                                else :
                                    $date = mktime( 0, 0, 0, $i, 1, $dates['year'] );
                                    ?>
                                    [<?php echo $date * 1000; ?>, <?php echo edd_get_sales_by_date( null, $i, $dates['year'] ); ?>],
                                    <?php
                                endif;
                                $i++;
                            endwhile;

                        }

                        ?>
                    ],
                    yaxis: 2,
                    label: "<?php _e('Donations number', 'leyka');?>",
                    id: 'sales'
                },
                    {
                        data: [
                            <?php

                            if( $dates['range'] == 'today' ) {

                                // Hour by hour
                                $hour  = 1;
                                $month = date( 'n' );
                                while ( $hour <= 23 ) :
                                    $earnings = edd_get_earnings_by_date( $dates['day'], $month, $dates['year'], $hour );
                                    $totals += $earnings;
                                    $date = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] ); ?>
                                    [<?php echo $date * 1000; ?>, <?php echo $earnings; ?>],
                                    <?php
                                    $hour++;
                                endwhile;

                            } elseif( $dates['range'] == 'this_week' || $dates['range'] == 'last_week' ) {

                                //Day by day
                                $day     = $dates['day'];
                                $day_end = $dates['day_end'];
                                $month   = $dates['m_start'];
                                while ( $day <= $day_end ) :
                                    $earnings = edd_get_earnings_by_date( $day, $month, $dates['year'] );
                                    $totals += $earnings;
                                    $date = mktime( 0, 0, 0, $month, $day, $dates['year'] ); ?>
                                    [<?php echo $date * 1000; ?>, <?php echo $earnings; ?>],
                                    <?php
                                    $day++;
                                endwhile;

                            } else {

                                $i = $dates['m_start'];
                                while ( $i <= $dates['m_end'] ) :
                                    if ( $day_by_day ) :
                                        $num_of_days 	= cal_days_in_month( CAL_GREGORIAN, $i, $dates['year'] );
                                        $d 				= 1;
                                        while ( $d <= $num_of_days ) :
                                            $date = mktime( 0, 0, 0, $i, $d, $dates['year'] );
                                            $earnings = edd_get_earnings_by_date( $d, $i, $dates['year'] );
                                            $totals += $earnings; ?>
                                            [<?php echo $date * 1000; ?>, <?php echo $earnings ?>],
                                            <?php $d++; endwhile;
                                    else :
                                        $date = mktime( 0, 0, 0, $i, 1, $dates['year'] );
                                        $earnings = edd_get_earnings_by_date( null, $i, $dates['year'] );
                                        $totals += $earnings;
                                        ?>
                                        [<?php echo $date * 1000; ?>, <?php echo $earnings; ?>],
                                        <?php
                                    endif;
                                    $i++;
                                endwhile;

                            }

                            ?>
                        ],
                        label: "<?php _e('Incoming funds', 'leyka');?>",
                        id: 'earnings'
                    }],
                {
                    series: {
                        lines: { show: true },
                        points: { show: true }
                    },
                    grid: {
                        show: true,
                        aboveData: false,
                        color: '#ccc',
                        backgroundColor: '#fff',
                        borderWidth: 2,
                        borderColor: '#ccc',
                        clickable: false,
                        hoverable: true
                    },
                    xaxis: {
                        mode: "time",
                        timeFormat: "<?php echo $time_format; ?>",
                        minTickSize: [1, "<?php echo $tick_size; ?>"]
                    },
                    yaxis: [
                        { min: 0, tickSize: 1, tickDecimals: 2 },
                        { min: 0, tickDecimals: 0 }
                    ]

                });

        function edd_flot_tooltip(x, y, contents) {
            $('<div id="edd-flot-tooltip">' + contents + '</div>').css( {
                position: 'absolute',
                display: 'none',
                top: y + 5,
                left: x + 5,
                border: '1px solid #fdd',
                padding: '2px',
                'background-color': '#fee',
                opacity: 0.80
            }).appendTo("body").fadeIn(200);
        }

        var previousPoint = null;
        $("#edd_monthly_stats").bind("plothover", function (event, pos, item) {
            $("#x").text(pos.x.toFixed(2));
            $("#y").text(pos.y.toFixed(2));
            if (item) {
                if (previousPoint != item.dataIndex) {
                    previousPoint = item.dataIndex;
                    $("#edd-flot-tooltip").remove();
                    var x = item.datapoint[0].toFixed(2),
                            y = item.datapoint[1].toFixed(2);
                    if( item.series.id == 'earnings' ) {
                        if( edd_vars.currency_pos == 'before' ) {
                            edd_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + edd_vars.currency_sign + y );
                        } else {
                            edd_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + y + edd_vars.currency_sign );
                        }
                    } else {
                        edd_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + y.replace( '.00', '' ) );
                    }
                }
            } else {
                $("#edd-flot-tooltip").remove();
                previousPoint = null;
            }
        });
    });
</script>

<div class="metabox-holder" style="padding-top: 0;">
    <div class="postbox">
        <h3><span><?php _e('Incoming funds over time', 'leyka'); ?></span></h3>

        <div class="inside">
            <?php edd_reports_graph_controls(); ?>
            <div id="edd_monthly_stats" style="height: 300px;"></div>
            <p id="edd_graph_totals"><strong><?php _e('Total incoming funds for period shown:', 'leyka'); echo ' '.edd_currency_filter(edd_format_amount($totals));?></strong></p>
            <p id="edd_graph_totals"><strong><?php _e('Total donations maked for period shown:', 'leyka'); echo ' '.$sales_totals;?></strong></p>
        </div>
    </div>
</div>
<?php
    echo ob_get_clean();
}

// Stats -> Reports -> Donates view:
function leyka_reports_donates_table(){
    require_once(LEYKA_PLUGIN_DIR.'includes/classes/donate-reports-table.php');

    $donate_reports_table = new Leyka_Donate_Reports_Table();
    $donate_reports_table->prepare_items();
    $donate_reports_table->display();
}
remove_action('edd_reports_view_downloads', 'edd_reports_downloads_table');
add_action('edd_reports_view_downloads', 'leyka_reports_donates_table');

// Stats -> Reports -> Donors view:
function leyka_reports_donors_table(){
    require_once(LEYKA_PLUGIN_DIR.'includes/classes/donor-reports-table.php');

    $donors_table = new Leyka_Donor_Reports_Table();
    $donors_table->prepare_items();
    $donors_table->display();
}
remove_action('edd_reports_view_customers', 'edd_reports_customers_table');
add_action('edd_reports_view_customers', 'leyka_reports_donors_table');

/** Changes in Stats -> Export. */
function leyka_reports_tab_export(){?>
<div class="metabox-holder">
    <div id="post-body">
        <div id="post-body-content">
            <div class="postbox">
                <h3><span><?php _e('Export PDF of donations maked and funds received', 'leyka'); ?></span></h3>
                <div class="inside">
                    <p><?php _e('Download a PDF file of donations maked and funds received for all donates for the current year.', 'leyka' ); ?> <?php _e('Date range reports will be coming soon.', 'edd');?></p>
                    <p><a class="button" href="<?php echo wp_nonce_url( add_query_arg(array('edd-action' => 'generate_pdf')), 'edd_generate_pdf'); ?>"><?php _e('Generate PDF', 'edd');?></a></p>
                </div><!-- .inside -->
            </div><!-- .postbox -->

            <div class="postbox">
                <h3><span><?php _e('Export donations history', 'leyka'); ?></span></h3>
                <div class="inside">
                    <p><?php _e('Download a CSV of all payments recorded.', 'edd');?></p>
                    <p><a class="button" href="<?php echo wp_nonce_url(add_query_arg(array('edd-action' => 'payment_export')), 'edd_payments_export');?>"><?php _e('Generate CSV', 'edd');?></a>
                    </p>
                </div><!-- .inside -->
            </div><!-- .postbox -->

            <div class="postbox">
                <h3><span><?php _e('Export donors in CSV', 'leyka');?></span></h3>
                <div class="inside">
                    <p><?php _e('Download a CSV file of all donors emails. This export includes donation numbers and amounts for each donor.', 'leyka');?></p>
                    <p><a class="button" href="<?php echo wp_nonce_url(add_query_arg(array('edd-action' => 'email_export')), 'edd_email_export');?>"><?php _e('Generate CSV', 'edd');?></a></p>
                </div><!-- .inside -->
            </div><!-- .postbox -->
        </div><!-- .post-body-content -->
    </div><!-- .post-body -->
</div><!-- .metabox-holder -->
<?php
}
remove_action('edd_reports_tab_export', 'edd_reports_tab_export');
add_action('edd_reports_tab_export', 'leyka_reports_tab_export');

/** Changes in the Stats -> Logs page. */
/** Common log views. */
function leyka_log_views($views){
    unset($views['file_downloads'], $views['api_requests']); // Remove unneeded views
    $views['sales'] = __('Donations', 'leyka');

    return $views;
}
add_filter('edd_log_views', 'leyka_log_views');

/** Logs tab. */
function leyka_reports_tab_logs() {
    require(EDD_PLUGIN_DIR.'includes/admin/reporting/logs.php');

    $current_view = 'sales';

    if(isset($_GET['view']) && array_key_exists($_GET['view'], edd_log_default_views()))
        $current_view = $_GET['view'];

    if($current_view == 'sales') // To replace sales view with a customized one
        remove_action('edd_logs_view_'.$current_view, 'edd_logs_view_'.$current_view);
    do_action('edd_logs_view_'.$current_view);
}
remove_action('edd_reports_tab_logs', 'edd_reports_tab_logs');
add_action('edd_reports_tab_logs', 'leyka_reports_tab_logs');

/** Donations log view. */
function leyka_logs_view_donations() {
    require LEYKA_PLUGIN_DIR.'/includes/classes/donations-logs-list-table.php';

    $logs_table = new Leyka_Donations_Log_Table();
    $logs_table->prepare_items();
    $logs_table->display();
}
add_action('edd_logs_view_sales', 'leyka_logs_view_donations');