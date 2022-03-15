<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlet: Recurring donations stats
 * Description: A portlet to display simple statistics for the recurring donations.
 *
 * Title: Recurrings
 * Thumbnail: /img/dashboard/icon-money-recurring.svg
 *
 * @var $params
 **/

/** @var $params */
$data = Leyka_Recurring_Stats_Portlet_Controller::get_instance()->get_template_data($params);?>

<div class="portlet-row">
    <div class="row-label"><?php _e('Recurring donation average amount', 'leyka');?></div>
    <div class="row-data">

        <?php if( !isset($data['donations_amount_avg']) ) {?>
            <div class="no-data"><?php _e('No data available', 'leyka');?></div>
        <?php } else {?>

            <div class="main-number"><?php echo number_format(floor($data['donations_amount_avg']), 0, ".", " ").'&nbsp;'.leyka()->opt('currency_'.leyka()->opt('currency_main').'_label');?></div>
            <div class="percent <?php echo $data['donations_amount_avg_delta_percent'] < 0 ? 'negative' : ($data['donations_amount_avg_delta_percent'] > 0 ? 'positive' : '');?>"><?php echo str_replace(['+', '-'], '', $data['donations_amount_avg_delta_percent']);?></div>

        <?php }?>

    </div>
</div>

<div class="portlet-row">
    <div class="row-label"><?php _e('Recurring donations amount', 'leyka');?></div>
    <div class="row-data">

        <?php if( !isset($data['recurring_donations_amount']) ) {?>
            <div class="no-data"><?php _e('No data available', 'leyka');?></div>
        <?php } else {?>

            <div class="main-number"><?php echo number_format($data['recurring_donations_amount'], 0, ".", " ").'&nbsp;'.leyka_get_currency_label();?></div>
            <div class="percent <?php echo $data['recurring_donations_amount_delta_percent'] < 0 ? 'negative' : ($data['recurring_donations_amount_delta_percent'] > 0 ? 'positive' : '');?>"><?php echo str_replace(['+', '-'], '', $data['recurring_donations_amount_delta_percent']);?></div>

        <?php }?>

    </div>
</div>

<div class="portlet-row">
    <div class="row-label"><?php _e('Subscriptions count', 'leyka');?></div>
    <div class="row-data">

        <?php if( !isset($data['recurring_donations_amount']) ) {?>
            <div class="no-data"><?php _e('No data available', 'leyka');?></div>
        <?php } else {?>

            <div class="main-number"><?php echo $data['subscriptions_count']; ?></div>
            <div class="percent <?php echo $data['subscriptions_count_delta_percent'] < 0 ? 'negative' : ($data['subscriptions_count_delta_percent'] > 0 ? 'positive' : '');?>"><?php echo str_replace(['+', '-'], '', $data['subscriptions_count_delta_percent']);?></div>

        <?php }?>

    </div>
</div>

