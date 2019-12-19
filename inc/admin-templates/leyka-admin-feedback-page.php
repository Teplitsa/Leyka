<?php if( !defined('WPINC') ) die;
/** Admin Feedback page template */

/** @var $this Leyka_Admin_Setup */

$user = wp_get_current_user();?>

<div class="wrap leyka-admin leyka-feedback-page" data-leyka-admin-page-type="feedback-page">

    <h2><?php _e('Send us a feedback', 'leyka');?></h2>

    <div class="leyka-feedback-description">

        <p><?php _e('Found a bug? Need a feature?', 'leyka'); ?></p>
        <p><?php _e('Please, <a href="https://github.com/Teplitsa/Leyka/issues/new">create an issue on Github</a> or send us a message with the following form', 'leyka'); ?></p>

    </div>

    <div class="feedback-columns">

        <div class="leyka-feedback-form">

            <img id="feedback-loader" style="display: none;" src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/ajax-loader.gif';?>" alt="">

            <form id="feedback" action="#" method="post">

                <fieldset class="leyka-ff-field">
                    <label for="feedback-topic"><?php _e('Message topic:', 'leyka');?></label>
                    <input id="feedback-topic" name="topic" placeholder="<?php _e('For ex., Paypal support needed', 'leyka');?>" class="regular-text">
                    <div id="feedback-topic-error" class="leyka-ff-field-error" style="display: none;"></div>
                </fieldset>

                <fieldset class="leyka-ff-field">
                    <label for="feedback-name"><?php _e("Your name (we'll use it to address you only):", 'leyka');?></label>
                    <input id="feedback-name" name="name" placeholder="<?php _e('For ex., Leo', 'leyka');?>" value="<?php echo $user->display_name;?>" class="regular-text">
                    <div id="feedback-name-error" class="leyka-ff-field-error" style="display: none;"></div>
                </fieldset>

                <fieldset class="leyka-ff-field">
                    <label for="feedback-email"><?php _e('Your email:', 'leyka');?></label>
                    <input id="feedback-email" name="email" placeholder="<?php _e('your@mailbox.com', 'leyka');?>" value="<?php echo $user->user_email;?>" class="regular-text">
                    <div id="feedback-email-error" class="leyka-ff-field-error" style="display: none;"></div>
                </fieldset>

                <fieldset class="leyka-ff-field">
                    <label for="feedback-text"><?php _e('Your message:', 'leyka');?></label>
                    <textarea id="feedback-text" name="text" class="regular-text"></textarea>
                    <div id="feedback-text-error" class="leyka-ff-field-error" style="display: none;" ></div>
                </fieldset>

                <fieldset class="leyka-ff-field leyka-submit">
                    <input type="hidden" id="nonce" value="<?php echo wp_create_nonce('leyka_feedback_sending');?>">
                    <input type="submit" class="button-primary" value="<?php _e('Submit');?>">
                </fieldset>

            </form>

            <div id="message-ok" class="leyka-ff-msg ok" style="display: none;">
                <p><?php _e('<strong>Thank you!</strong> Your message sended successfully. We will answer it soon - please await our response on the email you entered.', 'leyka');?></p>
            </div>

            <div id="message-error" class="leyka-ff-msg wrong" style="display: none;">
                <p><?php _e("Sorry, but the message can't be sended. Please check your mail server settings.", 'leyka');?></p>
            </div>

        </div>

        <div class="feedback-sidebar"></div>

    </div>

</div>