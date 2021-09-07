<?php if( !defined('WPINC') ) die;
/** Admin Recurring subscriptions list page template */

/** @var $this Leyka_Admin_Setup */?>

<div class="leyka-admin wrap recurring-subscriptions-list leyka-settings-page" data-leyka-admin-page-type="recurring-subscriptions-list-page">
    <h1 class="wp-heading-inline"><?php _e('Recurring subscriptions', 'leyka');?></h1>

    <div id="poststuff">
        <div>

            <form class="recurring-subscriptions-list-controls admin-list-controls" action="#" method="get">

                <div class="recurring-subscriptions-list-filters admin-list-filters">

                    <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']);?>">

                    <div class="col-1">

                        <div class="filters-row">

                            <?php $filter_value = isset($_GET['status']) ? esc_attr($_GET['status']) : false;?>
                            <div class="leyka-admin-list-filter-wrapper">
                                <select name="status" class="leyka-selector leyka-select-menu">
                                    <option value="" <?php echo !$filter_value ? 'selected="selected"' : '';?>>
                                        --- <?php _e('All subscriptions', 'leyka');?> ---
                                    </option>
                                    <option value="active" <?php echo $filter_value == 'active' ? 'selected="selected"' : '';?>>
                                        <?php _e('Only active', 'leyka');?>
                                    </option>
                                    <option value="non-active" <?php echo $filter_value == 'non-active' ? 'selected="selected"' : '';?>>
                                        <?php _e('Only not active', 'leyka');?>
                                    </option>
                                </select>
                            </div>

                            <div class="leyka-admin-list-filter-wrapper">
                                <input type="text" name="donor-name-email" class="leyka-donor-name-email-selector leyka-selector" data-search-donors-in="donations" value="<?php echo isset($_GET['donor-name-email']) ? esc_attr($_GET['donor-name-email']) : '';?>" placeholder="<?php _e("Donor's name or email", 'leyka');?>">
                            </div>

                            <div class="leyka-admin-list-filter-wrapper">

                                <input type="text" name="campaigns-input" class="leyka-campaigns-selector leyka-selector leyka-autocomplete" value="" placeholder="<?php _e('All campaigns', 'leyka');?>">
                                <?php $filter_value = isset($_GET['campaigns']) ? (array)$_GET['campaigns'] : [];?>

                                <select class="leyka-campaigns-select autocomplete-select" name="campaigns[]" multiple="multiple">
                                <?php $campaigns = $filter_value ? leyka_get_campaigns_list(['include' => $filter_value]) : [];
                                foreach($campaigns as $campaign_id => $campaign_title) {?>
                                    <option value="<?php echo $campaign_id;?>" <?php echo is_array($filter_value) && in_array($campaign_id, $filter_value) ? 'selected="selected"' : '';?>>
                                        <?php echo $campaign_title;?>
                                    </option>
                                <?php }?>
                                </select>

                            </div>

                        </div>

                        <div class="filters-row">

                            <div class="leyka-admin-list-filter-wrapper leyka-donation-date-filter-wrapper">
                                <input type="text" name="first-date" autocomplete="off" class="leyka-first-donation-date-selector leyka-selector datepicker-ranged-selector" value="<?php echo isset($_GET['first-date']) ? esc_attr($_GET['first-date']) : '';?>" placeholder="<?php _e('First payment dates', 'leyka');?>">
                            </div>

                            <div class="leyka-admin-list-filter-wrapper">

                                <?php $filter_value = isset($_GET['gateway']) ? (array)$_GET['gateway'] : [];?>

                                <select id="leyka-gateways-select" class="leyka-select-menu" name="gateway">

                                    <option value="" <?php echo $filter_value ? '' : 'selected="selected"';?>>
                                        --- <?php _e('All gateways', 'leyka');?> ---
                                    </option>

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

                        </div>

                        <div class="filters-row"><div class="filter-warning" id="leyka-filter-warning"></div></div>

                    </div>

                    <div class="col-2">
                        <input type="submit" class="button" value="<?php _e('Filter the data', 'leyka');?>">
                        <a href="<?php echo admin_url('/admin.php?page=leyka_recurring_subscriptions');?>" class="reset-filters"><?php _e('Reset the filter', 'leyka');?></a>
                    </div>

                </div>

                <div class="recurring-subscriptions-list-export admin-list-export">
                    <input type="submit" class="submit" name="subscriptions-list-export" value="<?php _e('Export the list in CSV', 'leyka');?>">
                </div>

            </form>

            <div id="post-body-content" class="<?php if($this->_recurring_subscriptions_list_table->get_items_count() === 0) {?>empty-donors-list<?php }?>">
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