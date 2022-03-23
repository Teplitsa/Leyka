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
            <th class="donation-id"><?php _e('ID', 'leyka');?></th>
            <th class="donation-type"><?php _e('Type', 'leyka');?></th>
            <th class="donation-campaign"><?php _e('Campaign', 'leyka');?></th>
            <th class="donation-donor"><?php _e('Donor', 'leyka');?></th>
            <th class="donation-date"><?php _e('Date', 'leyka');?></th>
            <th class="donation-amount-status"><?php _e('Sum', 'leyka');?></th>
            <th class="donation-gateway-pm"><?php _e('Method', 'leyka');?></th>
        </tr>
    </thead>
    <tbody>
    <?php if($data) {
        foreach($data as $donation) { ?>
        <tr>
            <td class="donation-id">
                <div><?php echo $donation['id'] ?></div>
                <div><a href="<?php echo admin_url('admin.php?page=leyka_donation_info&donation='.$donation['id']) ?>">К платежу</a></div>
            </td>
            <td class="donation-type">
                <img class="has-tooltip" src="<?php echo LEYKA_PLUGIN_BASE_URL . 'img/dashboard/icon-donation-type-'.$donation['donation_type']['id'].'.svg';?>" alt="" title="<?php echo $donation['donation_type']['label'] ?>">
            </td>
            <td class="donation-campaign">
                <div class="campaign">
                    <a href="<?php echo get_edit_post_link($donation['campaign_id']); ?>">
                        <?php if(strlen($donation['campaign_title']) > 30) {
                            echo mb_substr($donation['campaign_title'], 0, 30).' ...';
                        } else {
                            echo $donation['campaign_title'];
                        } ?>
                    </a>
                </div>
            </td>
            <td class="donation-donor">

                <?php $edit_url = empty($donation['donor']['id']) ?
                    admin_url('admin.php?page=leyka_donor_info&donor='.$donation['donor']['id']) :
                    admin_url('admin.php?page=leyka_donation_info&donation='.$donation['id'])?>

                <a href="<?php echo $edit_url;?>" target="_blank"><?php echo $donation['donor']['name'];?></a>
                <div class="donor-contact"><?php echo $donation['donor']['email'];?></div>
                <div class="donor-contact"><?php echo $donation['donor']['phone'];?></div>
            </td>
            <td class="donation-date">
                <div class="date"><?php echo date('Y.m.d', strtotime($donation['date_time']) ) ;?></div>
                <div class="time"><?php echo date('H:i', strtotime($donation['date_time']) ) ;?></div>
            </td>
            <td class="donation-amount-status">
                <div class="wrapper-donation-amount-status">
                    <div class="wrapper-donation-status">
                        <i class="donation-status <?php echo $donation['status']['id'];?> has-tooltip leyka-tooltip-align-left" title=""></i>
                        <span class="donation-status-description leyka-tooltip-content"><b><?php echo $donation['status']['label'];?></b>: <?php echo $donation['status']['description'];?></span>
                    </div>
                    <div class="wrapper-donation-amount">
                        <div class="donation-amount"><?php echo $donation['amount'].' '.$donation['currency'];?></div>
                        <div class="donation-total-amount"><?php echo $donation['total_amount'].' '.$donation['currency'];?></div>
                    </div>
                </div>
            </td>
            <td class="donation-gateway-pm">
                <span class="donation-gateway"><img src="<?php echo $donation['gateway']['icon'] ?>" alt="<?php echo $donation['gateway']['label'] ?>"></span>
                <span class="donation-pm"><img src="<?php echo $donation['payment_method']['category_icon']; ?>" alt="<?php echo $donation['payment_method']['category_label'] ?>"></span>
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