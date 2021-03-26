<?php if( !defined('WPINC') ) die;
/** Admin Donations list page template */

/** @var $this Leyka_Admin_Setup */?>

<div class="leyka-admin wrap donations-list leyka-settings-page" data-leyka-admin-page-type="donations-list-page">

    <h1 class="wp-heading-inline"><?php _e('Donations', 'leyka');?></h1>
    <a href="<?php echo admin_url('admin.php?page=leyka_donation_info');?>" class="page-title-action"><?php _e('Add correctional donation', 'leyka');?></a>

    <div id="poststuff">
        <div>

            <form class="donations-list-controls" action="#" method="get">

                <div class="donations-list-filters admin-list-filters">

                    <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']);?>">

                    <div class="col-1">

                        <div class="filters-row">

                            <?php $filter_value = isset($_GET['payment-type']) ? esc_attr($_GET['payment-type']) : false;?>
                            <select id="payment-type-select" name="payment_type" class="leyka-select-menu">

                                <option value="" <?php echo $filter_value ? '' : 'selected="selected"';?>>
                                    <?php _e('Payment type', 'leyka');?>
                                </option>

                                <?php foreach(leyka_get_payment_types_list() as $payment_type => $label) {?>

                                <option value="<?php echo $payment_type;?>" <?php echo $filter_value && $filter_value == $payment_type ? 'selected="selected"' : '';?>>
                                    <?php echo $label;?>
                                </option>

                                <?php }?>

                            </select>

                            <?php $filter_value = isset($_GET['status']) ? esc_attr($_GET['status']) : false;?>
                            <select id="donation-status-select" name="donation-status" class="leyka-select-menu">

                                <option value="" <?php echo $filter_value ? '' : 'selected="selected"';?>>
                                    <?php _e('Donation status', 'leyka');?>
                                </option>

                                <?php foreach(leyka_get_donation_status_list() as $status => $label) {?>

                                    <option value="<?php echo $status;?>" <?php echo $filter_value && $filter_value == $status ? 'selected="selected"' : '';?>>
                                        <?php echo $label;?>
                                    </option>

                                <?php }?>

                            </select>

                            <label for="donation-datetime-from"><?php _e('From:', 'leyka');?></label>
                            <input type="date" id="donation-datetime-from" name="date-from" value="<?php echo empty($_GET['date-from']) ? '' : $_GET['date-from'];?>">

                            <label for="donation-datetime-to"><?php _e('To:', 'leyka');?></label>
                            <input type="date" id="donation-datetime-to" name="date-to" value="<?php echo empty($_GET['date-to']) ? '' : $_GET['date-to'];?>">

<!--                            <input type="text" name="donor-name-email" class="leyka-donor-name-email-selector leyka-selector" value="--><?php //echo isset($_GET['donor-name-email']) ? esc_attr($_GET['donor-name-email']) : '';?><!--" placeholder="--><?php //_e("Donor's name or email", 'leyka');?><!--">-->
<!---->
<!--                            <input type="text" name="first-donation-date" autocomplete="off" class="leyka-first-donation-date-selector leyka-selector" value="--><?php //echo isset($_GET['first-donation-date']) ? esc_attr($_GET['first-donation-date']) : '';?><!--" placeholder="--><?php //_e('First payment dates', 'leyka');?><!--">-->
<!---->
<!--                            <input type="text" name="last-donation-date" autocomplete="off" class="leyka-last-donation-date-selector leyka-selector" value="--><?php //echo isset($_GET['last-donation-date']) ? esc_attr($_GET['last-donation-date']) : '';?><!--" placeholder="--><?php //_e('Last payment dates', 'leyka');?><!--">-->

                        </div>

                        <div class="filters-row">

                            <input type="text" name="campaigns-input" class="leyka-campaigns-selector leyka-selector autocomplete-input" value="" placeholder="<?php _e('Campaigns list', 'leyka');?>">
                            <?php $filter_value = isset($_GET['campaigns']) ? (array)$_GET['campaigns'] : array();?>

                            <select id="leyka-campaigns-select" class="autocomplete-select" name="campaigns[]" multiple="multiple">
                                <?php $campaigns = $filter_value ? leyka_get_campaigns_list(array('include' => $filter_value)) : array();
                                foreach($campaigns as $campaign_id => $campaign_title) {?>

                                    <option value="<?php echo $campaign_id;?>" <?php echo is_array($filter_value) && in_array($campaign_id, $filter_value) ? 'selected="selected"' : '';?>>
                                        <?php echo $campaign_title;?>
                                    </option>

                                <?php }?>

                            </select>

                            <input type="text" name="leyka-gateways" class="leyka-gateways-selector leyka-selector autocomplete-input" value="" placeholder="<?php _e('Payment gateway', 'leyka');?>">

                            <?php $filter_value = isset($_GET['gateways']) ? (array)$_GET['gateways'] : array();?>
                            <select id="leyka-gateways-select" class="autocomplete-select" name="gateways[]" multiple="multiple">

                                <?php $gateways = leyka_get_gateways();
                                usort($gateways, function($gateway_first, $gateway_second){
                                    return strcmp($gateway_first->name, $gateway_second->name);
                                });

                                foreach($gateways as $gateway) {?>
                                    <option value="<?php echo $gateway->id;?>" <?php echo is_array($filter_value) && in_array($gateway->id, $filter_value) ? 'selected="selected"' : '';?> data-active-class="<?php echo $gateway->is_active ? "active-gateway" : "";?>">
                                        <?php echo $gateway->name;?>
                                    </option>
                                <?php }?>

                            </select>

                            <?php $filter_value = isset($_GET['gateway-pm']) ? (array)$_GET['gateway-pm'] : array();?>
                            <select id="gateway-pm-select" name="gateway-pm" class="autocomplete-select">

                                <option value="" <?php echo empty($_GET['gateway-pm']) ? '' : 'selected="selected"';?>>
                                    <?php _e('Gateway or payment method', 'leyka');?>
                                </option>

                                <?php $gw_pm_list = array();
                                foreach(leyka_get_gateways() as $gateway) {

                                    /** @var Leyka_Gateway $gateway */
                                    $pm_list = $gateway->get_payment_methods();
                                    if($pm_list)
                                        $gw_pm_list[] = array('gateway' => $gateway, 'pm_list' => $pm_list);
                                }
                                $gw_pm_list = apply_filters('leyka_donations_list_gw_pm_filter', $gw_pm_list);

                                foreach($gw_pm_list as $element) {?>

                                    <option value="<?php echo $element['gateway']->id;?>" <?php echo !empty($_GET['gateway_pm']) && $_GET['gateway_pm'] === $element['gateway']->id ? 'selected="selected"' : '';?>><?php echo $element['gateway']->name;?></option>

                                    <?php foreach($element['pm_list'] as $pm) {?>

                                        <option value="<?php echo $pm->full_id;?>" <?php echo !empty($_GET['gateway_pm']) && $_GET['gateway_pm'] === $pm->full_id ? 'selected="selected"' : '';?>><?php echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$pm->name;?></option>
                                    <?php }

                                }?>

                            </select>

                        </div>

<!--                        <div class="filters-row">-->
<!--                            <div class="option-block">-->
<!--                            --><?php //leyka_render_checkbox_field('donors-mailout-subscribed', array(
//                                'title' => __('Only donors subscribed to mailouts', 'leyka'),
//                                'comment' => __('Check to select only donors who chose to subscribe to campaign news mailouts when they donated.', 'leyka'),
//                                'short_format' => true,
//                                'value' => !empty($_GET['leyka_donors-mailout-subscribed']),
//                            ));?>
<!--                            </div>-->
<!--                        </div>-->

                        <div class="filters-row">
                            <div class="filter-warning" id="leyka-filter-warning"></div>
                        </div>

                    </div>

                    <div class="col-2">
                        <input type="submit" class="button" value="<?php _e('Filter the data', 'leyka');?>">
                        <a href="<?php echo admin_url('/admin.php?page=leyka_donors');?>" class="reset-filters">
                            <?php _e('Reset the filter', 'leyka');?>
                        </a>
                    </div>
                </div>

                <div class="donors-list-export admin-list-export">
                    <input type="submit" class="submit" name="donors-list-export" value="<?php _e('Export the list in CSV', 'leyka');?>">
                </div>

            </form>

            <div id="post-body-content" class="<?php if($this->_donors_list_table->record_count() === 0) {?>empty-donors-list<?php }?>">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">

                        <?php $this->_donors_list_table->prepare_items();
                        $this->_donors_list_table->display();

                        if($this->_donors_list_table->has_items()) {
                            $this->_donors_list_table->bulk_edit_fields();
                        }?>

                    </form>
                </div>
            </div>

        </div>

    </div>
</div>
<div class="clear"></div>





<div class="wrap" data-leyka-admin-page-type="donations-list-page">

    <h1 class="wp-heading-inline"><?php _e('Donations', 'leyka');?></h1>
    <a href="<?php echo admin_url('admin.php?page=leyka_donation_info');?>" class="page-title-action"><?php _e('Add correctional donation', 'leyka');?></a>

    <div id="poststuff">
        <div>

            <form class="donations-list-filters" action="#" method="get">

                <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']);?>">
                <input type="hidden" name="status" value="<?php echo empty($_GET['status']) ? '' : esc_attr($_GET['status']);?>">

                <div class="col-1">
                    <div class="filters-row"><div class="filter-warning" id="leyka-filter-warning"></div></div>
                </div>

                <div id="post-body-content" class="<?php if($this->_donations_list_table->record_count() === 0) {?>empty-donations-list<?php }?>">
                    <div>
                        <?php $this->_donations_list_table->views();
                        $this->_donations_list_table->prepare_items();
                        $this->_donations_list_table->display();?>
                    </div>
                </div>

            </form>

        </div>

    </div>
</div>