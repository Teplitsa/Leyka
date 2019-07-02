<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

try {
    $donor = new Leyka_Donor(absint($_GET['donor']));
} catch(Exception $e) {
    wp_die($e->getMessage());
}?>

<div class="donor-col-1">
    <div class="donor-photo"><img src="<?php echo get_avatar_url($donor_user->ID, array('size' => 96));?>" alt=""></div>
</div>

<div class="donor-col-2">
    <div class="donor-info-main">

        <div class="donor-name" style="border: 1px solid black;">
            <h2><?php echo $donor_user->display_name;?></h2>
            <div class="donor-data-edit" data-donor-id="<?php echo $donor_user->ID;?>" title="<?php _e('Edit');?>">Edit</div>
        </div>

        <div class="donor-description" style="border: 1px solid black;">

        <?php $donor_description = get_user_meta($donor_user->ID, 'leyka_donor_description', true);

        if($donor_description) {?>
            <div class="description-text"><?php echo $donor_description;?></div>
            <div class="donor-data-edit" data-donor-id="<?php echo $donor_user->ID;?>" title="<?php _e('Edit');?>">Edit</div>
        <?php } else {?>
            <div class="donor-data-add" data-donor-id="<?php echo $donor_user->ID;?>" title="<?php _e('Add the description', 'leyka');?>">Add the description</div>
        <?php }?>
        </div>

    </div>

    <div class="donor-info-details">

        <dl>
            <dt><?php _e("Donor's type", 'leyka');?></dt>
            <dd><?php echo 'Рекуррент'; // get_user_meta($donor_user->ID, '', true);?></dd>

            <dt><?php _e('Email', 'leyka');?></dt>
            <dd><a href="mailto:<?php echo $donor_user->user_email;?>"><?php echo $donor_user->user_email;?></a></dd>

            <dt><?php _e('GA Client ID', 'leyka');?></dt>
            <dd><?php echo __('none'); //echo get_user_meta($donor_user->ID, 'leyka_donor_ga_client_id', true);?></dd>

            <dt><?php _e('First donation', 'leyka');?></dt>
            <dd>
                <?php $first_donation_date = get_user_meta($donor_user->ID, 'leyka_donor_first_donation_date', true);
                echo $first_donation_date ? date('d.m.Y', $first_donation_date) : '';?>
            </dd>

            <dt><?php _e('Subscribed to news', 'leyka');?></dt>
            <dd>

            <?php $campaigns = array();
            foreach(get_user_meta($donor_user->ID, 'leyka_donor_campaigns_news_subscriptions', true) as $campaign_id => $title) {
                $campaigns[] = '«<a href="'.get_edit_post_link($campaign_id).'">'.$title.'</a>»';
            }
            echo $campaigns ? implode(', ', $campaigns) : __('none', 'leyka');?>

            </dd>

            <dt><?php _e('Campaigns', 'leyka');?></dt>
            <dd>

            <?php $campaigns = array();
            foreach(get_user_meta($donor_user->ID, 'leyka_donor_campaigns', true) as $campaign_id => $title) {
                $campaigns[] = '«<a href="'.get_edit_post_link($campaign_id).'">'.$title.'</a>»';
            }
            echo $campaigns ? implode(', ', $campaigns) : __('none', 'leyka');?>

            </dd>
        </dl>

    </div>
</div>