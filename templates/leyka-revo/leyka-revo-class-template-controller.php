<?php if( !defined('WPINC') ) die;
/**
 * Leyka Templates Controller class.
 **/

class Leyka_Revo_Template_Controller extends Leyka_Template_Controller {

    /** @var $_instance Leyka_Template_Controller */
    protected static $_instance;

    protected function _generate_template_data(Leyka_Campaign $campaign) {

        if( !empty($this->_template_data[$campaign->id]) ) {
            return;
        }

        $currencies = leyka_get_currencies_data();
        $main_currency_id = leyka_options()->opt('main_currency');

        $this->_template_data[$campaign->id] = array(
            'currency_label' => $currencies[$main_currency_id]['label'],
            'amount_default' => $currencies[$main_currency_id]['amount_settings']['flexible'],
            'amount_min' => $currencies[$main_currency_id]['bottom'],
            'amount_max' => leyka_options()->opt('revo_template_slider_max_sum'),
            'pm_list' => leyka_get_pm_list(true, $main_currency_id),
            //            '' => ,
        );

    }

} //class end