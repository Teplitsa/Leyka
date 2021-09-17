<?php if( !defined('WPINC') ) die;

class Leyka_Eu_Options_Allocator extends Leyka_Ru_Options_Allocator {

    protected static $_instance;

    protected function _get_main_currency_options_tabs() {

        $main_currency_id = leyka_options()->opt_safe('currency_main');
        $main_currency_info = leyka_get_currencies_full_info($main_currency_id);

        return [
            $main_currency_id.'_currency' => [
                'title' => $main_currency_info['title'],
                'sections' => [
                    [
                        'title' => '',
                        'options' => [
                            "currency_{$main_currency_id}_label", "currency_{$main_currency_id}_min_sum",
                            "currency_{$main_currency_id}_max_sum", "currency_{$main_currency_id}_flexible_default_amount",
                            "currency_{$main_currency_id}_fixed_amounts",
                        ],
                    ],
                ],
            ],
        ];
    }

}