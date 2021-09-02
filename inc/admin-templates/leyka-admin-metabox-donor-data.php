<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

try {
    $donor = new Leyka_Donor(absint($_GET['donor']));
} catch(Exception $e) {
    wp_die($e->getMessage());
}?>

<?php wp_nonce_field('leyka_save_donor_name', 'leyka_save_donor_name_nonce');?>
<?php wp_nonce_field('leyka_save_donor_description', 'leyka_save_donor_description_nonce');?>

<div class="donor-col-1">
    <div class="donor-photo"><img src="<?php echo get_avatar_url($donor->id, ['size' => 96,]);?>" alt=""></div>
</div>

<div class="donor-col-2">
    <div class="donor-info-main">

        <div class="donor-name leyka-editable-str-wrapper">

            <h2 class="leyka-editable-str-result" id="editable-donor-name-str-result" str-field="editable-donor-name-str-field">
                <?php echo esc_html($donor->name);?>
            </h2>

        	<input class="leyka-editable-str-field" type="text" value="<?php echo esc_html($donor->name);?>" style="display: none;" id="editable-donor-name-str-field" str-btn="editable-donor-name-str-btn" str-result="editable-donor-name-str-result" save-action="leyka_save_donor_name" text-item-id="">

            <div class="loading-indicator-wrap" style="display: none;">
                <div class="loader-wrap"><span class="leyka-loader xxs"></span></div>
                <img class="ok-icon" src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/dashboard/icon-check.svg" alt="">
            </div>

            <a href="#" class="donor-data-edit leyka-editable-str-btn" id="editable-donor-name-str-btn" str-field="editable-donor-name-str-field" title="<?php _e('Edit');?>"> </a>

        </div>

        <div class="donor-description">

            <div class="donor-view-description-wrapper leyka-editable-str-wrapper" style="<?php if(!$donor->description) {?>display: none;<?php }?>">

                <div class="description-text leyka-editable-str-result"
            		id="editable-donor-description-str-result" 
            		str-field="editable-donor-description-str-field">
                    <?php echo esc_html($donor->description);?>
                </div>
                
            	<textarea class="leyka-editable-str-field" type="text" style="display: none;" 
            		id="editable-donor-description-str-field" 
            		str-btn="editable-donor-description-str-btn" 
            		str-result="editable-donor-description-str-result"
            		save-action="leyka_save_donor_description"
            		text-item-id=""><?php echo esc_html($donor->description);?></textarea>

                <div class="loading-indicator-wrap" style="display: none;">
                    <div class="loader-wrap"><span class="leyka-loader xxs"></span></div>
                    <img class="ok-icon" src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/dashboard/icon-check.svg" alt="">
                </div>
                
                <a href="#" class="donor-data-edit leyka-editable-str-btn" title="<?php _e('Edit');?>"
                	id="editable-donor-description-str-btn" 
                	str-field="editable-donor-description-str-field"> </a>

            </div>

        	<?php if( !$donor->description ) {?>

        	<div class="donor-add-description-wrapper">

                <a href="#" class="donor-add-description-link" data-donor-id="<?php echo $donor->id;?>" title="<?php _e('Add the description', 'leyka');?>"><?php _e('Add the description', 'leyka');?></a>
                
                <form class="add-donor-description-form" method="post">

                    <textarea id="donor-description-field" name="donor-description"></textarea>

                    <input type="submit" value="<?php _e('Save description', 'leyka');?>">

                    <div class="loading-indicator-wrap" style="display: none;">
                        <div class="loader-wrap"><span class="leyka-loader xxs"></span></div>
                        <img class="ok-icon" src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/dashboard/icon-check.svg" alt="">
                    </div>
                    
                </form>

        	</div>

        	<?php }?>
        </div>

    </div>

    <div class="donor-info-details">

        <dl>
            <dt><?php _e("Donor's type", 'leyka');?></dt>
            <dd><?php echo $donor->type_label;?></dd>

            <dt><?php _e('Email', 'leyka');?></dt>
            <dd><a href="mailto:<?php echo $donor->email;?>"><?php echo $donor->email;?></a></dd>

            <!--<dt><?php _e('GA Client ID', 'leyka');?></dt>
            <dd><?php echo __('none'); //echo get_user_meta($donor_user->ID, 'leyka_donor_ga_client_id', true);?></dd>-->

            <dt><?php _e('First donation', 'leyka');?></dt>
            <dd><?php echo $donor->first_donation_date_label;?></dd>

            <dt><?php _e('Subscribed to news', 'leyka');?></dt>
            <dd>

            <?php $campaigns = [];
            foreach($donor->campaigns_news_subscriptions as $campaign_id => $title) {
                $campaigns[] = '«<a href="'.get_edit_post_link($campaign_id).'">'.$title.'</a>»';
            }
            echo $campaigns ? implode(', ', $campaigns) : _x('none', 'Multiple case', 'leyka');?>

            </dd>

            <dt><?php _e('Campaigns', 'leyka');?></dt>
            <dd>

            <?php $campaigns = [];
            foreach($donor->campaigns as $campaign_id => $title) {
                $campaigns[] = '«<a href="'.get_edit_post_link($campaign_id).'">'.$title.'</a>»';
            }
            echo $campaigns ? implode(', ', $campaigns) : _x('none', 'Multiple case', 'leyka');?>

            </dd>
        </dl>

    </div>
</div>