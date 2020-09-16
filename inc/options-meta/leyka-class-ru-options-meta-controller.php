<?php if( !defined('WPINC') ) die;

class Leyka_Ru_Options_Meta_Controller extends Leyka_Options_Meta_Controller {

    protected static $_instance;

    protected function _get_meta_org() { // Keywords: org_
        return parent::_get_meta_org() + array(
            'org_state_reg_number' => array(
                'type' => 'text',
                'title' => __('The organization state registration number', 'leyka'),
                'description' => __('Enter the organization state registration number.', 'leyka'),
                'required' => true,
                'placeholder' => __('E.g., 1023400056789', 'leyka'),
                'mask' => "'mask': '9{13}'",
            ),
            'org_kpp' => array(
                'type' => 'text',
                'title' => __('The organization statement of the account number', 'leyka'),
                'description' => __("Enter the organization statement of the account number.", 'leyka'),
                'required' => true,
                'placeholder' => __('E.g., 780302015', 'leyka'),
                'mask' => "'mask': '9{9}'",
            ),
            'org_inn' => array(
                'type' => 'text',
                'title' => __('The organization taxpayer individual number', 'leyka'),
                'description' => __('Enter the organization individual number of a taxpayer.', 'leyka'),
                'required' => true,
                'placeholder' => __('E.g., 4283256127', 'leyka'),
                'mask' => "'mask': '9{10}'",
            ),
            'org_bank_bic' => array(
                'type' => 'text',
                'title' => __('The organization bank BIC number', 'leyka'),
                'description' => __("Enter a BIC of the organization bank.", 'leyka'),
                'required' => true,
                'placeholder' => __('E.g., 044180293', 'leyka'),
                'mask' => "'mask': '9{9}'",
            ),
            'org_bank_corr_account' => array(
                'type' => 'text',
                'title' => __('The organization bank correspondent account number', 'leyka'),
                'description' => __('Enter a correspondent account number of the organization.', 'leyka'),
                'required' => true,
                'placeholder' => __('E.g., 30101810270902010595', 'leyka'),
                'mask' => "'mask': '9{20}'",
            ),
        );
    }

}