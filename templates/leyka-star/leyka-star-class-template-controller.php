<?php if( !defined('WPINC') ) die;
/**
 * Leyka Templates Controller class.
 **/

class Leyka_Star_Template_Controller extends Leyka_Template_Controller {

    protected static $_instance;

    protected function _generate_template_data(Leyka_Campaign $campaign) {

        if( !empty($this->_template_data[$campaign->id]) ) {
            return;
        }

        $currencies = leyka_get_currencies_data();
        $main_currency_id = leyka_options()->opt('main_currency');
        
        $amount_mode = leyka_options()->opt_template('donation_sum_field_type', 'star');
        if($amount_mode == 'fixed' || $amount_mode == 'mixed') {
            $amount_variants = explode(',', $currencies[$main_currency_id]['amount_settings']['fixed']);
        } else {
            $amount_variants = array();
        }
        
        $this->_template_data[$campaign->id] = array(
        	'currency_id' => $main_currency_id,
            'currency_label' => $currencies[$main_currency_id]['label'],
            'amount_default' => $currencies[$main_currency_id]['amount_settings']['flexible'],
            'amount_min' => $currencies[$main_currency_id]['bottom'],
            'amount_max' => $currencies[$main_currency_id]['top'],
            'amount_max_total' => leyka_options()->opt('leyka_currency_'.$main_currency_id.'_max_sum'),
            'pm_list' => leyka_get_pm_list(true, $main_currency_id),
            
            'amount_mode' => $amount_mode,
            'amount_variants' => $amount_variants,
        );

    }

}