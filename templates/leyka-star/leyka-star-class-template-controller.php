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
        $main_currency_id = leyka_options()->opt('currency_main');
        
        $amount_mode = leyka_options()->opt_template('donation_sum_field_type', 'star');

        if($amount_mode == 'fixed' || $amount_mode == 'mixed') {
            $amount_variants = $campaign->default_payments_amounts === '1' ? [
                'single' => leyka_options()->opt_safe('payments_single_amounts_options_'.$main_currency_id),
                'recurrent' => leyka_options()->opt_safe('payments_recurrent_amounts_options_'.$main_currency_id)
            ] : [
                'single' =>  $campaign->{'payments_single_amounts_options_'.$main_currency_id},
                'recurrent' => $campaign->{'payments_recurrent_amounts_options_'.$main_currency_id},
            ];
        } else {
            $amount_variants = [];
        }

        $payments_amounts_tab_titles = $campaign->default_payments_amounts === '1' ? [
            'single' => leyka_options()->opt('payments_single_tab_title'),
            'recurrent' => leyka_options()->opt('payments_recurrent_tab_title')
        ] : [
            'single' => $campaign->payments_single_tab_title,
            'recurrent' => $campaign->payments_recurrent_tab_title
        ];

        $this->_template_data[$campaign->id] = [
        	'currency_id' => $main_currency_id,
            'currency_label' => $currencies[$main_currency_id]['label'],
            'payments_amounts_tab_titles' => $payments_amounts_tab_titles,
            'amount_default' => $currencies[$main_currency_id]['amount_settings']['flexible'],
            'amount_min' => $currencies[$main_currency_id]['bottom'],
            'amount_max' => $currencies[$main_currency_id]['top'],
            'amount_max_total' => leyka_options()->opt('leyka_currency_'.$main_currency_id.'_max_sum'),
            'pm_list' => leyka_get_pm_list(true, $main_currency_id),
            'amount_mode' => $amount_mode,
            'amount_variants' => $amount_variants,
        ];

    }

}