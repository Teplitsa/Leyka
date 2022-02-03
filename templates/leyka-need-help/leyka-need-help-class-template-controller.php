<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template Controller class.
 **/

class Leyka_Need_Help_Template_Controller extends Leyka_Template_Controller {

    protected static $_instance;

    protected function _generate_template_data(Leyka_Campaign $campaign) {

        if( !empty($this->_template_data[$campaign->id]) ) {
            return;
        }

        $currencies = leyka_get_currencies_data();
        $main_currency_id = leyka_options()->opt('currency_main');

        if($campaign->daily_rouble_mode_on_and_valid) {

            $amount_mode = 'fixed';
            $amount_variants = array_map(
                function($amount){ return absint(trim($amount)); },
                explode(',', $campaign->daily_rouble_amount_variants)
            );

        } else {

            $amount_mode = leyka_options()->opt_template('donation_sum_field_type', 'need-help');
            if($amount_mode === 'fixed' || $amount_mode === 'mixed') {
                $amount_variants = explode(',', $currencies[$main_currency_id]['amount_settings']['fixed']);
            } else {
                $amount_variants = [];
            }

        }

        $this->_template_data[$campaign->id] = [
        	'currency_id' => $main_currency_id,
            'currency_label' => $currencies[$main_currency_id]['label'],
            'amount_default' => $currencies[$main_currency_id]['amount_settings']['flexible'],
            'amount_min' => $currencies[$main_currency_id]['bottom'],
            'amount_max' => $currencies[$main_currency_id]['top'],
            'amount_max_total' => leyka_options()->opt('leyka_currency_'.$main_currency_id.'_max_sum'),
            'pm_list' => $campaign->daily_rouble_mode_on_and_valid ?
                [$campaign->daily_rouble_pm] : leyka_get_pm_list(true, $main_currency_id),
            'amount_mode' => $amount_mode,
            'amount_variants' => $amount_variants,
            'platform_signature_on_form_enabled' => leyka_options()->opt('platform_signature_on_form_enabled')
        ];

    }

}