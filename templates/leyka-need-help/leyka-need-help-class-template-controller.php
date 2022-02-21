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

                $amount_variants = $campaign->default_payments_amounts === '1' ? [
                    'single' => leyka_options()->opt_safe('payments_single_amounts_options_'.$main_currency_id),
                    'recurring' => leyka_options()->opt_safe('payments_recurring_amounts_options_'.$main_currency_id)
                ] : [
                    'single' =>  $campaign->{'payments_single_amounts_options_'.$main_currency_id},
                    'recurring' => $campaign->{'payments_recurring_amounts_options_'.$main_currency_id},
                ];

                // TODO: remove these checks when validation on the campaign edit page will be fixed
                foreach ($amount_variants['single'] as $variant_idx => $variant_data) {
                    if (empty($variant_data['amount']) ||
                        $variant_data['amount'] < $currencies[$main_currency_id]['bottom'] ||
                        $variant_data['amount'] > $currencies[$main_currency_id]['top']) {
                        unset($amount_variants['single'][$variant_idx]);
                    }
                }

                foreach ($amount_variants['recurring'] as $variant_idx => $variant_data) {
                    if (empty($variant_data['amount']) ||
                        $variant_data['amount'] < $currencies[$main_currency_id]['bottom'] ||
                        $variant_data['amount'] > $currencies[$main_currency_id]['top']) {
                        unset($amount_variants['recurring'][$variant_idx]);
                    }
                }

            } else {
                $amount_variants = [];
            }

        }

        $payments_amounts_tab_titles = $campaign->default_payments_amounts === '1' ? [
            'single' => leyka_options()->opt('payments_single_tab_title'),
            'recurring' => leyka_options()->opt('payments_recurring_tab_title')
        ] : [
            'single' => $campaign->payments_single_tab_title,
            'recurring' => $campaign->payments_recurring_tab_title
        ];

        $this->_template_data[$campaign->id] = [
        	'currency_id' => $main_currency_id,
            'currency_label' => $currencies[$main_currency_id]['label'],
            'payments_amounts_tab_titles' => $payments_amounts_tab_titles,
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