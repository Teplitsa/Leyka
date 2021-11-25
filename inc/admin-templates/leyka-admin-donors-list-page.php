<?php if( !defined('WPINC') ) die;
/** Admin Donors list page template */

/** @var $this Leyka_Admin_Setup */?>

<div class="leyka-admin wrap donors-list leyka-settings-page" data-leyka-admin-page-type="donors-list-page">

    <h1 class="wp-heading-inline">

        <?php _e('Donors', 'leyka');

        $taxonomy = $taxonomy = get_taxonomy(Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME);?>

        <a href="<?php echo admin_url('edit-tags.php?taxonomy='.$taxonomy->name);?>" class="button"><?php echo $taxonomy->labels->menu_name;?></a>
    </h1>

    <div id="poststuff">
        <div>

            <form class="donors-list-controls" action="#" method="get">

                <div class="donors-list-filters admin-list-filters">

                    <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']);?>">

                    <div class="col-1">

                        <div class="filters-row">

                            <?php $filter_value = isset($_GET['donor-type']) ? esc_attr($_GET['donor-type']) : false;?>
                            <div class="leyka-admin-list-filter-wrapper">
                                <select name="donor-type" class="leyka-select-menu">

                                    <option value="" <?php echo $filter_value ? '' : 'selected="selected"';?>>
                                        --- <?php _e('All donor types', 'leyka');?> ---
                                    </option>

                                    <option value="single" <?php echo $filter_value == 'single' ? 'selected="selected"' : '';?>>
                                        <?php _ex('Single', 'Donor type name', 'leyka');?>
                                    </option>

                                    <option value="regular" <?php echo $filter_value == 'regular' ? 'selected="selected"' : '';?>>
                                        <?php _ex('Regular', 'Donor type name', 'leyka');?>
                                    </option>

                                </select>
                            </div>

                            <div class="leyka-admin-list-filter-wrapper">
                                <input type="text" name="donor-name-email" class="leyka-donor-name-email-selector leyka-selector" value="<?php echo isset($_GET['donor-name-email']) ? esc_attr($_GET['donor-name-email']) : '';?>" placeholder="<?php _e("Donor's name or email", 'leyka');?>">
                            </div>

                            <div class="leyka-admin-list-filter-wrapper leyka-donation-date-filter-wrapper">
                                <input type="text" name="first-date" autocomplete="off" class="leyka-first-donation-date-selector leyka-selector datepicker-ranged-selector" value="<?php echo isset($_GET['first-date']) ? esc_attr($_GET['first-date']) : '';?>" placeholder="<?php _e('First payment dates', 'leyka');?>">
                            </div>

                            <div class="leyka-admin-list-filter-wrapper leyka-donation-date-filter-wrapper">
                                <input type="text" name="last-date" autocomplete="off" class="leyka-last-donation-date-selector leyka-selector datepicker-ranged-selector" value="<?php echo isset($_GET['last-date']) ? esc_attr($_GET['last-date']) : '';?>" placeholder="<?php _e('Last payment dates', 'leyka');?>">
                            </div>

                        </div>

                        <div class="filters-row">

                            <?php $filter_value = isset($_GET['campaigns']) ? (array)$_GET['campaigns'] : [];?>
                            <div class="leyka-admin-list-filter-wrapper">

                                <input type="text" name="campaigns-input" class="leyka-campaigns-selector leyka-selector autocomplete-input" value="" placeholder="<?php _e('All campaigns', 'leyka');?>">

                                <select class="leyka-campaigns-select autocomplete-select" name="campaigns[]" multiple="multiple">
                                    <?php $campaigns = $filter_value ? leyka_get_campaigns_list(['include' => $filter_value]) : [];
                                    foreach($campaigns as $campaign_id => $campaign_title) {?>

                                        <option value="<?php echo $campaign_id;?>" <?php echo is_array($filter_value) && in_array($campaign_id, $filter_value) ? 'selected="selected"' : '';?>>
                                            <?php echo $campaign_title;?>
                                        </option>

                                    <?php }?>

                                </select>

                            </div>

                            <div class="leyka-admin-list-filter-wrapper">

                                <input type="text" name="donors-tags-input" class="leyka-donors-tags-selector leyka-selector autocomplete-input" value="" placeholder="<?php _e('Donors tags', 'leyka');?>">

                                <?php $filter_value = isset($_GET['donors-tags']) ? (array)$_GET['donors-tags'] : [];?>
                                <select class="leyka-donors-tags-select autocomplete-select" name="donors-tags[]" multiple="multiple">
                                    <?php $donors_tags = $filter_value ? get_terms(
                                        Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME,
                                        [
                                            'include' => $filter_value,
                                            'hide_empty' => false,
                                            'orderby' => 'name',
                                            'order' => 'ASC',
                                        ]
                                    ) : [];

                                    foreach($donors_tags as $tag) {?>
                                        <option value="<?php echo $tag->term_id;?>" <?php echo is_array($filter_value) && in_array($tag->term_id, $filter_value) ? 'selected="selected"' : '';?>>
                                            <?php echo $tag->name;?>
                                        </option>
                                    <?php }?>
                                </select>

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

            <div id="post-body-content" class="<?php if($this->_donors_list_table->get_items_count() === 0) {?>empty-donors-list<?php }?>">
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