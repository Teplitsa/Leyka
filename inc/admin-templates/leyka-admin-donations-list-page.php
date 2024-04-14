<?php if( !defined('WPINC') ) die;
/** Admin Donations list page template */

/** @var $this Leyka_Admin_Setup */

$current_page     = $_GET['page'];
$date_from        = isset( $_GET['date-from'] ) ? $_GET['date-from'] : '';
$date_to          = isset( $_GET['date-to'] ) ? $_GET['date-to'] : '';
$donor_name_email = isset( $_GET['donor-name-email'] ) ? $_GET['donor-name-email'] : '';
$donor_subscribed = isset( $_GET['donor_subscribed'] ) ? $_GET['donor_subscribed'] : '';
?>

<div class="leyka-admin wrap donations-list leyka-settings-page" data-leyka-admin-page-type="donations-list-page">

    <h1 class="wp-heading-inline"><?php esc_html_e('Donations', 'leyka');?></h1>
    <a href="<?php echo esc_url( admin_url('admin.php?page=leyka_donation_info') );?>" class="page-title-action"><?php esc_html_e('Add correctional donation', 'leyka');?></a>

    <div id="poststuff">
        <div>

            <form class="donations-list-controls" action="#" method="get">

                <div class="donations-list-filters admin-list-filters">

                    <input type="hidden" name="page" value="<?php echo esc_attr( $current_page );?>">

                    <div class="col-1">

                        <div class="filters-row">

                            <?php $filter_value = isset($_GET['type']) ? mb_strtolower(esc_attr($_GET['type'])) : false;?>
                            <div class="leyka-admin-list-filter-wrapper">
                                <select id="payment-type-select" name="type" class="leyka-select-menu">

                                    <option value="" <?php echo wp_kses_post( $filter_value ? '' : 'selected="selected"' );?>>
                                        --- <?php esc_html_e('Payment type', 'leyka');?> ---
                                    </option>

                                    <?php foreach(leyka_get_payment_types_list() as $payment_type => $label) {?>

                                    <option value="<?php echo esc_attr( $payment_type );?>" <?php selected( $filter_value, $payment_type );?>>
                                        <?php echo esc_html( $label );?>
                                    </option>

                                    <?php }?>

                                </select>
                            </div>

                            <?php $filter_value = isset($_GET['status']) ? esc_attr($_GET['status']) : false;?>
                            <div class="leyka-admin-list-filter-wrapper">
                                <select id="donation-status-select" name="status" class="leyka-select-menu">

                                    <option value="" <?php echo wp_kses_post( $filter_value ? '' : 'selected="selected"' );?>>
                                        --- <?php esc_html_e('Donation status', 'leyka');?> ---
                                    </option>

                                    <?php foreach(leyka_get_donation_status_list() as $status => $label) {?>

                                        <option value="<?php echo esc_attr( $status );?>" <?php echo wp_kses_post( $filter_value && $filter_value == $status ? 'selected="selected"' : '' );?>>
                                            <?php echo esc_html( $label );?>
                                        </option>

                                    <?php }?>

                                </select>
                            </div>

                            <div class="leyka-admin-list-filter-wrapper leyka-donation-date-filter-wrapper">
                                <label for="donation-datetime-from"><?php esc_html_e('From:', 'leyka');?></label>
                                <input type="text" id="donation-datetime-from" name="date-from" autocomplete="off" class="leyka-datepicker leyka-selector" data-min-date="-5Y" data-max-date="+0D" value="<?php echo esc_attr( $date_from ); ?>" placeholder="<?php esc_attr_e('dd.mm.yyyy', 'leyka');?>">
                            </div>

                            <div class="leyka-admin-list-filter-wrapper leyka-donation-date-filter-wrapper">
                                <label for="donation-datetime-to"><?php esc_html_e('To:', 'leyka');?></label>
                                <input type="text" id="donation-datetime-to" name="date-to" autocomplete="off" class="leyka-datepicker leyka-selector" data-min-date="-5Y" data-max-date="+0D" value="<?php echo esc_attr( $date_to ); ?>" placeholder="<?php esc_attr_e('dd.mm.yyyy', 'leyka');?>">
                            </div>

                        </div>

                        <div class="filters-row">

                            <?php $filter_value = isset($_GET['campaigns']) ? (array)$_GET['campaigns'] : [];?>
                            <div class="leyka-admin-list-filter-wrapper">

                                <input type="text" name="campaigns-input" class="leyka-campaigns-selector leyka-selector autocomplete-input" value="" placeholder="<?php esc_attr_e('All campaigns', 'leyka');?>">

                                <select class="leyka-campaigns-select autocomplete-select" name="campaigns[]" multiple="multiple">

                                    <?php $campaigns = $filter_value ?
                                        leyka_get_campaigns_list(['include' => $filter_value]) : [];

                                    foreach($campaigns as $campaign_id => $campaign_title) {?>

                                        <option value="<?php echo esc_attr( $campaign_id );?>" <?php echo is_array($filter_value) && in_array($campaign_id, $filter_value) ? 'selected="selected"' : '';?>>
                                            <?php echo esc_html( $campaign_title );?>
                                        </option>

                                    <?php }?>

                                </select>

                            </div>

                            <?php $filter_value = isset($_GET['gateway-pm']) ? $_GET['gateway-pm'] : '';?>

                            <div class="leyka-admin-list-filter-wrapper">
                                <select id="gateway-pm-select" name="gateway-pm" class="leyka-select-menu">

                                    <option value="" <?php echo wp_kses_post( $filter_value ? '' : 'selected="selected"' );?>>
                                        --- <?php esc_html_e('Payment method', 'leyka');?> ---
                                    </option>

                                    <?php $gw_pm_list = [];
                                    foreach(leyka_get_gateways() as $gateway) {

                                        /** @var Leyka_Gateway $gateway */
                                        $pm_list = $gateway->get_payment_methods();
                                        if($pm_list) {
                                            $gw_pm_list[] = ['gateway' => $gateway, 'pm_list' => $pm_list,];
                                        }

                                    }
                                    $gw_pm_list = apply_filters('leyka_donations_list_gw_pm_filter', $gw_pm_list);

                                    foreach($gw_pm_list as $element) {?>

                                        <option class="leyka-gateway-entry" value="<?php echo esc_attr( $element['gateway']->id );?>" <?php selected( $filter_value, $element['gateway']->id );?>>
                                            <?php echo esc_html( $element['gateway']->name );?>
                                        </option>

                                        <?php foreach($element['pm_list'] as $pm) {?>

                                            <option class="leyka-pm-entry" value="<?php echo esc_attr( $pm->full_id );?>" <?php selected( $filter_value, $pm->full_id );?>>
                                                <?php echo esc_html( '&nbsp;&nbsp;&nbsp;&nbsp;' . $pm->name );?>
                                            </option>

                                        <?php }

                                    }?>

                                </select>
                            </div>

                            <div class="leyka-admin-list-filter-wrapper leyka-donation-donor-filter-wrapper">
                                <input type="text" name="donor-name-email" class="leyka-donor-name-email-selector leyka-selector autocomplete-input" data-search-donors-in="donations" value="<?php echo esc_attr( $donor_name_email ); ?>" placeholder="<?php esc_attr_e("Donor's name or email", 'leyka');?>">
                            </div>

                            <div class="leyka-admin-list-filter-wrapper">
                                <select id="donor-subscribed-select" name="donor_subscribed" class="leyka-select-menu">
                                    <option value="-" <?php selected( $donor_subscribed, '-' );?>>--- <?php esc_html_e('Email subscription', 'leyka');?> ---</option>
                                    <option value="1" <?php selected( $donor_subscribed, 1 );?>><?php esc_html_e('Subscription on', 'leyka');?></option>
                                    <option value="0" <?php selected( $donor_subscribed, 0 );?>><?php esc_html_e('No subscription', 'leyka');?></option>
                                </select>
                            </div>

                        </div>

                        <div class="filters-row">
                            <div class="filter-warning" id="leyka-filter-warning"></div>
                        </div>

                    </div>

                    <div class="col-2">
                        <input type="submit" class="button" value="<?php esc_html_e('Filter the data', 'leyka');?>">
                        <a href="<?php echo esc_url( admin_url('/admin.php?page=leyka_donations') );?>" class="reset-filters">
                            <?php esc_html_e('Reset the filter', 'leyka');?>
                        </a>
                    </div>
                </div>

                <div class="donations-list-export admin-list-export">
                    <input type="submit" class="submit" name="donations-list-export" value="<?php esc_attr_e('Export the list in CSV', 'leyka');?>">
                </div>

            </form>

            <div id="post-body-content" class="<?php if($this->_donations_list_table->get_items_count() === 0) {?>empty-donations-list<?php }?>">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">

                        <?php $this->_donations_list_table->prepare_items();
                        $this->_donations_list_table->display();

                        if($this->_donations_list_table->has_items()) {
                            $this->_donations_list_table->bulk_edit_fields();
                        }?>

                    </form>
                </div>
            </div>

        </div>

    </div>
</div>
<div class="clear"></div>
