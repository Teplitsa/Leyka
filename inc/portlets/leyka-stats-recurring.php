<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlet: Recurring donations stats
 * Description: A portlet to display simple statistics for the recurring donations.
 *
 * Title: Recurring
 * Thumbnail: /img/stats-recurring.svg
 **/

$data = Leyka_Recurring_Stats_Portlet_Controller::get_instance()->get_template_data($params);?>

<div class="portlet-row">
    <div class="row-label"><?php _e('Recurring donations amount', 'leyka');?></div>
    <div class="row-data">
        <?php if( !isset($data['recurring_donations_amount']) ) {?>
            <div class="no-data"><?php _e('No data available', 'leyka');?></div>
        <?php } else {?>

            <div class="main-number"><?php echo $data['recurring_donations_amount'].'&nbsp;'.leyka()->opt('currency_'.leyka()->opt('main_currency').'_label');?></div>
            <div class="percent <?php echo $data['recurring_donations_amount_delta_percent'] < 0 ? 'negative' : 'positive';?>"><?php echo $data['recurring_donations_amount_delta_percent'];?></div>

        <?php }?>
    </div>
</div>