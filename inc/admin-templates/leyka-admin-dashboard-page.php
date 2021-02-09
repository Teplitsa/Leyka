<?php if( !defined('WPINC') ) die;
/** Admin Dashboard page template */

/** @var $this Leyka_Admin_Setup */?>

<div class="wrap leyka-admin leyka-info-sidebar-page leyka-dashboard-page">

    <h1><?php _e('Leyka dashboard', 'leyka');?></h1>

<?php if( 1 /*leyka_options()->opt('send_plugin_stats') !== 'y' && leyka_options()->opt('plugin_stats_sync_enabled')*/ ) {?>

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
                $this->show_banner('admin-dashboard', 'main');
            }

            $_GET['interval'] = empty($_GET['interval']) ? 'year' : esc_attr($_GET['interval']);
            $current_url = admin_url('admin.php?page=leyka');?>

            <div class="plugin-data-interval">
                <a href="<?php echo add_query_arg('interval', 'year', $current_url);?>" class="<?php echo $_GET['interval'] === 'year' ? 'current-interval' : '';?>"><?php _e('Year', 'leyka');?></a>
                <a href="<?php echo add_query_arg('interval', 'half-year', $current_url);?>" class="<?php echo $_GET['interval'] === 'half-year' ? 'current-interval' : '';?>"><?php _e('Half-year', 'leyka');?></a>
                <a href="<?php echo add_query_arg('interval', 'quarter', $current_url);?>" class="<?php echo $_GET['interval'] === 'quarter' ? 'current-interval' : '';?>"><?php _e('Quarter', 'leyka');?></a>
                <a href="<?php echo add_query_arg('interval', 'month', $current_url);?>" class="<?php echo $_GET['interval'] === 'month' ? 'current-interval' : '';?>"><?php _e('Month', 'leyka');?></a>
                <a href="<?php echo add_query_arg('interval', 'week', $current_url);?>" class="<?php echo $_GET['interval'] === 'week' ? 'current-interval' : '';?>"><?php _e('Week', 'leyka');?></a>
            </div>

            <div class="leyka-content-row">
                <?php $this->show_admin_portlet('stats-donations-main', array('interval' => $_GET['interval']));
                $this->show_admin_portlet('stats-recurring', array('interval' => $_GET['interval']));?>
            </div>

            <div class="leyka-content-row">
                <?php $this->show_admin_portlet('donations-dynamics', array('interval' => $_GET['interval']));?>
            </div>

            <div class="leyka-content-row">
                <?php $this->show_admin_portlet('recent-donations', array(
                    'interval' => $_GET['interval'],
                    'number' => 5,
                ));?>
            </div>

        </div>
        <div class="sidebar-col">
            <?php $this->_show_admin_template('dashboard-sidebar');?>
        </div>
    </div>

</div>