<?php if( !defined('WPINC') ) die;

/** Custom field group for the Campaign completed Step of the Init wizard. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */

$campaigns = get_posts(array(
            'post_type' => Leyka_Campaign_Management::$post_type,
            'post_status' => array('publish', 'pending', 'draft'),
            'posts_per_page'   => 1,
            'fields' => 'ids',
        ));

$campaign_id = count($campaigns) ? $campaigns[0] : null;
$campaign_url = $campaign_id ? get_the_permalink($campaign_id) : null;

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
                <a href="<?php echo get_edit_post_link($campaign->id);?>" class="inline-action inline-copy-shortcode">Скопировать</a>
            </div>
        </li>
        <li>
            <div class="item-text">Подключена <strong>оплата с помощью банковских квитанций</strong></div>
        </li>
    </ul>
    
    <div class="<?php echo $this->field_type;?> init-final-step-go-campaign">
    
        <?php if($campaign_id):?>
            <p>Перейдите на страницу вашей кампании, чтобы протестировать ее.</p>
        <?php else:?>
            <p>Создайте кампанию, чтобы начать сбор пожертвований.</p>
        <?php endif?>
        
        <div class="final-button">
            <?php if($campaign_id):?>
                <a class="step-next button button-primary" href="<?php echo $campaign_url?>" target="_blank">Открыть страницу кампании</a>
            <?php else:?>
                <a class="step-next button button-primary go-back" href="<?php echo admin_url("/admin.php?page=leyka_settings_new&screen=wizard-init")?>">Создать кампанию</a>
            <?php endif?>
        </div>
    
    </div>

<?php } else { // Some bank essentials are NOT filled ?>

    <p>Кампания настроена, но вы не сможете получать средства, т.к. ранее вы указали не все ваши данные. На данный момент вам будет недоступен даже самый простой платежный способ — «оплата с помощью банковских квитанций».</p>
    <p>Чтобы подключить этот способ оплаты, вернитесь и заполните недостающие данные.</p>
    
<?php }?>

</div>
