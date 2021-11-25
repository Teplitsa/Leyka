<?php if( !defined('WPINC') ) die;

$pm_available = leyka_options()->opt('pm_available');
$pm_order = explode('pm_order[]=', leyka_options()->opt('pm_order'));
array_shift($pm_order);?>

<div class="side-area">

    <div class="pm-order-header">
        <h3><?php _e('The order for payment methods display on the website', 'leyka');?></h3>
        <div class="pm-order-description"><?php _e('Drag & drop the payment method blocks', 'leyka');?></div>
    </div>

    <div class="pm-update-status">
        <div class="result ok-message" style="display: none;"><?php _e('Changes saved', 'leyka');?></div>
        <div class="result error-message"></div>
        <div class="result leyka-loader xs" style="display: none;"></div>
    </div>

    <div class="pm-order pm-list-empty" <?php echo empty($pm_available) || (count($pm_available) === 1 && empty($pm_available[0])) ? '' : 'style="display:none;"';?>>

        <div class="pm-list-empty-base-content">
            <?php _e('How to add a payment method?', 'leyka');?>
        </div>

        <div class="pm-list-empty-comment" style="display: none;">
            <?php _e('Select a gateway in the list on the left, then proceed to gateway settings. There, check the checkboxes of needed payment methods.', 'leyka');?>
        </div>

    </div>

    <ul id="pm-order-settings" data-pm-order="<?php echo leyka_options()->opt('pm_order');?>" data-nonce="<?php echo wp_create_nonce('leyka-update-pm-order');?>">

    <?php leyka_pm_sortable_option_html_new(true); // To clone the PM item structure when adding new items

    foreach($pm_order as $i => &$pm_full_id) { // Active PM list

        $pm_full_id = str_replace(['&amp;', '&'], '', $pm_full_id);
        $pm = leyka_get_pm_by_id($pm_full_id, true);

        if( !$pm ) {

            unset($pm_order[$i]);
            continue;

        }

        $gateway = leyka_get_gateway_by_id($pm->gateway_id);

        if(in_array($pm_full_id, $pm_available) && $gateway->is_country_supported()) {
            leyka_pm_sortable_option_html_new(false, $pm_full_id, $pm->label);
        } else {
            unset($pm_order[$i]);
        }

    }

    if( !empty($_GET['gateway']) ) { // Gateway settings page - show it's inactive PMs as hidden

        $gateway = leyka_get_gateway_by_id($_GET['gateway']); /** @var $gateway Leyka_Gateway*/

        if($gateway) {
            foreach($gateway->get_payment_methods(false) as $pm_inactive) {
                if( !in_array($pm_inactive->full_id, $pm_available) ) {
                    leyka_pm_sortable_option_html_new(true, $pm_inactive->full_id, $pm_inactive->label);
                }
            }
        }

    }?>

    </ul>

</div>