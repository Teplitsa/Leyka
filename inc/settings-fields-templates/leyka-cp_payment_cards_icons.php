<?php if( !defined('WPINC') ) die;

/** Custom field group for the CP payments cards. */

/** @var $this Leyka_Text_Block A block for which the template is used. */?>

<div class="<?php echo $this->field_type;?> custom-block-payment-cards-icons">

    <div class="gateway-supported-cards">
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-card-mastercard.svg" alt="">
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-card-visa.svg" alt="">
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-card-mir.svg" alt="">
    </div>

</div>