<?php
/**
 * The template for displaying leyka persistent campaign
 *
 * @link https://leyka.te-st.ru/campaign/demo-kampaniya/
 *
 * @package Leyka
 * @since 1.0.0
 */

?>
        
<form class="leyka-screen-form leyka-cancel-subscription-form">
    
    <h2><?php esc_html_e('We will be grateful if you share why you decided to cancel the subscription?', 'leyka');?></h2>
    
    <div class="limit-width">
        <div class="leyka-cancel-subscription-reason">
            <span>
                <input type="checkbox" name="leyka_cancel_subscription_reason" id="leyka_cancel_subscription_reason_uncomfortable_pm" class="required" value="uncomfortable_pm">
                <label for="leyka_cancel_subscription_reason_uncomfortable_pm">Неудобный способ оплаты</label>
            </span>
            <span>
                <input type="checkbox" name="leyka_cancel_subscription_reason" id="leyka_cancel_subscription_reason_too_much" class="required" value="too_much">
                <label for="leyka_cancel_subscription_reason_too_much">Слишком большая сумма пожертвования</label>
            </span>
            <span>
                <input type="checkbox" name="leyka_cancel_subscription_reason" id="leyka_cancel_subscription_reason_not_match" class="required" value="not_match">
                <label for="leyka_cancel_subscription_reason_not_match">Издание не отражает мои интересы</label>
            </span>
            <span>
                <input type="checkbox" name="leyka_cancel_subscription_reason" id="leyka_cancel_subscription_reason_better_use" class="required" value="better_use">
                <label for="leyka_cancel_subscription_reason_better_use">Нашел лучшее применение деньгам</label>
            </span>
            <span>
                <input type="checkbox" name="leyka_cancel_subscription_reason" id="leyka_cancel_subscription_reason_other" class="required" value="other">
                <label for="leyka_cancel_subscription_reason_other">Другая причина</label>
            </span>
        </div>
        
        <div class="section unsubscribe-comment">
            <div class="section__fields donor">
                <?php $field_id = 'leyka-'.wp_rand();?>
                <div class="donor__textfield donor__textfield--comment">
                    <div class="leyka-star-field-frame">
                        <label for="<?php echo $field_id;?>">
                            <span class="donor__textfield-label leyka_donor_comment-label"><?php echo __('Your reason', 'leyka');?></span>
                        </label>
                        <textarea id="<?php echo $field_id;?>" class="leyka-donor-comment" name="leyka_donor_comment"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="leyka-hidden-controls">
        	<input type="hidden" name="leyka_campaign_id" value="">
        	<?php wp_nonce_field( 'leyka_cancel_subscription' );?>
        </div>
        
        <div class="leyka-star-field-error-frame">
            <span class="donor__textfield-error choose-reason"><?php _e('Choose unsubscription reason, please', 'leyka');?></span>
            <span class="donor__textfield-error give-details"><?php _e('Give some details about your reason', 'leyka');?></span>
        </div>
        

        <div class="leyka-star-submit double">
            <a href="<?php echo site_url('/donor-account/');?>" class="leyka-star-btn leyka-do-not-unsubscribe"><?php esc_html_e('Do not unsubscribe', 'leyka');?></a>
            <input type="submit" name="unsubscribe" class="leyka-star-btn secondary last" value="<?php esc_html_e('Continue', 'leyka');?>">
        </div>
        
    </div>

</form>

<form class="leyka-screen-form leyka-confirm-unsubscribe-request-form">

    <h2><?php esc_html_e('Disable subscription?', 'leyka');?></h2>
    
    <div class="form-controls">
        <p><?php esc_html_e('We were able to do a lot with the help of your donations. Without your support, it will be harder for us to achieve results. It is a pity that you unsubscribe!', 'leyka');?></p>
        
        <div class="form-message"></div>
        
        <div class="leyka-star-submit double confirm-unsubscribe-submit">
            <a href="#" class="leyka-star-btn leyka-do-not-unsubscribe"><?php esc_html_e('Do not unsubscribe', 'leyka');?></a>
            <input type="submit" name="unsubscribe" class="leyka-star-btn secondary last" value="<?php esc_html_e('Disable subscription', 'leyka');?>">
        </div>
    </div>

    <div class="leyka-form-spinner">
    	<span class="leyka-spinner-border"></span>
    </div>

</form>

<form class="leyka-screen-form leyka-unsubscribe-request-accepted-form">

    <h2><?php esc_html_e('Your request to unsubscribe accepted', 'leyka');?></h2>
    
    <p><?php esc_html_e('The subscription will be disabled within 3 days', 'leyka');?></p>
    
    <div class="leyka-star-submit">
    	<a href="<?php echo site_url('/donor-account/');?>" class="leyka-star-single-link"><?php esc_html_e('To main' , 'leyka');?></a>
    </div>
        
</form>