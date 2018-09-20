<?php if( !defined('WPINC') ) die;

/** Custom field group for the CP payments cards. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */

$campaigns = array(); get_posts(array(
            'post_type' => Leyka_Campaign_Management::$post_type,
            'post_status' => array('publish', 'pending', 'draft'),
            'posts_per_page'   => 1,
            'fields' => 'ids',
        ));

$campaign_id = count($campaigns) ? $campaigns[0] : null;
$campaign_url = $campaign_id ? get_the_permalink($campaign_id) : null;
$campaign_url_encoded = $campaign_url ? urlencode($campaign_url) : null;

?>

<div class="<?php echo $this->field_type;?> cp-final-share-campaign">

<?php if($campaign_id):?>
    <!--
    <p>Поделитесь вашей последней кампанией с друзьями и попросите их отправить пожертвование. Так вы сможете протестировать новый способ оплаты.</p>
    -->
    <p>Перейдите на страницу вашей кампании, чтобы протестировать ее.</p>
<?php else:?>
    <p>Создайте кампанию, чтобы начать сбор пожертвований.</p>
<?php endif?>

<div class="final-button">
    <?php if($campaign_id):?>
        <a class="step-next button button-primary" href="<?php echo $campaign_url?>" target="_blank">Протестировать кампанию</a>
    <?php else:?>
        <a class="step-next button button-primary" href="<?php echo admin_url("/admin.php?page=leyka_settings_new&screen=wizard-init")?>">Создать кампанию</a>
    <?php endif?>
</div>

<!--
<div class="share-campaign">
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $campaign_url_encoded?>" target="_blank"><img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-share-fb.svg"/></a>
    <a href="https://vk.com/share.php?url=<?php echo $campaign_url_encoded?>" target="_blank"><img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-share-vk.svg"/></a>
    <a href="https://connect.ok.ru/offer?url=<?php echo $campaign_url_encoded?>" target="_blank"><img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-share-od.svg"/></a>
</div>
-->

</div>
