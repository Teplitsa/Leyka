<?php
/**
 * The template for displaying leyka persistent campaign
 *
 * @link https://leyka.te-st.ru/campaign/demo-kampaniya/
 *
 * @package Leyka
 * @since 1.0.0
 */

include(LEYKA_PLUGIN_DIR . 'templates/account/header.php'); ?>

<div id="content" class="site-content leyka-campaign-content">
    
    <section id="primary" class="content-area">
        <main id="main" class="site-main">
            <div class="entry-content">

                <div id="leyka-pf-" class="leyka-pf leyka-pf-star">
                    <div class="leyka-account-form">
        
                        <form class="leyka-screen-form">
                            
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
                                            <div class="leyka-star-field-error-frame">
                                                <span class="donor__textfield-error leyka_donor_comment-error"><?php _e('Entered value is too long', 'leyka');?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="leyka-star-submit double">
                                    <a href="<?php echo site_url('/donor-account/');?>" class="leyka-star-btn"><?php esc_html_e('Do not unsubscribe', 'leyka');?></a>
                                    <input type="submit" name="unsubscribe" class="leyka-star-btn secondary last" value="<?php esc_html_e('Continue', 'leyka');?>">
                                </div>
                                
                            </div>
        
                        </form>

                    </div>
                </div>
                
            </div>

        </main>
    </section>

</div>

<?php get_footer(); ?>