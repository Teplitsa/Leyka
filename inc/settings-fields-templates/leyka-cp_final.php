<?php if( !defined('WPINC') ) die;

/** Custom field group for the CP payments cards. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */

$campaign_id = get_transient('leyka_init_campaign_id');
$campaign_url_encoded = urlencode(get_the_permalink($campaign_id));

?>

<div class="<?php echo $this->field_type;?> cp-final-share-campaign">

<div class="share-campaign">
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $campaign_url_encoded?>" target="_blank"><img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-share-fb.svg"/></a>
    <a href="https://vk.com/share.php?url=<?php echo $campaign_url_encoded?>" target="_blank"><img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-share-vk.svg"/></a>
    <a href="https://connect.ok.ru/offer?url=<?php echo $campaign_url_encoded?>" target="_blank"><img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-share-od.svg"/></a>
</div>

</div>
