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
            <th class="donation-donor"><?php _e('Donor', 'leyka');?></th>
            <th class="donation-date"><?php _e('Date', 'leyka');?></th>
            <th class="donation-amount-status"><?php _e('Sum', 'leyka');?></th>
            <th class="donation-gateway-pm"><?php _e('Method', 'leyka');?></th>
            <th class="donation-donor-email-status"><?php _e('Message', 'leyka');?></th>
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
            <td class="donation-donor">

                <?php $edit_url = empty($donation['donor']['id']) ?
                    admin_url('admin.php?page=leyka_donation_info&donation='.$donation['id']) :
                    admin_url('admin.php?page=leyka_donor_info&donor='.$donation['donor']['id']) ?>

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
            <td class="donation-gateway-pm has-tooltip leyka-tooltip-align-left" title='<?php echo $donation['gateway']['label'].' / '.$donation['payment_method']['category_label']; ?>'>
                <span class="donation-gateway"><img src="<?php echo $donation['gateway']['icon'] ?>" alt="<?php echo $donation['gateway']['label'] ?>"></span>
                <span class="donation-pm"><img src="<?php echo $donation['payment_method']['category_icon']; ?>" alt="<?php echo $donation['payment_method']['category_label'] ?>"></span>
            </td>
            <td class="donation-donor-email-status">

                <?php if($donation['donor']['email_date']) {?>

                <div class="donor has-thanks">
                    <span class="donation-email-status"><?php echo __('Sent', 'leyka');?></span>
                    <span class="donation-email-date"><?php echo date(get_option('date_format'), $donation['donor']['email_date']);?></span>
                </div>

                <?php } else {?>

                <div class="donor no-thanks" data-donation-id="<?php echo $donation['id'];?>" data-nonce="<?php echo wp_create_nonce('leyka_donor_email');?>">
                    <span class="donation-email-status"><?php echo __("Not sent", 'leyka'); ?></span>
                    <span class="donation-email-action send-donor-thanks"><?php echo __('Send it now', 'leyka'); ?></span>
                </div>

                <?php } ?>

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