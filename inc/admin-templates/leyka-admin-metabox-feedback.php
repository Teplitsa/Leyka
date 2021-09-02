<?php if( !defined('WPINC') ) die;
/** Admin Feedback metabox content */

/** @var $this Leyka_Admin_Setup */

$user = wp_get_current_user();?>

<div class="metabox-content leyka-feedback-form leyka-options-section no-background" data-thumbnail="/img/admin-boxes/feedback.svg">

    <img id="feedback-loader" style="display: none;" src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/ajax-loader.gif';?>" alt="">

    <form id="feedback" action="#" method="post">

        <?php leyka_render_text_field('feedback_name', [
            'title' => __('Your name (we are going to use it only to address you)', 'leyka'),
            'required' => true,
            'value' => $user->display_name,
            'placeholder' => __('For ex., Leo', 'leyka'),
        ]);?>
        <div id="leyka_feedback_name-field-error" class="error-message" style="display: none;"></div>

        <?php leyka_render_email_field('feedback_email', [
            'title' => __('Your email', 'leyka'),
            'required' => true,
            'value' => $user->user_email,
            'placeholder' => __('your@mailbox.com', 'leyka'),
        ]);?>
        <div id="leyka_feedback_email-field-error" class="error-message" style="display: none;"></div>

        <?php leyka_render_textarea_field('feedback_text', [
            'title' => __('Describe a question', 'leyka'),
            'required' => true,
        ]);?>
        <div id="leyka_feedback_text-field-error" class="error-message" style="display: none;"></div>

        <input type="hidden" name="leyka_feedback_topic" value="<?php _e('A message from Leyka help admin page', 'leyka');?>">

        <p class="leyka-submit">
            <input type="hidden" id="nonce" value="<?php echo wp_create_nonce('leyka_feedback_sending');?>">
            <input type="submit" class="button-primary" value="<?php _e('Submit');?>">
        </p>

    </form>

    <div id="message-ok" class="leyka-ff-msg ok" style="display: none;">
        <p><?php _e('<strong>Thank you!</strong> Your message sended successfully. We will answer it soon - please await our response on the email you entered.', 'leyka');?></p>
    </div>

    <div id="message-error" class="leyka-ff-msg wrong" style="display: none;">
        <p><?php _e("Sorry, but the message can't be sended. Please check your mail server settings.", 'leyka');?></p>
    </div>

</div>