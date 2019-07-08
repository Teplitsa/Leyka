<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

try {
    $donor = new Leyka_Donor(absint($_GET['donor']));
} catch(Exception $e) {
    wp_die($e->getMessage());
}?>

<div class="donor-col-1">
    <div class="donor-photo"><img src="<?php echo get_avatar_url($donor->id, array('size' => 96));?>" alt=""></div>
</div>

<div class="donor-col-2">
    <div class="donor-info-main">

        <div class="donor-name">
            <h2><?php echo $donor->name;?></h2>
            <a href="#" class="donor-data-edit" data-donor-id="<?php echo $donor->id;?>" title="<?php _e('Edit');?>"> </a>
        </div>

        <div class="donor-description">

        <?php 
            $donor->description = "Бесконечно малая величина позиционирует интеграл от функции, обращающейся в бесконечность вдоль линии, таким образом сбылась мечта идиота - утверждение полностью доказано. Векторное поле ускоряет интеграл Пуассона.";
            $donor->description = "";
        ?>
        <?php if($donor->description) {?>
            <div class="description-text"><?php echo $donor->description;?></div>
            <a href="#" class="donor-data-edit" data-donor-id="<?php echo $donor->id;?>" title="<?php _e('Edit');?>"> </a>
        <?php } else {?>
            <a href="#" class="donor-data-add" data-donor-id="<?php echo $donor->id;?>" title="<?php _e('Add the description', 'leyka');?>"><?php _e('Add the description', 'leyka');?></a>
        <?php }?>
        </div>

    </div>

    <div class="donor-info-details">

        <dl>
            <dt><?php _e("Donor's type", 'leyka');?></dt>
            <dd><?php echo $donor->type_label;?></dd>

            <dt><?php _e('Email', 'leyka');?></dt>
            <dd><a href="mailto:<?php echo $donor->email;?>"><?php echo $donor->email;?></a></dd>

            <dt><?php _e('GA Client ID', 'leyka');?></dt>
            <dd><?php echo __('none'); //echo get_user_meta($donor_user->ID, 'leyka_donor_ga_client_id', true);?></dd>

            <dt><?php _e('First donation', 'leyka');?></dt>
            <dd><?php echo $donor->first_donation_date_label;?></dd>

            <dt><?php _e('Subscribed to news', 'leyka');?></dt>
            <dd>

            <?php $campaigns = array();
            foreach($donor->campaigns_news_subscriptions as $campaign_id => $title) {
                $campaigns[] = '«<a href="'.get_edit_post_link($campaign_id).'">'.$title.'</a>»';
            }
            echo $campaigns ? implode(', ', $campaigns) : _x('none', 'Multiple case', 'leyka');?>

            </dd>

            <dt><?php _e('Campaigns', 'leyka');?></dt>
            <dd>

            <?php $campaigns = array();
            foreach($donor->campaigns as $campaign_id => $title) {
                $campaigns[] = '«<a href="'.get_edit_post_link($campaign_id).'">'.$title.'</a>»';
            }
            echo $campaigns ? implode(', ', $campaigns) : _x('none', 'Multiple case', 'leyka');?>

            </dd>
        </dl>

    </div>
</div>