<?php if( !defined('WPINC') ) die;
/** Admin Recurring subscriptions list page template */

/** @var $this Leyka_Admin_Setup */?>

<div class="leyka-admin wrap recurring-subscriptions-list" data-leyka-admin-page-type="recurring-subscriptions-list-page">
    <h1 class="wp-heading-inline"><?php _e('Recurring subscriptions', 'leyka');?></h1>

    <div id="poststuff">
        <div>

            <form class="recurring-subscriptions-list-controls" action="#" method="get">

                <div class="recurring-subscriptions-list-filters">

                    <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']);?>">

                    <div class="col-1">

                        <div class="filters-row">

                            <input type="text" name="leyka-recurring-subscription-status" class="leyka-recurring-subscription-status-selector leyka-selector autocomplete-input" value="" placeholder="<?php _e('Subscription status', 'leyka');?>">

                            <?php $filter_value = isset($_GET['subscription-status']) ? (array)$_GET['subscription-status'] : array();?>
                            <select id="leyka-recurring-subscription-status-select" class="autocomplete-select" name="subscription-status[]" multiple="multiple">

                                <?php $status_list = array('active' => __('Active', 'leyka'), 'non-active' => __('Non-active', 'leyka'),);
                                foreach($status_list as $status => $status_label) {?>
                                    <option value="<?php echo $status;?>" <?php echo is_array($filter_value) && in_array($status, $filter_value) ? 'selected="selected"' : '';?>>
                                        <?php echo $status_label;?>
                                    </option>
                                <?php }?>

                            </select>

                            <input type="text" name="donor-name-email" class="leyka-donor-name-email-selector leyka-selector" value="<?php echo isset($_GET['donor-name-email']) ? esc_attr($_GET['donor-name-email']) : '';?>" placeholder="<?php _e("Donor's name or email", 'leyka');?>">

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

                        </div>

                        <div class="filters-row">

                            <input type="text" name="first-donation-date" autocomplete="off" class="leyka-first-donation-date-selector leyka-selector" value="<?php echo isset($_GET['first-donation-date']) ? esc_attr($_GET['first-donation-date']) : '';?>" placeholder="<?php _e('First payment dates', 'leyka');?>">

                            <input type="text" name="last-donation-date" autocomplete="off" class="leyka-last-donation-date-selector leyka-selector" value="<?php echo isset($_GET['last-donation-date']) ? esc_attr($_GET['last-donation-date']) : '';?>" placeholder="<?php _e('Last payment dates', 'leyka');?>">

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

                        </div>

                        <div class="filters-row"><div class="filter-warning" id="leyka-filter-warning"></div></div>

                    </div>

                    <div class="col-2">
                        <input type="submit" class="button" value="<?php _e('Filter the data', 'leyka');?>">
                        <a href="<?php echo admin_url('/admin.php?page=leyka_donors');?>" class="reset-filters"><?php _e('Reset the filter', 'leyka');?></a>
                    </div>

                </div>

                <div class="donors-list-export">
                    <input type="submit" class="submit" name="subscription-list-export" value="<?php _e('Export the list in CSV', 'leyka');?>">
                </div>

            </form>

            <div id="post-body-content" class="<?php if($this->_recurring_subscriptions_list_table->record_count() === 0) {?>empty-donors-list<?php }?>">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">

                        <?php $this->_recurring_subscriptions_list_table->prepare_items();
                        $this->_recurring_subscriptions_list_table->display();

                        if($this->_recurring_subscriptions_list_table->has_items()) {
                            $this->_recurring_subscriptions_list_table->bulk_edit_fields();
                        }?>

                    </form>
                </div>
            </div>

        </div>

    </div>
</div>
<div class="clear"></div>