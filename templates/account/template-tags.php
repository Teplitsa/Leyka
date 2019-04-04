<?php if( !defined('WPINC') ) die;
/**
 * Donor's account template tags & helpers
 **/

if( !function_exists('leyka_get_donor_account_donations_list_item_html') ) {
    function leyka_get_donor_account_donations_list_item_html($is_hidden = false, $donation = false) {

        $is_hidden = !!$is_hidden;
        $placeholders = array(
            'donation_status' => '#STATUS#',
            'donation_status_description' => '#STATUS_DESCR#',
            'donation_type' => '#TYPE#',
            'donation_type_description' => '#TYPE_DESCR#',
            'item_classes' => '#ITEM_CLASS#',
            'tooltip classes' => '#TOOLTIP_CLASS#',
            'amount' => '#AMOUNT#',
            'currency_label' => '#CURR#',
            'gateway_label' => '#GATEWAY#',
            'pm_label' => '#PM#',
            'date' => '#DATE#',
            'campaign_title' => '#CAMPAIGN_TITLE#',
        );

        if($donation) {

            $donation = leyka_get_validated_donation($donation);

            $item_class = $tooltip_class = '';
            if($donation->status === 'failed') { $item_class = 'error'; $tooltip_class = 'error'; }
            else if($donation->status === 'refunded') { $item_class = 'refund'; $tooltip_class = 'notice'; }
            else if($donation->type === 'single') { $item_class = 'no-pay'; $tooltip_class = 'funded'; }
            else if($donation->type === 'rebill') { $item_class = 'pay'; $tooltip_class = 'funded'; }

            $placeholders = array(
                'donation_status' => $donation->status,
                'donation_status_description' => $donation->status_description,
                'donation_type' => $donation->type,
                'donation_type_description' => $donation->type_description,
                'item_classes' => $item_class,
                'tooltip classes' => $tooltip_class,
                'amount' => $donation->amount,
                'currency_label' => $donation->currency_label,
                'gateway_label' => $donation->gateway_label,
                'pm_label' => $donation->pm_label,
                'date' => $donation->date,
                'campaign_title' => $donation->campaign_title,
            );

        }

        ob_start();?>

        <div class="item <?php echo $placeholders['donation_status'];?> <?php echo $placeholders['donation_type'];?> <?php echo $placeholders['item_classes'];?>" <?php echo $is_hidden ? 'style="display:none;"' : '';?>>
            <h4 class="item-title">
                <span class="field-q"><span class="field-q-tooltip <?php echo 'status-'.$placeholders['donation_status'];?> <?php echo 'type-'.$placeholders['donation_type'];?> <?php echo $placeholders['tooltip_classes'];?>">
                    <?php echo $placeholders['donation_type_description'];?>
                    <br><br>
                    <?php echo $placeholders['donation_status_description'];?>
                </span></span>
                <?php echo $placeholders['amount'].' '.$placeholders['currency_label'];?>
            </h4>
            <span class="date"><?php echo $placeholders['date'];?></span>
            <p><?php echo '«'.$placeholders['campaign_title'].'»';?></p>

            <div class="donation-gateway-pm">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-info.svg" alt="">
                <span class="gateway"><?php echo $placeholders['gateway_label'];?></span> /
                <span class="pm"><?php echo $placeholders['pm_label'];?></span>
            </div>

        </div>

    <?php $out = ob_get_contents();
        ob_end_clean();

        return apply_filters('leyka_donor_account_donations_history_item_html', $out, $donation);

    }
}