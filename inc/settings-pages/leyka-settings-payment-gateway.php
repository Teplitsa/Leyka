<?php if( !defined('WPINC') ) die; // If this file is called directly, abort ?>

<div class="main-area-top">
    <a href="<?php echo admin_url('admin.php?page=leyka_settings&stage=payment');?>" class="settings-return-link">К списку платёжных операторов</a>
</div>

<?php $gateway = leyka_get_gateway_by_id($_GET['gateway']);
if( !$gateway ) {?>
    <p class="error">Неизвестный платёжный оператор.</p>
<?php } else { // Gateway settings area ?>

<div class="main-area single-gateway-settings gateway-<?php echo $_GET['gateway'];?>">

    <div class="gateway-settings-header">

        <div class="gateway-header-element gateway-logo"><img src="<?php echo $gateway->icon_url;?>"></div>
        <h2 class="gateway-header-element"><?php echo $gateway->title;?></h2>

        <?php if($gateway->has_wizard) {?>
        <a class="gateway-header-element gateway-wizard-link" href="<?php echo $gateway->wizard_url;?>" title="Открыть Мастер пошагового подключения к платёжному оператору">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-wizard.svg">
        </a>
        <?php }?>

    </div>

    <div class="gateway-info">

        <div class="gateway-links">
            <?php if($gateway->registration_url) {?>
            <a href="<?php echo $gateway->registration_url;?>" class="gateway-registration-link">
                <?php echo $gateway->registration_url;?>
            </a>
            <?php }?>

            <?php if($gateway->docs_url) {?>
                <a href="<?php echo $gateway->docs_url;?>" class="gateway-docs-link">+документация</a>
            <?php }?>
        </div>

        <div class="gateway-description"><?php echo $gateway->description;?></div>

        <div class="gateway-details">

            <?php if($gateway->min_commission && $gateway->min_commission > 0.0) {?>
            <div class="gateway-commission">
                от <span class="commission-size"><?php echo $gateway->min_commission;?>%</span>
                <div class="details-label">комиссия</div>
            </div>
            <?php }

            if($gateway->has_recurring) {?>
            <div class="gateway-has-recurring">
                <div class="has-recurring-icon"><img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-recurring.svg"></div>
                <div class="details-label">рекурренты</div>
            </div>
            <?php }

            if($gateway->receiver_types) {?>
            <div class="gateway-receiver-types">
                <div class="receiver-type-icon">
                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-receiver-type-<?php echo count($gateway->receiver_types) > 1 ? 'all' : $gateway->receiver_types[0];?>.svg">
                </div>
                <div class="details-label">получатель</div>
            </div>
            <?php }?>

        </div>

    </div>

    <div class="gateway-settings">
        Настройки гейта
    </div>

    <div class="gateway-pm-list">
        Список ПМов
    </div>

</div>

<?php }