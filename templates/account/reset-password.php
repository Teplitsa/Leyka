<?php if( !defined('WPINC') ) die;
/**
 * The template for displaying donor's account password reset page.
 *
 * @package Leyka
 * @since 1.0.0
 */

include(LEYKA_PLUGIN_DIR.'templates/account/header.php'); ?>

<div id="content" class="site-content leyka-campaign-content">
    
    <section id="primary" class="content-area">
        <main id="main" class="site-main">
            <div class="entry-content">

                <div id="leyka-pf-" class="leyka-pf leyka-pf-star">
                    <div class="leyka-account-form">

                        <?php if(empty($_GET['code']) || empty($_GET['donor'])) { // 1-st reset step form (email confirmation ?>

						<form class="leyka-screen-form leyka-reset-password" method="post" action="#">

							<h2><?php _e('Account password reset', 'leyka');?></h2>

							<div class="section section--person">
						
								<div class="section__fields donor">
					
									<?php $field_id = 'leyka-'.wp_rand();?>
									<div class="donor__textfield donor__textfield--email required">
										<div class="leyka-star-field-frame">
											<label for="<?php echo $field_id;?>">
												<span class="donor__textfield-label leyka_donor_email-label">
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

								</div>
							</div>

                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('leyka_donor_password_reset');?>">

                            <div class="leyka-form-spinner">
                            	<?php echo leyka_get_ajax_indicator();?>
                            </div>

                            <div class="form-message" style="display: none;"></div>

							<div class="leyka-star-submit password-reset-submit">
                                <input type="submit" class="leyka-star-btn" value="<?php _e('Reset the password' , 'leyka');?>">
							</div>

						</form>

                        <?php } else { // 2-nd reset step form - Account password resetting ?>

                            <form class="leyka-screen-form leyka-account-pass-setup" action="<?php echo home_url('/donor-account/login/');?>" method="post">

                                <h2><?php _e('Set up your new password', 'leyka');?></h2>

                                <div class="section">

                                    <?php $_GET['code'] = esc_sql($_GET['code']);
                                    $_GET['donor'] = esc_sql($_GET['donor']);

                                    $donor_account = get_user_by('email', $_GET['donor']);

                                    if(
                                        !$donor_account
                                        || !$donor_account->user_login
                                        || !is_a(check_password_reset_key($_GET['code'], $donor_account->user_login), 'WP_User')
                                    ) {?>

                                        <div class="section__fields error">

                                            <div class="error-message">
                                                <?php _e("No account found to reset it's password :( Try to log in.", 'leyka');?>
                                            </div>

                                            <div class="leyka-star-submit">
                                                <a href="<?php echo home_url('/donor-account/login/');?>" class="leyka-star-btn">
                                                    <?php _e('Log in', 'leyka');?>
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

                                        <?php echo leyka_get_ajax_indicator();?>

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