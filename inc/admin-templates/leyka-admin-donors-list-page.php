<?php if( !defined('WPINC') ) die;
/** Admin Donors list page template */

/** @var $this Leyka_Admin_Setup */?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Donors', 'leyka');?></h1>

    <div id="poststuff">
        <div>

            <form class="donors-list-filters" action="#" method="get">

                <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']);?>">

                <div class="col-1">
                
                	<div class="filters-row">
                
                        <?php $filter_value = isset($_GET['donor-type']) ? esc_attr($_GET['donor-type']) : false;?>
                        <select name="donor-type">
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
    
                        <input type="text" name="first-donation-date" class="leyka-first-donation-date-selector leyka-selector" value="<?php echo isset($_GET['first-donation-date']) ? esc_attr($_GET['first-donation-date']) : '';?>" placeholder="<?php _e('First payment date', 'leyka');?>">
    
                        <input type="text" name="leyka-campaigns-selector leyka-selector" value="" placeholder="<?php _e('Campaigns list', 'leyka');?>">
                        
                        <select name="campaigns[]" multiple="multiple">
                            <option value="" selected="selected"><?php _e('Campaigns list', 'leyka');?></option>
                            <?php /** @todo Use ajax query to get values */?>
                        </select>
                    
                    </div>
                    
                    <div class="filters-row">

                        <input type="text" name="last-donation-date" class="leyka-last-donation-date-selector leyka-selector" value="<?php echo isset($_GET['last-donation-date']) ? esc_attr($_GET['last-donation-date']) : '';?>" placeholder="<?php _e('Last payment date', 'leyka');?>">
    					
    					<input type="text" name="leyka-payment-status-selector leyka-selector" value="" placeholder="<?php _e('Payment status', 'leyka');?>">

                        <input type="text" name="leyka-donors-tags-selector leyka-selector" value="" placeholder="<?php _e('Donors tags', 'leyka');?>">
                        
                        <?php $filter_value = isset($_GET['donors-tags']) ? (array)$_GET['donors-tags'] : array();?>
                        <select name="donors-tags[]" multiple="multiple">
    
                            <option value="" <?php echo !$filter_value ? 'selected="selected"' : '';?>>
                                <?php _e('Donors tags', 'leyka');?>
                            </option>
    
                            <?php $donors_tags = get_terms(
                                Leyka_Donor::DONORS_TAGS_TAXONOMY_NAME,
                                array('hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC',)
                            );
    
                            foreach($donors_tags as $tag) {?>
                                <option value="<?php echo $tag->term_id;?>" <?php echo is_array($filter_value) && in_array($tag->term_id, $filter_value) ? 'selected="selected"' : '';?>>
                                    <?php echo $tag->name;?>
                                </option>
                            <?php }?>
    
                        </select>
    
                        <input type="text" name="leyka-gateways-selector leyka-selector" value="" placeholder="<?php _e('Payment gateway', 'leyka');?>">
                        
                        <?php $filter_value = isset($_GET['gateways']) ? (array)$_GET['gateways'] : array();?>
                        <select name="gateways[]" multiple="multiple">
    
                            <option value="" <?php echo !$filter_value ? 'selected="selected"' : '';?>>
                                <?php _e('Payment gateway', 'leyka');?>
                            </option>
    
                            <?php $gateways = leyka_get_gateways();
                            usort($gateways, function($gateway_first, $gateway_second){
                                return strcmp($gateway_first->name, $gateway_second->name);
                            });
    
                            foreach($gateways as $gateway) {?>
                                <option value="<?php echo $gateway->id;?>" <?php echo is_array($filter_value) && in_array($gateway->id, $filter_value) ? 'selected="selected"' : '';?>>
                                    <?php echo $gateway->name;?>
                                </option>
                            <?php }?>
    
                        </select>
                        
                    </div>

                </div>

                <div class="col-2">
                    <input type="submit" class="button" value="<?php _e('Filter the data', 'leyka');?>">
                    <input type="reset" class="reset-filters" value="<?php _e('Reset the filter', 'leyka');?>">
                </div>

            </form>

            <div class="donors-list-export"><button><?php _e('Export the list in CSV', 'leyka');?></button></div>

            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php $this->_donors_list_table->prepare_items();
                        $this->_donors_list_table->display();?>
                    </form>
                </div>
            </div>

        </div>

    </div>
</div>