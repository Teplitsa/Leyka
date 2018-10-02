<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa payments cards. */

/** @var $this Leyka_Text_Block A block for which the template is used. */

$campaigns = get_posts(array(
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

</div>
