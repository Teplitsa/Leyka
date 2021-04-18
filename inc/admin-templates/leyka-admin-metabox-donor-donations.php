<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

try {
    $donor = new Leyka_Donor(absint($_GET['donor']));
} catch(Exception $e) {
    wp_die($e->getMessage());
}?>

<div class="donations-info">
    <dl>
        <dt><?php _e('Amount donated:', 'leyka');?></dt>
        <dd><?php echo leyka_format_amount($donor->amount_donated).' '.leyka_get_currency_label();?></dd>

        <dt><?php echo __('Donations number:', 'leyka');?></dt>
        <dd><?php echo number_format_i18n($donor->get_donations_count());?></dd>
    </dl>
</div>

<table id="donations-data-table" class="leyka-data-table donor-info-table">
    <thead>
        <tr>
             <td><?php _e('ID', 'leyka');?></td>
            <td><?php _e('Type', 'leyka');?></td>
            <td><?php _e('Date', 'leyka');?></td>
            <td><?php _e('Campaign', 'leyka');?></td>
            <td><?php _e('Amount', 'leyka');?></td>
<!--            <td>--><?php //_e('Actions', 'leyka');?><!--</td>-->
        </tr>
    </thead>
    <tfoot>
        <tr>
             <td><?php _e('ID', 'leyka');?></td>
            <td><?php _e('Type', 'leyka');?></td>
            <td><?php _e('Date', 'leyka');?></td>
            <td><?php _e('Campaign', 'leyka');?></td>
            <td><?php _e('Amount', 'leyka');?></td>
<!--            <td>--><?php //_e('Actions', 'leyka');?><!--</td>-->
        </tr>
    </tfoot>

    <tbody>
    <?php foreach($donor->get_donations(false, -1) as $donation) { /** @var $donation Leyka_Donation */

        $gateway_label = $donation->gateway_id ? $donation->gateway_label : __('Custom payment info', 'leyka');
        $pm_label = $donation->gateway_id ? $donation->pm_label : $donation->pm;?>

        <tr <?php echo $donation->type == 'correction' ? 'class="leyka-donation-row-correction"' : '';?>>

            <td>
                <a href="<?php echo admin_url("/post.php?post={$donation->id}&action=edit");?>" target="_blank">
                    <?php echo $donation->id;?>
                </a>
            </td>

            <td class="column-donation_type">
                <i class="icon-payment-type icon-<?php echo $donation->is_init_recurring_donation ? 'rebill-init' : $donation->payment_type;?> has-tooltip" title="<?php echo $donation->payment_type_label;?>"></i>
            </td>

            <td><?php echo $donation->date_time_label;?></td>

            <td class="data-campaign">
                <div class="leyka-donation-info-wrapper">

                    <i class="icon-leyka-donation-status icon-<?php echo $donation->status;?> has-tooltip leyka-tooltip-align-left" title="<?php echo $donation->status_description;?>"></i>

                    <div class="leyka-donation-additional-data">
                        <div class="first-sub-row"><?php echo $donation->campaign_title;?></div>
                        <div class="second-sub-row"><?php echo $gateway_label.', '.$pm_label;?></div>
                    </div>

                </div>
            </td>

            <td class="data-amount">
                <?php echo $donation->amount_formatted.'&nbsp;'.$donation->currency_label
                    .'<span class="amount-total"> / '.$donation->amount_total_formatted.'&nbsp;'.$donation->currency_label.'</span>';?>
                <?php // echo '<span class="amount">'.$donation->amount_formatted.'&nbsp;'.$donation->currency_label.'</span>';?>
            </td>

<!--            <td><a href="--><?php //echo admin_url("/post.php?post={$donation->id}&action=edit");?><!--">--><?php //echo __('Edit', 'leyka');?><!--</a></td>-->
        </tr>

    <?php }?>
    </tbody>

</table>