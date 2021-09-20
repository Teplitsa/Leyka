<?php if( !defined('WPINC') ) die;

class Leyka_Ru_Options_Meta_Controller extends Leyka_Options_Meta_Controller {

    protected static $_instance;

    protected function _get_meta_org() { // Keywords: org_
        return parent::_get_meta_org() + [
            'org_state_reg_number' => [
                'type' => 'text',
                'title' => __('The organization state registration number', 'leyka'),
                'description' => __('Enter the organization state registration number.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s'), '1023400056789'),
                'mask' => "'mask': '9{13}'",
            ],
            'org_kpp' => [
                'type' => 'text',
                'title' => __('The organization statement of the account number', 'leyka'),
                'description' => __("Enter the organization statement of the account number.", 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s'), '780302015'),
                'mask' => "'mask': '9{9}'",
            ],
            'org_inn' => [
                'type' => 'text',
                'title' => __('The organization taxpayer individual number', 'leyka'),
                'description' => __('Enter the organization individual number of a taxpayer.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s'), '4283256127'),
                'mask' => "'mask': '9{10}'",
            ],
            'org_bank_bic' => [
                'type' => 'text',
                'title' => __('The organization bank BIC number', 'leyka'),
                'description' => __("Enter a BIC of the organization bank.", 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s'), '044180293'),
                'mask' => "'mask': '9{9}'",
            ],
            'org_bank_corr_account' => [
                'type' => 'text',
                'title' => __('The organization bank correspondent account number', 'leyka'),
                'description' => __('Enter a correspondent account number of the organization.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s'), '30101810270902010595'),
                'mask' => "'mask': '9{20}'",
            ],
        ];
    }

}