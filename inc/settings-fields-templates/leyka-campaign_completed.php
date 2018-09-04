<?php if( !defined('WPINC') ) die;

/** Custom field group for the Campaign completed Step of the Init wizard. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */
?>

<div id="<?php echo $this->id;?>" class="settings-block custom-block <?php echo $this->field_type;?>">
<?php if(leyka_are_bank_essentials_set()) { // Bank essentials are filled

    $init_campaign = get_post(get_transient('leyka_init_campaign_id'));?>

    <ul class="leyka-campaign-completed" data-campaign-id="<?php echo $init_campaign->ID;?>">
        <li>
            <div class="item-text">Кампания настроена по адресу:</div>
            <div class="item-info">
                <?php echo leyka_admin_get_slug_edit_field($init_campaign);?>
            </div>
        </li>
        <li>
            <div class="item-text">Вы можете вставить на любые страницы вашего сайта шорт-код</div>
            <div class="item-info">
                <?php echo leyka_admin_get_shortcode_field($init_campaign);?>
                <a href="<?php echo get_edit_post_link($campaign->id);?>" class="inline-action inline-copy-shortcode">Копировать для вставки</a>
            </div>
        </li>
        <li>
            <div class="item-text">Подключена <strong>оплата с помощью банковских квитанций</strong></div>
        </li>
    </ul>

<?php } else { // Some bank essentials are NOT filled ?>

    <p>Кампания настроена, но вы не сможете получать средства, т.к. ранее вы указали не все ваши данные. На данный момент вам будет недоступен даже самый простой платежный способ — «оплата с помощью банковских квитанций».</p>
    <p>Чтобы подключить этот способ оплаты, заполните недостающие данные ниже.</p>

<?php }?>

</div>