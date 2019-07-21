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
        <dt><?php _e('Amount donated', 'leyka');?></dt>
        <dd><?php echo $donor->amount_donated.' '.leyka_get_currency_label('rur');?></dd>

        <dt><?php echo str_replace(":", "", __('Donations number', 'leyka'));?></dt>
        <dd><?php echo $donor->get_donations_count();?></dd>
    </dl>
</div>

<table id="donations-data-table" class="leyka-data-table donor-info-table">
    <thead>
        <tr>
            <!-- <td><?php _e('ID', 'leyka');?></td> -->
            <td><?php _e('Type', 'leyka');?></td>
            <td><?php _e('Date', 'leyka');?></td>
            <td><?php _e('Campaign', 'leyka');?></td>
            <td><?php _e('Amount', 'leyka');?></td>
<!--            <td>--><?php //_e('Actions', 'leyka');?><!--</td>-->
        </tr>
    </thead>
    <tfoot>
        <tr>
            <!-- <td><?php _e('ID', 'leyka');?></td> -->
            <td><?php _e('Type', 'leyka');?></td>
            <td><?php _e('Date', 'leyka');?></td>
            <td><?php _e('Campaign', 'leyka');?></td>
            <td><?php _e('Amount', 'leyka');?></td>
<!--            <td>--><?php //_e('Actions', 'leyka');?><!--</td>-->
        </tr>
    </tfoot>

    <tbody>
    <?php foreach($donor->get_donations() as $donation) { /** @var $donation Leyka_Donation */

        $gateway_label = $donation->gateway_id ? $donation->gateway_label : __('Custom payment info', 'leyka');
        $pm_label = $donation->gateway_id ? $donation->pm_label : $donation->pm;?>

        <tr <?php echo $donation->type == 'correction' ? 'class="leyka-donation-row-correction"' : '';?>>
            <!-- <td><?php echo $donation->id;?></td> -->
            <td class="column-donor_type"><div class="<?php echo $donation->type;?>"></div></td>
            <td><?php echo $donation->date;?></td>
            <td class="data-campaign">
                <div class="leyka-donation-info-wrapper">
                    <span class="donation-status <?php echo $donation->status;?> field-q">
                    	<span class="field-q-tooltip"><?php _e('Donation ' . $donation->status, 'leyka');?></span>
                    </span>
                    <div class="first-sub-row"><?php echo $donation->campaign_title;?></div>
                    <div class="second-sub-row"><?php echo $gateway_label.', '.$pm_label;?></div>
                </div>
            </td>
            <td><?php echo '<span class="amount">'.$donation->amount.'&nbsp;'.$donation->currency_label.'</span>';?></td>

            <!--<td><a href="<?php echo admin_url("/post.php?post={$donation->id}&action=edit");?>"><?php echo __('Edit', 'leyka');?></a></td>-->
        </tr>

    <?php }?>
    </tbody>
</table>