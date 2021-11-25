<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlet: Recent donations
 * Description: A portlet to display recent donations.
 *
 * Title: Recent donations
 * Thumbnail: /img/dashboard/icon-donors.svg
 *
 * @var $params
 **/

$data = Leyka_Recent_Donations_Portlet_Controller::get_instance()->get_template_data($params);?>

<table class="recent-donations">
    <thead>
        <tr>
            <th><?php _e('Type', 'leyka');?></th>
            <th><?php _e('Donor', 'leyka');?></th>
            <th><?php _e('Campaign/date', 'leyka');?></th>
            <th><?php  _e('Amount', 'leyka');?></th>
        </tr>
    </thead>
    <tbody>
    <?php if($data) {
        foreach($data as $donation) {?>
        <tr>
            <td class="donation-type">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL . 'img/dashboard/icon-donation-type-'.$donation['type'].'.svg';?>" alt="">
            </td>
            <td class="donation-donor">

                <?php $edit_url = empty($donation['donor_id']) ?
                    admin_url('admin.php?page=leyka_donor_info&donor='.$donation['donor_id']) :
                    admin_url('admin.php?page=leyka_donation_info&donation='.$donation['id'])?>

                <a href="<?php echo $edit_url;?>" target="_blank"><?php echo $donation['donor_name'];?></a>
                <div class="donor-contact"><?php echo $donation['donor_email'];?></div>

            </td>
            <td class="donation-campaign-date">
                <div class="campaign"><?php echo $donation['campaign_title'];?></div>
                <div class="date"><?php echo $donation['date_time'];?></div>
            </td>
            <td class="donation-amount-status">
            <span class="donation-status <?php echo $donation['status']['id'];?> field-q">
                <span class="field-q-tooltip"><?php echo $donation['status']['label'];?></span>
            </span>
                <span class="donation-amount"><?php echo $donation['amount'].' '.$donation['currency'];?></span>
            </td>
        </tr>
        <?php }
    } else {?>
        <tr>
            <td colspan="4" class="no-rows"><?php _e('No donations yet', 'leyka');?></td>
        </tr>
    <?php }?>
    </tbody>
</table>