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
                    <div class="leyka-payment-form leyka-tpl-star-form">
        
                        <?php if(empty($_GET['activate'])) {?>
        
                        <form class="leyka-screen-form">
        
                            <h2><?php _e('Personal account login', 'leyka');?></h2>
        
                            <div class="section">
        
                                <div class="section__fields donor">
        
                                    <?php $field_id = 'leyka-'.wp_rand();?>
                                    <div class="donor__textfield donor__textfield--email required">
                                        <div class="leyka-star-field-frame">
                                            <label for="<?php echo $field_id;?>">
                                                <span class="donor__textfield-label leyka_donor_name-label">
                                                    <?php _e('Your email', 'leyka');?>
                                                </span>
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
                                                <span class="donor__textfield-label leyka_donor_name-label">
                                                    <?php _e('Your password', 'leyka');?>
                                                </span>
                                            </label>
                                            <input id="<?php echo $field_id;?>" type="password" name="leyka_donor_name" value="" autocomplete="off">
                                        </div>
                                        <div class="leyka-star-field-error-frame">
                                            <span class="donor__textfield-error leyka_donor_name-error">
                                                <?php _e('Enter your password' , 'leyka');?>
                                            </span>
                                        </div>
                                    </div>
        
                                </div>
                            </div>
                            
                            <div class="leyka-extra-links">
                                <a href="#"><?php esc_html_e('Reset password', 'leyka');?></a>
                            </div>
        
                            <div class="leyka-star-submit">
                                <a href="#" class="leyka-star-btn"><?php _e('Log in' , 'leyka');?></a>
                            </div>
        
                        </form>
        
                        <?php } else { // Account activation/password setting ?>
        
                        <form class="leyka-screen-form">
        
                            <h2><?php _e('Set up your password', 'leyka');?></h2>
        
                            <div class="section">
        
                                <div class="section__fields donor">
        
                                    <?php $field_id = 'leyka-'.wp_rand();?>
                                    <div class="donor__textfield donor__textfield--name required">
                                        <div class="leyka-star-field-frame">
                                            <label for="<?php echo $field_id;?>">
                                                <span class="donor__textfield-label leyka_donor_name-label">
                                                    <?php _e('Your password', 'leyka');?>
                                                </span>
                                            </label>
                                            <input id="<?php echo $field_id;?>" type="password" name="leyka_donor_name" value="" autocomplete="off">
                                        </div>
                                        <div class="leyka-star-field-error-frame">
                                            <span class="donor__textfield-error leyka_donor_name-error">
                                                <?php _e('Enter your password', 'leyka');?>
                                            </span>
                                        </div>
                                    </div>
        
                                    <?php $field_id = 'leyka-'.wp_rand();?>
                                    <div class="donor__textfield donor__textfield--name required">
                                        <div class="leyka-star-field-frame">
                                            <label for="<?php echo $field_id;?>">
                                                <span class="donor__textfield-label leyka_donor_name-label">
                                                    <?php _e('Repeat your password', 'leyka');?>
                                                </span>
                                            </label>
                                            <input id="<?php echo $field_id;?>" type="password" name="leyka_donor_name" value="" autocomplete="off">
                                        </div>
                                        <div class="leyka-star-field-error-frame">
                                            <span class="donor__textfield-error leyka_donor_name-error">
                                                <?php _e('Enter your password', 'leyka');?>
                                            </span>
                                        </div>
                                    </div>
        
                                </div>
                            </div>
        
                            <div class="leyka-star-submit">
                                <a href="#" class="leyka-star-btn"><?php _e('Set up the password', 'leyka');?></a>
                            </div>
        
                        </form>
        
                        <?php }?>
        
                    </div>
                </div>
                
            </div>

        </main>
    </section>

</div>

<?php get_footer(); ?>