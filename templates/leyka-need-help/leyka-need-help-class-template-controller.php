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

        $amount_mode = leyka_options()->opt_template('donation_sum_field_type', 'need-help');
        $payments_amounts_tab_titles = $campaign->default_payments_amounts === '1' ? [
            'single' => leyka_options()->opt('payments_single_tab_title'),
            'recurring' => leyka_options()->opt('payments_recurring_tab_title')
        ] : [
            'single' => $campaign->payments_single_tab_title,
            'recurring' => $campaign->payments_recurring_tab_title
        ];

        $currencies = $campaign->daily_rouble_mode_on_and_valid ?
            [leyka_options()->opt_safe('currency_main') => leyka_get_currency_data()] :
            leyka_get_currencies_data();

        foreach($currencies as $currency_id => $currency_data) {

            if( !leyka_get_pm_list(true, $currency_id) || !leyka_gw_is_currency_active($currency_id)) {
                continue;
            }

            if($campaign->daily_rouble_mode_on_and_valid) {

                $amount_mode = 'fixed';
                $amount_variants = array_map(
                    function($amount) {
                        return absint(trim($amount));
                    },
                    explode(',', $campaign->daily_rouble_amount_variants)
                );
                $pm_list = [$campaign->daily_rouble_pm];

            } else {

                $pm_list = leyka_get_pm_list(true, $currency_id);

                if($amount_mode === 'fixed' || $amount_mode === 'mixed') {

                    $amount_variants = $campaign->default_payments_amounts === '1' ? [
                        'single' => leyka_options()->opt_safe('payments_single_amounts_options_'.$currency_id),
                        'recurring' => leyka_options()->opt_safe('payments_recurring_amounts_options_'.$currency_id)
                    ] : [
                        'single' => $campaign->{'payments_single_amounts_options_'.$currency_id},
                        'recurring' => $campaign->{'payments_recurring_amounts_options_'.$currency_id},
                    ];

                    // TODO: remove these checks when validation on the campaign edit page will be fixed
                    foreach($amount_variants['single'] as $variant_idx => $variant_data) {

                        if(
                            empty($variant_data['amount'])
                            || $variant_data['amount'] < $currency_data['bottom']
                            || $variant_data['amount'] > $currency_data['top']
                        ) {
                            unset($amount_variants['single'][$variant_idx]);
                        }

                    }

                    foreach($amount_variants['recurring'] as $variant_idx => $variant_data) {

                        if(
                            empty($variant_data['amount'])
                            || $variant_data['amount'] < $currency_data['bottom']
                            || $variant_data['amount'] > $currency_data['top']
                        ) {
                            unset($amount_variants['recurring'][$variant_idx]);
                        }

                    }

                } else {
                    $amount_variants = [];
                }

            }

            $currencies_data[$currency_id] = [
                'currency_label' => $currency_data['label'],
                'amount_default' => $currency_data['amount_settings']['flexible'],
                'amount_min' => $currency_data['bottom'],
                'amount_max' => $currency_data['top'],
                'amount_max_total' => leyka_options()->opt('leyka_currency_' . $currency_id . '_max_sum'),
                'pm_list' => $pm_list,
                'amount_variants' => $amount_variants,
            ];

        }

        $this->_template_data[$campaign->id] = [
        	'payments_amounts_tab_titles' => $payments_amounts_tab_titles,
            'main_currency_id' => in_array(leyka_options()->opt('currency_main'), array_keys($currencies_data)) ?
                leyka_options()->opt('currency_main') : array_keys($currencies_data)[0],
            'currencies' => $currencies_data,
            'amount_mode' => $amount_mode,
            'platform_signature_on_form_enabled' => leyka_options()->opt('platform_signature_on_form_enabled'),
            'cryptocurrencies_wallets' => leyka_options()->opt('cryptocurrencies_wallets'),
            'cryptocurrencies_text' => leyka_options()->opt('cryptocurrencies_text'),
        ];

    }

}