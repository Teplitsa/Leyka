<?php if( !defined('WPINC') ) die;

/** Custom field group for the Campaign completed Step of the Init wizard. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */

$campaigns = get_posts([
    'post_type' => Leyka_Campaign_Management::$post_type,
    'post_status' => ['publish', 'pending', 'draft',],
    'posts_per_page' => 1,
    'fields' => 'ids',
]);

$campaign_id = count($campaigns) ? $campaigns[0] : null;
$campaign_url = $campaign_id ? get_the_permalink($campaign_id) : null;?>

<div id="<?php echo $this->id;?>" class="settings-block custom-block <?php echo $this->field_type;?>">
<?php if(leyka_are_bank_essentials_set()) { // Bank essentials are filled

    $init_campaign = get_post(get_transient('leyka_init_campaign_id'));?>

    <ul class="leyka-campaign-completed" data-campaign-id="<?php echo $init_campaign->ID;?>">
        <li>
            <div class="item-text"><?php esc_html_e('The campaign set up by the address:', 'leyka');?></div>
            <div class="item-info"><?php echo leyka_admin_get_slug_edit_field($init_campaign);?></div>
        </li>
        <li>
            <div class="item-text">
                <?php esc_html_e('You may insert the following shortcode on any of your site pages:', 'leyka');?>
            </div>
            <div class="item-info">
                <span class="leyka-wizard-copy2clipboard">
                    <?php echo leyka_admin_get_shortcode_field($init_campaign);?>
                </span>
            </div>
        </li>
        <li>
            <div class="item-text">
                <?php _e('<strong>Payment via bank order print-outs</strong> set up.', 'leyka');?>
            </div>
        </li>
    </ul>

    <div class="<?php echo $this->field_type;?> init-final-step-go-campaign">

    <?php if($campaign_id) {?>
        <p><?php esc_html_e('Proceed to your campaign page to test it out.', 'leyka');?></p>
    <?php } else {?>
        <p><?php esc_html_e('Create a campaign to start donations collection.', 'leyka');?></p>
    <?php }?>

        <div class="final-button">
        <?php if($campaign_id) {?>
            <a class="step-next button button-primary" href="<?php echo $campaign_url?>" target="_blank"><?php esc_html_e('Open the campaign page', 'leyka');?></a>
        <?php } else {?>
            <a class="step-next button button-primary go-back" href="<?php echo admin_url('/admin.php?page=leyka_settings_new&screen=wizard-init');?>"><?php esc_html_e('Create a campaign', 'leyka');?></a>
        <?php }?>
        </div>

    </div>

<?php } else { // Some bank essentials are NOT filled ?>

    <p><?php esc_html_e("The campaign is set up, but you cannot collect funds as you haven't set all your data. At the moment, you cannot use even the simplest payment method - \"payment via bank orders\".", 'leyka');?></p>
    <p><?php esc_html_e('To setup this payment method, please return and fill the necessary data.', 'leyka');?></p>
    
<?php }?>

</div>
