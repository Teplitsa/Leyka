<?php if( !defined('WPINC') ) die;

/** Admin helpchat widget markup. */

$current_user = wp_get_current_user();?>

<div id="admin-support-widget">

    <a class="help-chat-button" href="#"><img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-help-chat.svg" alt=""></a>

    <div class="help-chat fix-height">

        <div class="chat-header">
            <div class="title"><?php _e('Feedback form', 'leyka');?></div>
            <img class="close" src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-help-close.svg" alt="">
        </div>

        <div class="chat-body">

            <div class="leyka-loader md"></div>

            <div class="ok-message">
                <p><?php _e('Your message sent. We will try to answer in one day.', 'leyka');?></p>
                <p><?php _e('Thank you!', 'leyka');?></p>
            </div>

            <form action="#" class="form">

                <?php wp_nonce_field('leyka_feedback_sending', 'leyka_feedback_sending_nonce');?>

                <div class="settings-block option-block">

                    <div>
                        <label for="leyka-help-chat-name">
                            <span class="field-component title"><?php _e('Your name', 'leyka');?></span>
                            <span class="field-component field">
                                <input type="text" id="leyka-help-chat-name" value="<?php echo $current_user->display_name;?>" maxlength="255" required="required">
                            </span>
                        </label>
                    </div>

                    <div class="field-errors"><?php _e('This field is required', 'leyka');?></div>

                </div>

                <div class="settings-block option-block">

                    <div>
                        <label for="leyka-help-chat-email">
                            <span class="field-component title"><?php _e('Your email', 'leyka');?></span>
                            <span class="field-component field">
                                <input type="email" id="leyka-help-chat-email" value="<?php echo get_option('admin_email');?>" maxlength="255" required="required">
                            </span>
                        </label>
                    </div>

                    <div class="field-errors"><?php _e('This field is required', 'leyka');?></div>

                </div>

                <div class="settings-block option-block">

                    <div>
                        <label for="leyka-help-chat-message">
                            <span class="field-component title">
                                <?php _e('Describe the problem', 'leyka');?>
                            </span>
                            <span class="field-component field">
                                <textarea id="leyka-help-chat-message" required="required"></textarea>
                            </span>
                        </label>
                    </div>

                    <div class="field-errors"><?php _e('This field is required', 'leyka');?></div>

                </div>

                <input type="submit" class="button button-primary" value="<?php _e('Submit', 'leyka');?>">

            </form>

        </div>

    </div>

</div>