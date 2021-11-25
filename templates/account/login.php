<?php if( !defined('WPINC') ) die;
/**
 * The template for displaying donor's account login page.
 *
 * @package Leyka
 * @since 1.0.0
 */

include(LEYKA_PLUGIN_DIR.'templates/account/header.php'); ?>

<div id="content" class="site-content leyka-campaign-content">
    
    <section id="primary" class="content-area">
        <main id="main" class="site-main">
            <div class="entry-content">

                <div id="leyka-pf-" class="leyka-pf leyka-pf-star leyka-login">
                    <div class="leyka-account-form">
        
                        <?php if(empty($_GET['activate'])) { // Normal login ?>
        
                        <form class="leyka-screen-form leyka-account-login" action="<?php echo home_url('/donor-account/login/');?>" method="post">

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
                                    <div class="donor__textfield donor__textfield--pass required">
                                        <div class="leyka-star-field-frame">
                                            <label for="<?php echo $field_id;?>">
                                                <span class="donor__textfield-label leyka_donor_pass-label">
                                                    <?php _e('Your password', 'leyka');?>
                                                </span>
                                            </label>
                                            <input id="<?php echo $field_id;?>" type="password" name="leyka_donor_pass" value="" autocomplete="off">
                                        </div>
                                        <div class="leyka-star-field-error-frame">
                                            <span class="donor__textfield-error leyka_donor_pass-error">
                                                <?php _e('Enter your password' , 'leyka');?>
                                            </span>
                                        </div>
                                    </div>

                                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('leyka_donor_login');?>">

                                </div>

                                <div class="leyka-form-spinner">
                                	<?php echo leyka_get_ajax_indicator();?>
                                </div>

                                <div class="form-message" style="display: none;"></div>

                            </div>
                            
                            <div class="leyka-extra-links">
                                <a href="<?php echo home_url('/donor-account/reset-password/');?>">
                                    <?php esc_html_e('Reset password', 'leyka');?>
                                </a>
                            </div>
        
                            <div class="leyka-star-submit login-submit">
                                <input type="submit" class="leyka-star-btn" value="<?php _e('Log in' , 'leyka');?>">
                            </div>
        
                        </form>

                        <?php } else { // Account activation/password setting ?>

                        <form class="leyka-screen-form leyka-account-pass-setup" action="<?php echo home_url('/donor-account/login/');?>" method="post" data-account-activation="1">

                            <h2><?php _e('Set up your password', 'leyka');?></h2>
        
                            <div class="section">

                                <?php $_GET['activate'] = esc_sql($_GET['activate']);
                                $donor_account = get_users(['meta_query' => [[
                                    'key' => 'leyka_account_activation_code',
                                    'value' => $_GET['activate'],
                                    'compare' => '=',
                                ]]]);

                                if( !$donor_account) {?>

                                    <div class="section__fields error">

                                        <div class="error-message">
                                            <?php _e('No such account to activate :( Try to log in.', 'leyka');?>
                                        </div>

                                        <div class="leyka-star-submit">
                                            <a href="<?php echo home_url('/donor-account/login/');?>" class="leyka-star-btn">
                                                <?php _e('Log in', 'leyka');?>
                                            </a>
                                        </div>

                                    </div>

                                <?php } else if(count($donor_account) > 1) {?>

                                    <div class="section__fields error">

                                        <div class="error-message">
                                            <?php _e('The account search has ambiguous results %) Please, tell about that to our tech. support.', 'leyka');?>
                                        </div>

                                        <div class="leyka-star-submit">
                                            <a href="mailto:<?php echo leyka_get_website_tech_support_email();?>" target="_blank" class="leyka-star-btn">
                                                <?php _e('Email to the tech. support', 'leyka');?>
                                            </a>
                                        </div>

                                    </div>

                                <?php } else { // Password setup form

                                    $donor_account = reset($donor_account);?>

                                <div class="section__fields donor">

                                    <?php $field_id = 'leyka-'.wp_rand();?>
                                    <div class="donor__textfield donor__textfield--pass required">
                                        <div class="leyka-star-field-frame">
                                            <label for="<?php echo $field_id;?>">
                                                <span class="donor__textfield-label leyka_donor_pass-label">
                                                    <?php _e('Your password', 'leyka');?>
                                                </span>
                                            </label>
                                            <input id="<?php echo $field_id;?>" type="password" name="leyka_donor_pass" value="" autocomplete="off">
                                        </div>
                                        <div class="leyka-star-field-error-frame">
                                            <span class="donor__textfield-error leyka_donor_pass-error"></span>
                                        </div>
                                    </div>

                                    <?php $field_id = 'leyka-'.wp_rand();?>
                                    <div class="donor__textfield donor__textfield--pass2 required">
                                        <div class="leyka-star-field-frame">
                                            <label for="<?php echo $field_id;?>">
                                                <span class="donor__textfield-label leyka_donor_pass2-label">
                                                    <?php _e('Repeat your password', 'leyka');?>
                                                </span>
                                            </label>
                                            <input id="<?php echo $field_id;?>" type="password" name="leyka_donor_pass2" value="" autocomplete="off">
                                        </div>
                                        <div class="leyka-star-field-error-frame">
                                            <span class="donor__textfield-error leyka_donor_pass2-error"></span>
                                        </div>
                                    </div>

                                    <input type="hidden" name="donor_account_id" value="<?php echo $donor_account->ID;?>">
                                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('leyka_account_password_setup');?>">

                                </div>

                                <div class="leyka-form-spinner">
                                	<?php echo leyka_get_ajax_indicator();?>
                                </div>

                                <div class="form-message" style="display: none;"></div>

                                <div class="leyka-star-submit activation-submit">
                                    <input type="submit" class="leyka-star-btn" value="<?php _e('Set up the password', 'leyka');?>">
                                </div>

                                <?php }?>

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