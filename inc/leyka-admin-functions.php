<?php if( !defined('WPINC') ) die;

/**
 * Leyka Admin setup
 **/

if( !function_exists('leyka_admin_get_slug_edit_field') ) {
    function leyka_admin_get_slug_edit_field($campaign) {

        $campaign = new Leyka_Campaign($campaign);
        if($campaign->id <= 0) {
            return '';
        }

        $permalinks_on = !!get_option('permalink_structure');

        $campaign_permalink_parts = get_sample_permalink($campaign->id); // [0] - current URL template, [1] - current slug
        $campaign_base_url = rtrim(str_replace('%pagename%', '', $campaign_permalink_parts[0]), '/');
        $campaign_permalink_full = str_replace('%pagename%', $campaign_permalink_parts[1], $campaign_permalink_parts[0]);

        ob_start();?>

        <div class="leyka-campaign-permalink">

            <?php if($permalinks_on) {?>

                <span class="leyka-current-value">
                <span class="base-url"><?php echo $campaign_base_url;?></span>/<span class="current-slug"><?php echo $campaign_permalink_parts[1];?></span>
            </span>

                <a href="<?php echo get_edit_post_link($campaign->id);?>" class="inline-action inline-edit-slug">Редактировать</a>

                <span class="inline-edit-slug-form" data-slug-original="<?php echo $campaign_permalink_parts[1];?>" data-campaign-id="<?php echo $campaign->id;?>" data-nonce="<?php echo wp_create_nonce('leyka-edit-campaign-slug');?>" style="display: none;">
                <input type="text" class="leyka-slug-field inline-input" value="<?php echo $campaign_permalink_parts[1];?>">
                <span class="slug-submit-buttons">
                    <button class="inline-submit"><?php esc_html_e('OK');?></button>
                    <button class="inline-reset"><?php esc_html_e('Cancel');?></button>
                </span>
            </span>

            <?php } else {?>

                <span class="base-url"><?php echo $campaign_permalink_full;?></span>
                <a href="<?php echo admin_url('options-permalink.php');?>" class="permalink-action" target="_blank">Включить постоянные ссылки</a>

            <?php }?>

            <div class="edit-permalink-loading">
                <div class="loader-wrap">
                    <span class="leyka-loader xs"></span>
                </div>
            </div>

        </div>

        <?php return ob_get_clean();

    }
}

if( !function_exists('leyka_admin_get_shortcode_field') ) {
    function leyka_admin_get_shortcode_field($campaign) {

        $campaign = new Leyka_Campaign($campaign);
        if($campaign->id <= 0) {
            return '';
        }

        $shortcode = Leyka_Campaign_Management::get_campaign_form_shortcode($campaign->id);
        ob_start();?>

        <span class="leyka-current-value"><?php echo esc_attr($shortcode);?></span>
        <span class="leyka-campaign-shortcode-field" style="display: none;">
            <input type="text" class="embed-code read-only campaign-shortcode inline-input" id="campaign-shortcode" value="<?php echo esc_attr($shortcode);?>">
            <button class="inline-reset"><?php esc_html_e('Cancel');?></button>
        </span>

        <?php return ob_get_clean();

    }
}

if( !function_exists('leyka_sync_plugin_stats_option_action') ) {
    function leyka_sync_plugin_stats_option_action($old_value, $new_value) {

        if( !$old_value && $new_value === 'n' ) {
            return;
        }

        leyka_sync_plugin_stats_option();

    }
}
add_action('leyka_after_save_option-send_plugin_stats', 'leyka_sync_plugin_stats_option_action', 10, 2);

if( !function_exists('leyka_get_admin_footer') ) {
    function leyka_get_admin_footer($footer_class='', $old_footer_html='') {
        ob_start();
        ?>
        <div class="leyka-dashboard-footer leyka-admin-footer <?php echo $footer_class;?>">
            <a href="https://te-st.ru/" class="te-st-logo">
                <img  src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/logo-te-st-with-caption.svg" alt="<?php _e('te-st.ru', 'leyka');?>" />
            </a>
            <div class="links">
                <div class="te-st-link">
                    <span><?php _e('Created by', 'leyka');?></span>
                    <a href="https://te-st.ru/"><?php _e('Teplitsa. Technologies for Social Good', 'leyka');?></a>
                </div>
                <div class="info-links">
                    <a href="https://leyka.te-st.ru/sla/" target="_blank"><?php _e('SLA', 'leyka');?></a>
                    <a href="https://github.com/Teplitsa/leyka/wiki" target="_blank"><?php _e('Documentation', 'leyka');?></a>
                    <a href="https://t.me/joinchat/BshvgVUqHJLyCNIXd6pZXQ" target="_blank"><?php _e('Developer chat', 'leyka');?></a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean() . $old_footer_html;
    }

}

if( !function_exists('leyka_show_admin_footer') ) {
    function leyka_show_admin_footer($old_footer_html='') {
        $footer_class = '';
        if(!empty($_GET['screen']) && strpos($_GET['screen'], 'wizard-') === 0) {
            $footer_class .= 'leyka-wizard-footer';
        }
        elseif(!empty($_GET['page']) && $_GET['page'] === 'leyka_settings' && empty($_GET['screen'])) {
            $footer_class .= 'leyka-settings-footer';
        }

        echo leyka_get_admin_footer($footer_class, $old_footer_html);
    }
}

if( !function_exists('leyka_show_admin_footer_on_default_pages') ) {
    function leyka_show_admin_footer_on_default_pages($old_footer_html='') {
        $screen = get_current_screen();
        if(false === stripos($screen->base, 'leyka') && false === stripos($screen->id, 'leyka')) {
            return $old_footer_html;
        }
        elseif( !empty($_GET['post_type']) && in_array($_GET['post_type'], array('leyka_donation', 'leyka_campaign')) ) {
            return leyka_get_admin_footer('', $old_footer_html);
        }
    }
    add_filter( 'admin_footer_text', 'leyka_show_admin_footer_on_default_pages', 20 );
}

if( !function_exists('leyka_admin_body_class') ) {
    function leyka_admin_body_class($classes) {
        $leyka_page_class = '';

        if(!empty($_GET['screen']) && strpos($_GET['screen'], 'wizard-') === 0) {
            $leyka_page_class .= 'leyka-admin-wizard';
        }
        elseif(!empty($_GET['page']) && $_GET['page'] === 'leyka_settings' && empty($_GET['screen'])) {
            $leyka_page_class .= 'leyka-admin-settings';
        }
        elseif(!empty($_GET['page']) && $_GET['page'] === 'leyka' && empty($_GET['screen'])) {
            $leyka_page_class .= 'leyka-admin-dashboard';
        }
        elseif(!empty($_GET['page']) && $_GET['page'] === 'leyka_donors' && empty($_GET['screen'])) {
            $leyka_page_class .= 'leyka-admin-donors-list';
        }
        elseif( (!empty($_GET['post_type']) && in_array($_GET['post_type'], array('leyka_donation', 'leyka_campaign')))
            || (!empty($_GET['page']) && $_GET['page'] === 'leyka_feedback' && empty($_GET['screen']))) {
            $leyka_page_class .= 'leyka-admin-default';
        }

        return $classes . ' ' . $leyka_page_class . ' ';
    }
    add_filter( 'admin_body_class', 'leyka_admin_body_class', 20 );
}

if( !function_exists('leyka_admin_get_donor_comment_table_row') ) {
    function leyka_admin_get_donor_comment_table_row($comment_id, $comment) {
        
        ob_start();?>
        
        <tr class="comment-id-<?php echo $comment_id;?>">
            <td class="donor-comment-date"><?php echo date(get_option('date_format'), (int)$comment['date']);?></td>
            <td class="donor-comment-text">
            	<div class="leyka-editable-str-wrapper">
                	<div class="leyka-editable-str-result" 
                		id="editable-comment-str-result<?php echo $comment_id;?>" 
                		str-field="editable-comment-str-field<?php echo $comment_id;?>"
            		><?php echo esc_html($comment['text']);?></div>
                	<input class="leyka-editable-str-field" type="text" value="<?php echo esc_html($comment['text']);?>" style="display: none;" 
                		id="editable-comment-str-field<?php echo $comment_id;?>" 
                		str-btn="editable-comment-str-btn<?php echo $comment_id;?>" 
                		str-result="editable-comment-str-result<?php echo $comment_id;?>"
                		save-action="leyka_save_editable_comment"
                		text-item-id="<?php echo $comment_id;?>" 
            		/>
                    <div class="loading-indicator-wrap">
                        <div class="loader-wrap"><span class="leyka-loader xxs"></span></div>
                        <img class="ok-icon" src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/dashboard/icon-check.svg" alt="">
                    </div>
                </div>
            </td>
            <td class="donor-comment-author"><?php echo $comment['author_name'];?></td>
            <td class="donor-comment-edit">
                <a href="#" class="comment-icon-edit leyka-editable-str-btn" 
                	id="editable-comment-str-btn<?php echo $comment_id;?>" 
                	str-field="editable-comment-str-field<?php echo $comment_id;?>" 
                	data-comment-id="<?php echo $comment_id;?>" 
                	data-nonce="<?php echo wp_create_nonce('leyka_update_donor_comment');?>"
            	> </a>
            </td>
            <td class="donor-comment-delete">
                <a href="#" class="comment-icon-delete" data-comment-id="<?php echo $comment_id;?>"> </a>
                <div class="loading-indicator-wrap">
                    <div class="loader-wrap"><span class="leyka-loader xxs"></span></div>
                    <img class="ok-icon" src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/dashboard/icon-check.svg" alt="">
                </div>
            </td>
        </tr>
        
        <?php return ob_get_clean();

    }
}
