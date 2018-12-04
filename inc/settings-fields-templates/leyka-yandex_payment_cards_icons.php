<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa payments cards. */

/** @var $this Leyka_Text_Block A block for which the template is used. */?>

<div class="<?php echo $this->field_type;?> custom-block-payment-cards-icons">

    <div class="cp-supported-cards">
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-card-mastercard.svg" alt="">
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-card-visa.svg" alt="">
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-card-mir.svg" alt="">
    </div>

    <h3><?php esc_html_e('Before you begin Yandex.Kassa registration, please, prepare scaned copies of the following documents (jpg/png):', 'leyka');?></h3>

    <ul>
        <li><?php esc_html_e("Organization head's passport - the full main page and the registration page.", 'leyka');?></li>
        <li><?php esc_html_e('The the organization state registration certificate.', 'leyka');?></li>
    </ul>

</div>
