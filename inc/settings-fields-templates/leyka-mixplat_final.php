<?php if( !defined('WPINC') ) die;

/** Custom field group for the MIXPLAT payments cards. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */

$campaigns = get_posts([
    'post_type' => Leyka_Campaign_Management::$post_type,
    'post_status' => ['publish', 'pending', 'draft',],
    'posts_per_page'   => 1,
    'fields' => 'ids',
]);

$campaign_id = count($campaigns) ? $campaigns[0] : null;
$campaign_url = $campaign_id ? get_the_permalink($campaign_id) : null;
//$campaign_url_encoded = $campaign_url ? urlencode($campaign_url) : null;?>

<div class="<?php echo esc_attr( $this->field_type );?> cp-final-share-campaign">
    <p>
        <?
            esc_html_e("You have enabled payment acceptance via the Mixplat. Payments using payment systems, bank cards and mobile phones are available to you.", "leyka");
        ?>
    </p>
    
    <p>
        <?
            esc_html_e("Now go to payment settings page and enable payment methods after confirmation from MIXPLAT", "leyka");
        ?>
    </p>

    <div class="final-button">
        <a class="step-next button button-primary" href="<?php echo esc_url( admin_url("/admin.php?page=leyka_settings&stage=payment&gateway=mixplat") );?>"><?php esc_html_e('Go to payment settings', 'leyka');?></a>
    </div>


</div>