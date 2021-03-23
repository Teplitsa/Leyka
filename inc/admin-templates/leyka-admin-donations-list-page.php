<?php if( !defined('WPINC') ) die;
/** Admin Donations list page template */

/** @var $this Leyka_Admin_Setup */?>

<div class="leyka-admin wrap donations-list leyka-settings-page" data-leyka-admin-page-type="donations-list-page">

    <h1 class="wp-heading-inline"><?php _e('Donations', 'leyka');?></h1>
    <a href="<?php echo admin_url('admin.php?page=leyka_donation_info');?>" class="page-title-action"><?php _e('Add correctional donation', 'leyka');?></a>

    <div id="poststuff">
        <div>

            <form class="donors-list-controls" action="#" method="get">

                <div class="donors-list-filters admin-list-filters">

                    <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']);?>">

                    <div class="col-1">

                        <div class="filters-row">

                            <?php $filter_value = isset($_GET['donor-type']) ? esc_attr($_GET['donor-type']) : false;?>
                            <select name="donor-type" class="leyka-select-menu">
                                <option value="" <?php echo !$filter_value ? 'selected="selected"' : '';?>>
                                    <?php _e('All donor types', 'leyka');?>
                                </option>
                                <option value="single" <?php echo $filter_value == 'single' ? 'selected="selected"' : '';?>>
                                    <?php _ex('Single', 'Donor type name', 'leyka');?>
                                </option>
                                <option value="regular" <?php echo $filter_value == 'regular' ? 'selected="selected"' : '';?>>
                                    <?php _ex('Regular', 'Donor type name', 'leyka');?>
                                </option>
                            </select>

                            <input type="text" name="donor-name-email" class="leyka-donor-name-email-selector leyka-selector" value="<?php echo isset($_GET['donor-name-email']) ? esc_attr($_GET['donor-name-email']) : '';?>" placeholder="<?php _e("Donor's name or email", 'leyka');?>">

                            <input type="text" name="first-donation-date" autocomplete="off" class="leyka-first-donation-date-selector leyka-selector" value="<?php echo isset($_GET['first-donation-date']) ? esc_attr($_GET['first-donation-date']) : '';?>" placeholder="<?php _e('First payment dates', 'leyka');?>">

                            <input type="text" name="last-donation-date" autocomplete="off" class="leyka-last-donation-date-selector leyka-selector" value="<?php echo isset($_GET['last-donation-date']) ? esc_attr($_GET['last-donation-date']) : '';?>" placeholder="<?php _e('Last payment dates', 'leyka');?>">

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

                            <input type="text" name="leyka-payment-status" class="leyka-payment-status-selector leyka-selector autocomplete-input" value="" placeholder="<?php _e('Payment status', 'leyka');?>">

                            <?php $filter_value = isset($_GET['payment-status']) ? (array)$_GET['payment-status'] : array();?>
                            <select id="leyka-payment-status-select" class="autocomplete-select" name="payment-status[]" multiple="multiple">

                                <?php $payment_status_list = leyka()->get_donation_statuses();

                                foreach($payment_status_list as $status => $status_label) {?>
                                    <option value="<?php echo $status;?>" <?php echo is_array($filter_value) && in_array($status, $filter_value) ? 'selected="selected"' : '';?>>
                                        <?php echo $status_label;?>
                                    </option>
                                <?php }?>

                            </select>

                            <input type="text" name="donors-tags-input" class="leyka-donors-tags-selector leyka-selector autocomplete-input" value="" placeholder="<?php _e('Donors tags', 'leyka');?>">

                            <?php $filter_value = isset($_GET['donors-tags']) ? (array)$_GET['donors-tags'] : array();?>
                            <select class="leyka-donors-tags-select autocomplete-select" name="donors-tags[]" multiple="multiple">
                                <?php $donors_tags = $filter_value ? get_terms(
                                    Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME,
                                    array(
                                        'include' => $filter_value,
                                        'hide_empty' => false,
                                        'orderby' => 'name',
                                        'order' => 'ASC',
                                    )
                                ) : array();

                                foreach($donors_tags as $tag) {?>
                                    <option value="<?php echo $tag->term_id;?>" <?php echo is_array($filter_value) && in_array($tag->term_id, $filter_value) ? 'selected="selected"' : '';?>>
                                        <?php echo $tag->name;?>
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