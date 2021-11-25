<?php if( !defined('WPINC') ) die;

class Leyka_By_Options_Meta_Controller extends Leyka_Options_Meta_Controller {

    protected static $_instance;

    protected function _get_meta_org() { // Keywords: org_
        return parent::_get_meta_org() + [
            'org_unp' => [
                'type' => 'text',
                'title' => __('The organization UNP number', 'leyka'),
                'description' => __('Enter the organization UNP number.', 'leyka'),
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), '328525617'),
//                'mask' => "'mask': '9{8}'", // Numbers for Org, numbers & letters for Phys
            ],
            'org_bank_iban' => [
                'type' => 'text',
                'title' => __('The organization bank account IBAN', 'leyka'),
                'comment' => '',
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'BY20 OLMP 3135 0000 0010 0000 0933'),
//                'mask' => "'mask': '9{6}'",
            ],
            'org_bank_bic_new' => [
                'type' => 'text',
                'title' => __('The organization bank BIC', 'leyka'),
                'comment' => '',
                'required' => true,
                'placeholder' => sprintf(__('E.g., %s', 'leyka'), 'OLMP'),
//                'mask' => "'mask': '9{6}'",
            ],
        ];
    }

    protected function _get_meta_terms() {

        $default_options_meta = parent::_get_meta_terms();

        $default_options_meta['agree_to_pd_terms_needed']['default'] = false;

        return $default_options_meta;

    }

}