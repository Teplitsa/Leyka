<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Star
 * Description: A modern and lightweight form template
 * Debug only: true
 * 
 * $campaign - current campaign
 * 
 **/

$template_data = Leyka_Star_Template_Controller::getInstance()->getTemplateData($campaign);
$leyka_screen = !empty($_GET['leyka-screen']) ? $_GET['leyka-screen'] : '';
?>

<?php if($leyka_screen == 'cancel-subscription') { ?>
<form class="leyka-screen-form">
        
        <h2>Мы были бы вам благодарны, если вы поделитесь почему вы решили отменить подписку?</h2>
        
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
    
        <div class="leyka-star-submit">
            <input type="submit" class="leyka-star-btn" value="Отключить">
            <input type="submit" class="leyka-star-btn btn-secondary" value="Не отключать">
        </div>
        
    </form>
    
<?php } elseif($leyka_screen == 'thankyou') { ?>

    <form class="leyka-screen-form">
        
        <h2>Спасибо за ваше пожертвование!</h2>
        
        <p>Мы будем рады небольшой, но ежемесячной помощи, это дает нам уверенность в завтрашнем дне и возможность планировать нашу деятельность.</p>
    
        <div class="leyka-star-submit">
            <a href="#" class="leyka-star-btn">На главную</a>
        </div>
        
    </form>
    
<?php } elseif($leyka_screen == 'history') { ?>

    <form class="leyka-screen-form">
        
        <h2>История пожертвований</h2>
        
        <p>Мы благодарны вам за оказываемую поддержку!</p>
        
        <div class="leyka-star-history">
            <div class="item break">
                <h2>Отключение</h2>
                <span class="date">12.01.2019</span>
                <p>«Помогите изданию оставаться независимым источником информации»</p>
            </div>
            <div class="item no-pay">
                <h2>300 Р.</h2>
                <span class="date">12.01.2019</span>
                <p>«Помогите изданию оставаться независимым источником информации»</p>
            </div>
            <div class="item error">
                <h2>300 Р.</h2>
                <span class="date">12.01.2019</span>
                <p>«Помогите изданию оставаться независимым источником информации»</p>
            </div>
            <div class="item pay">
                <h2>300 Р.</h2>
                <span class="date">12.01.2019</span>
                <p>«Помогите изданию оставаться независимым источником информации»</p>
            </div>
            <div class="item break">
                <h2>Отключение</h2>
                <span class="date">12.01.2019</span>
                <p>«Помогите изданию оставаться независимым источником информации»</p>
            </div>
        </div>
    
        <div class="leyka-star-submit">
            <a href="#" class="leyka-star-btn">Загрузить еще</a>
        </div>
        
        <p class="leyka-we-need-you">Вы всегда можете <a href="?leyka-screen=cancel-subscription">отключить ваше ежемесячное пожертвование.</a><br />Но нам будет без вас трудно.</p>
        
    </form>

<?php } else { ?>
<div id="leyka-pf-<?php echo $campaign->id;?>" class="leyka-pf leyka-pf-star" data-form-id="leyka-pf-<?php echo $campaign->id;?>-star-form">
    
<div class="leyka-inline-campaign-form leyka-payment-form leyka-tpl-star-form" data-template="star">
    
    <form id="<?php echo leyka_pf_get_form_id($campaign->id).'-star-form';?>" class="leyka-pm-form" action="<?php echo Leyka_Payment_Form::get_form_action();?>" method="post" novalidate="novalidate">
    
        <div class="section section--periodicity">
        
            <?php if(leyka_is_recurring_supported()) {?>
                <div class="section__fields periodicity">
                    <a href="#" class="active" data-periodicity="monthly">Ежемесячно</a>
                    <a href="#" class="" data-periodicity="once">Разово</a>
                </div>
            <?php }?>
            
        </div>
    
    
        <div class="section section--amount">
            
            <div class="section__fields amount">
    
            <?php echo Leyka_Payment_Form::get_common_hidden_fields($campaign, array(
                'leyka_template_id' => 'star',
                'leyka_amount_field_type' => 'custom',
            ));
    
            $form_api = new Leyka_Payment_Form();
            echo $form_api->get_hidden_amount_fields();?>
    
                <div class="amount__figure star-swiper">
                    <div class="arrow-gradient left"></div><a class="swiper-arrow swipe-left" href="#"></a>
                    <div class="arrow-gradient right"></div><a class="swiper-arrow swipe-right" href="#"></a>
                    
                    <div class="swiper-list">
                    
                        <?php foreach($template_data['amount_variants'] as $i => $amount) {?>
                            <div class="swiper-item <?php echo $i ? "" : "selected";?>" data-value="<?php echo (int)$amount;?>"><span class="amount"><?php echo (int)$amount;?></span><span class="currency"><?php echo $template_data['currency_label'];?></span></div>
                        <?php }?>
        
                        <?php if($template_data['amount_mode'] != 'fixed') {?>
                            <div class="swiper-item flex-amount-item">
                                <label for="leyka-flex-amount">
                                    <span class="textfield-label">Другая сумма, <span class="currency"><?php echo $template_data['currency_label'];?></span></span>
                                </label>
                                <input type="number" title="Введите вашу сумму" placeholder="Введите вашу сумму" data-desktop-ph="Другая сумма" data-mobile-ph="Введите вашу сумму" name="donate_amount_flex" class="donate_amount_flex" value="<?php echo esc_attr($template_data['amount_default']);?>" min="1" max="999999">
                            </div>
                        <?php }?>
                    </div>
                    <input type="hidden" class="leyka_donation_amount" name="leyka_donation_amount" value="">
                </div>
                
                <input type="hidden" class="leyka_donation_currency" name="leyka_donation_currency" data-currency-label="<?php echo $template_data['currency_label'];?>" value="<?php echo leyka_options()->opt('main_currency');?>">
                <input type="hidden" name="leyka_recurring" class="is-recurring-chosen" value="0">
            </div>
    
        </div>
        
    
        <div class="section section--cards">
    
            <div class="section__fields payments-grid">
                <div class="star-swiper">
                    <div class="arrow-gradient left"></div><a class="swiper-arrow swipe-left" href="#"></a>
                    <div class="arrow-gradient right"></div><a class="swiper-arrow swipe-right" href="#"></a>
                    <div class="swiper-list">
    
                    <?php foreach($template_data['pm_list'] as $number => $pm) { /** @var $pm Leyka_Payment_Method */?>
            
                        <div class="payment-opt swiper-item <?php echo $number ? "" : "selected";?>">
                            <label class="payment-opt__button">
                                <input class="payment-opt__radio" name="leyka_payment_method" value="<?php echo esc_attr($pm->full_id);?>" type="radio" data-processing="<?php echo $pm->processing_type;?>" data-has-recurring="<?php echo $pm->has_recurring_support() ? '1' : '0';?>" data-ajax-without-form-submission="<?php echo $pm->ajax_without_form_submission ? '1' : '0';?>">
                                <span class="payment-opt__icon">
                                    <?php foreach($pm->icons ? $pm->icons : array($pm->main_icon_url) as $icon_url) {?>
                                        <img class="pm-icon" src="<?php echo $icon_url;?>" alt="">
                                    <?php }?>
                                </span>
                            </label>
                            <span class="payment-opt__label"><?php echo $pm->label;?></span>
                        </div>
                    <?php }?>
            
                    </div>
                </div>
            </div>
    
        </div>
    
    
        <?php foreach($template_data['pm_list'] as $pm) { /** @var $pm Leyka_Payment_Method */
    
            if($pm->processing_type != 'static') {
                continue;
            }?>
            
        <div class="section section--static <?php echo $pm->full_id;?>">
    
            <div class="section__fields static-text">
                <?php $pm->display_static_data();?>

                <div class="static__complete-donation">
                    <input class="leyka-js-complete-donation" value="<?php echo leyka_options()->opt_safe('revo_donation_complete_button_text');?>">
                </div>

            </div>
    
        </div>
    
        <?php }?>
    
    
        <!-- donor data -->
        <div class="section section--person">
    
            <div class="section__fields donor">

                <?php $field_id = 'leyka-'.wp_rand();?>
                <div class="donor__textfield donor__textfield--email required">
                    <div class="leyka-star-field-frame">
                        <label for="<?php echo $field_id;?>">
                            <span class="donor__textfield-label leyka_donor_name-label"><?php _e('Your email', 'leyka');?></span>
                        </label>
                        <input type="email" id="<?php echo $field_id;?>" name="leyka_donor_email" value="" autocomplete="off">
                    </div>
                    <div class="leyka-star-field-error-frame">
                        <span class="donor__textfield-error leyka_donor_email-error">
                            <?php _e('Enter an email in the some@email.com format', 'leyka');?>
                        </span>
                    </div>
                </div>

                <?php $field_id = 'leyka-'.wp_rand();?>
                <div class="donor__textfield donor__textfield--name required">
                    <div class="leyka-star-field-frame">
                        <label for="<?php echo $field_id;?>">
                            <span class="donor__textfield-label leyka_donor_name-label">Имя и фамилия</span>
                        </label>
                        <input id="<?php echo $field_id;?>" type="text" name="leyka_donor_name" value="" autocomplete="off">
                    </div>
                    <div class="leyka-star-field-error-frame">
                        <span class="donor__textfield-error leyka_donor_name-error">
                            <?php _e('Enter your name', 'leyka');?>
                        </span>
                    </div>
                </div>

                <?php if(leyka_options()->opt_template('show_donation_comment_field')) { $field_id = 'leyka-'.wp_rand();?>
                <div class="donor__textfield donor__textfield--comment leyka-field">
                    <div class="leyka-star-field-frame">
                        <label for="<?php echo $field_id;?>">
                            <span class="donor__textfield-label leyka_donor_comment-label"><?php echo leyka_options()->opt_template('donation_comment_max_length') ? sprintf(__('Your comment (<span class="donation-comment-current-length">0</span> / <span class="donation-comment-max-length">%d</span> symbols)', 'leyka'), leyka_options()->opt_template('donation_comment_max_length')) : __('Your comment', 'leyka');?></span>
                        </label>
                        <textarea id="<?php echo $field_id;?>" class="leyka-donor-comment" name="leyka_donor_comment" data-max-length="<?php echo leyka_options()->opt_template('donation_comment_max_length');?>"></textarea>
                    </div>
                    <div class="leyka-star-field-error-frame">
                        <span class="donor__textfield-error leyka_donor_comment-error"><?php _e('Entered value is too long', 'leyka');?></span>
                    </div>
                </div>
                <?php }?>

                <?php if(leyka_options()->opt('agree_to_terms_needed') || leyka_options()->opt('agree_to_pd_terms_needed')) {?>
                <div class="donor__oferta">
                    <span>
                    <?php if(leyka_options()->opt('agree_to_terms_needed')) {?>
                        <input type="checkbox" name="leyka_agree" id="leyka_agree" class="required" value="1" <?php echo leyka_options()->opt('terms_agreed_by_default') ? 'checked="checked"' : '';?>>
                        <label for="leyka_agree">
                        <?php echo apply_filters('agree_to_terms_text_text_part', leyka_options()->opt('agree_to_terms_text_text_part')).' ';

                        if(leyka_options()->opt('agree_to_terms_link_action') === 'popup') {?>
                            <a href="#" class="leyka-js-oferta-trigger">
                        <?php } else {?>
                            <a target="_blank" href="<?php echo leyka_get_terms_of_service_page_url();?>">
                        <?php }?>
                                <?php echo apply_filters('agree_to_terms_text_link_part', leyka_options()->opt('agree_to_terms_text_link_part'));?>
                            </a>
                        </label>
                    <?php if(leyka_options()->opt('agree_to_pd_terms_needed')) {?>

                        <input type="checkbox" name="leyka_agree_pd" id="leyka_agree_pd" class="required" value="1" <?php echo leyka_options()->opt('pd_terms_agreed_by_default') ? 'checked="checked"' : '';?>>
                        <label for="leyka_agree_pd">
                        <?php echo apply_filters('agree_to_pd_terms_text_text_part', leyka_options()->opt('agree_to_pd_terms_text_text_part')).' ';?>
                            <a href="#" class="leyka-js-pd-trigger">
                                <?php echo apply_filters('agree_to_pd_terms_text_link_part', leyka_options()->opt('agree_to_pd_terms_text_link_part'));?>
                            </a>
                        </label>

                    <?php }?>
                    </span>
                    <div class="donor__oferta-error leyka_agree-error leyka_agree_pd-error">
                        <?php _e('You should accept Terms of Service to donate', 'leyka');?>
                    </div>
                    <?php }?>
                </div>
                <?php }?>

                <div class="donor__submit">
                    <?php echo apply_filters('leyka_revo_template_final_submit', '<input type="submit" disabled="disabled" class="leyka-default-submit" value="'.leyka_options()->opt_template('donation_submit_text').'">');?>
                </div>

            </div>
                
        </div>
    </form>

    <div class="leyka-pf__overlay"></div>
    <?php if(leyka_options()->opt('agree_to_terms_needed')) {?>
    <div class="leyka-pf__agreement oferta">
        <div class="agreement__frame">
            <div class="agreement__flow">
                <?php echo apply_filters('leyka_terms_of_service_text', do_shortcode(leyka_options()->opt('terms_of_service_text')));?>
            </div>
        </div>
        <a href="#" class="agreement__close">
            <?php echo leyka_options()->opt('leyka_agree_to_terms_text_text_part').' '.leyka_options()->opt('leyka_agree_to_terms_text_link_part');?>
        </a>
    </div>
    <?php }?>

    <?php if(leyka_options()->opt('agree_to_pd_terms_needed')) {?>
    <div class="leyka-pf__agreement pd">
        <div class="agreement__frame">
            <div class="agreement__flow">
                <?php echo apply_filters('leyka_terms_of_pd_usage_text', do_shortcode(leyka_options()->opt('pd_terms_text')));?>
            </div>
        </div>
        <a href="#" class="agreement__close">
            <?php echo leyka_options()->opt('agree_to_pd_terms_text_text_part').' '.leyka_options()->opt('agree_to_pd_terms_text_link_part');?>
        </a>
    </div>
    <?php }?>

</div>

</div>

<?php } ?>