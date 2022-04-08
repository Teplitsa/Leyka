<?php if( !defined('WPINC') ) die;
/** Admin Dashboard page template */

/** @var $this Leyka_Admin_Setup */?>

<div class="wrap leyka-admin leyka-info-sidebar-page leyka-dashboard-page">

    <h1><?php _e('Leyka dashboard', 'leyka');?></h1>

<?php if(leyka_options()->opt('send_plugin_stats') !== 'y' && leyka_options()->opt('plugin_stats_sync_enabled')) {?>

    <div class="send-plugin-stats-invite">

        <div class="invite-text">
            <?php _e('Please, turn on the option to send anonymous plugin usage data to help us diagnose', 'leyka');?>
        </div>

        <div class="invite-link">

            <button class="send-plugin-usage-stats-y"><?php _e('Allow usage statistics collection', 'leyka');?></button>
            <?php wp_nonce_field('usage_stats_y', 'usage_stats_y');?>

            <div class="loading-indicator-wrap">
                <div class="loader-wrap" style="display: none;"><span class="leyka-loader xxs"></span></div>
                <img class="ok-icon" src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/dashboard/icon-check.svg" alt="">
            </div>

        </div>

    </div>

<?php }?>

    <div class="leyka-page-content">

        <div class="main-col">

            <?php if($this->has_banners('admin-dashboard', 'main')) {
                $this->show_banner('admin-dashboard', 'main');        }

            $dashboard_stats_intervals = apply_filters('leyka_admin_dashboard_intervals', [
                'days_365' => __('365 days', 'leyka'),
                'days_180' => __('180 days', 'leyka'),
                'days_90' => __('90 days', 'leyka'),
                'days_30' => __('30 days', 'leyka'),
                'days_7' => __('7 days', 'leyka'),
                'this_year' => __('From the year start', 'leyka'),
                'this_half_year' => __('Half-year', 'leyka'),
                'this_quarter' => __( sprintf('%d quarter of %d',ceil(date("m", time()) / 3), date("Y") ) , 'leyka'),
                'this_month' => __(date("F", time()), 'leyka'),
                'this_week' => __('Current week', 'leyka')
            ]);
            $_GET['interval'] = empty($_GET['interval']) ?
                apply_filters('leyka_admin_dashboard_interval_default', 'days_365') : esc_attr($_GET['interval']);
            $current_url = admin_url('admin.php?page=leyka');
            $dashboard_data_cache_date = date('d.m.Y, H:i',
                get_transient('leyka_dashboard_data_cache_timestamp_'.$_GET['interval']) === false || isset($_GET['reset']) ?
                time() : get_transient('leyka_dashboard_data_cache_timestamp_'.$_GET['interval'])); ?>

            <div class="plugin-data-interval">

                <div class ="plugin-data-interval-content">

                    <div class="leyka-content-row">
                        <?php foreach($dashboard_stats_intervals as $interval_id => $title) {
                            if(strpos($interval_id, 'this') === false) { ?>
                                <a href="<?php echo add_query_arg('interval', $interval_id, $current_url);?>" class="<?php echo $_GET['interval'] === $interval_id ? 'current-interval' : '';?>">
                                    <?php echo esc_html($title);?>
                                </a>
                            <?php }
                        }?>
                    </div>

                    <div class="leyka-content-row">
                        <?php foreach($dashboard_stats_intervals as $interval_id => $title) {
                            if(strpos($interval_id, 'this') !== false) { ?>
                                <a href="<?php echo add_query_arg('interval', $interval_id, $current_url);?>" class="<?php echo $_GET['interval'] === $interval_id ? 'current-interval' : '';?>">
                                    <?php echo esc_html($title);?>
                                </a>
                            <?php }
                        }?>
                    </div>

                </div>

                <div class="plugin-data-interval-tools">
                    <span class="plugin-data-interval-reset-text"><?php echo __('The data is current as</br> of ', 'leyka').$dashboard_data_cache_date; ?></span>
                    <a class="plugin-data-interval-reset-btn" href="?page=leyka&interval=<?php echo $_GET['interval']; ?>&reset=true"><?php echo __('Renew data', 'leyka');  ?></a>
                </div>


            </div>

            <?php $row_id = 'donations-stats';?>
            <div class="leyka-content-row leyka-<?php echo $row_id;?>">
                <?php do_action('leyka_admin_dashboard_portlets_row', $row_id);?>
            </div>

            <?php $row_id = 'donations-dynamics';?>
            <div class="leyka-content-row leyka-<?php echo $row_id;?>">
                <?php do_action('leyka_admin_dashboard_portlets_row', $row_id);?>
            </div>

            <?php $row_id = 'recent-donations';?>
            <div class="leyka-content-row leyka-<?php echo $row_id;?>">
                <?php do_action('leyka_admin_dashboard_portlets_row', $row_id);?>
            </div>

        </div>
        <div class="sidebar-col">
            <?php $this->_show_admin_template('dashboard-sidebar');?>
        </div>
    </div>

</div>