<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa general info step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */

if( !empty($this->field_data['screenshot'])) {
    leyka_show_wizard_captioned_screenshot(
        $this->field_data['screenshot'],
        empty($this->field_data['screenshot_full']) ? null : $this->field_data['screenshot_full']
    );
}