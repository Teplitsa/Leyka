<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa payments cards. */

/** @var $this Leyka_Text_Block A block for which the template is used. */

$campaigns = get_posts([
    'post_type' => Leyka_Campaign_Management::$post_type,
    'post_status' => ['publish', 'pending', 'draft',],
    'posts_per_page'   => 1,
    'fields' => 'ids',
]);

$campaign_id = count($campaigns) ? $campaigns[0] : null;
$campaign_url = $campaign_id ? get_the_permalink($campaign_id) : null;?>

<div class="<?php echo $this->field_type;?>">

<?php if($campaign_id) {?>
    <p><?php esc_html_e('Proceed to your campaign page to test it out.', 'leyka');?></p>
<?php } else {?>
    <p><?php esc_html_e('Create a campaign to start donations collection.', 'leyka');?></p>
<?php }?>

    <div class="final-button">
        <?php if($campaign_id) {?>
            <a class="step-next button button-primary" href="<?php echo $campaign_url;?>" target="_blank"><?php esc_html_e('Test the campaign', 'leyka');?></a>
        <?php } else {?>
            <a class="step-next button button-primary" href="<?php echo admin_url('/admin.php?page=leyka_settings_new&screen=wizard-init');?>"><?php esc_html_e('Create a campaign', 'leyka');?></a>
        <?php }?>
    </div>

</div>
