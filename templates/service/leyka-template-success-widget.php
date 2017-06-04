<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Successful donation page block.
 * Description: A template for the interactive actions block shown on the successful donation page.
 **/
?>

<?php $donation_id = leyka_remembered_data('donation_id');
echo '<pre>' . print_r($_COOKIE, 1) . '</pre>';?>

<form action="#" class="leyka-success-form" method="post" novalidate="novalidate" <?php echo empty($donation_id) ? 'style="display: none;"' : '';?>>

    <input type="hidden" name="leyka_donation_id" value="<?php echo $donation_id;?>">
    <input type="hidden" name="action" value="leyka_donor_subscription">
    <?php wp_create_nonce('leyka_donor_subscription');?>

<?php if(leyka_options()->opt('revo_template_ask_donor_data') == 'success-page') {?>

    <h3><?php _e('Want us to keep you posted about the results?', 'leyka');?></h3>

    <p><input type="text" name="leyka_donor_name" value="" placeholder="<?php _e('Your name', 'leyka');?>"></p>
    <p><input type="text" name="leyka_donor_email" value="" placeholder="<?php _e('Your email', 'leyka');?>"></p>

<?php } else if(leyka_options()->opt('show_subscription_on_success')) {?>

    <h3><?php _e('Want us to keep you posted about the results?', 'leyka');?></h3>

    <p><input type="text" name="leyka_donor_email" value="<?php echo leyka_remembered_data('donor_email');?>" placeholder="Ваш <?php _e('Your email', 'leyka');?>"></p>

<?php }?>

    <input type="submit" class="leyka-success-submit" name="leyka_success_submit" value="<?php _e('I want to know about the results', 'leyka');?>">
    <br>
    <a href="#"><?php _e('No, thank you', 'leyka');?></a>

<!-- Original prototype markup -->
<!--<div id="thankyou" class="step thankyou inactive">-->
<!--    <div class="step__form thankyou_form">-->
<!--        <div class="thankyou__icon">-->
<!--            <svg class="svg-icon pic-ok"><use xlink:href="#pic-ok"/></svg>-->
<!--        </div>-->
<!--        <div class="thankyou__text">-->
<!--            <p class="subscribe-ok-hide">Спасибо! Вы очень нам помогли! Давайте оставаться на связи?</p>-->
<!--            <div class="subscribe-ok">-->
<!--                <p>Мы отправим письмо с результатами на указанную почту, спасибо!</p>-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="thankyou__field subscribe-ok-hide">-->
<!--            <input type="text" name="person_email2" value="" id="person_email2">-->
<!--            <div class="text-field-validation">-->
<!--                <span class="valid"><svg class="svg-icon icon-valid"><use xlink:href="#icon-valid"/></svg></span>-->
<!--                <span class="invalid"><svg class="svg-icon icon-invalid"><use xlink:href="#icon-invalid"/></svg></span>-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="thankyou_btn subscribe-ok-hide">-->
<!--            <button class="thanks">Да, держите меня в&nbsp;курсе</button>-->
<!--        </div>-->
<!--        <div class="step__note subscribe-ok-hide">-->
<!--            <p><a href="#finalstep" class="another-step">Нет, спасибо</a> </p>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
<!-- Original prototype markup END -->

</form>