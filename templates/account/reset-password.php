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

						<form class="leyka-screen-form leyka-reset-password" method="post" action="#">
							
							<h2>Восстановление пароля</h2>

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

                            <div class="form-ajax-indicator" style="display: none;">
                                <div class="loading">
                                    <div class="spinner">
                                        <div class="bounce1"></div>
                                        <div class="bounce2"></div>
                                        <div class="bounce3"></div>
                                    </div>
                                </div>
                                <div class="waiting__card-text"><?php _e('Preparing to reset your password...', 'leyka');?></div>
                            </div>

                            <div class="form-message" style="display: none;"></div>

							<div class="leyka-star-submit password-reset-submit">
                                <input type="submit" class="leyka-star-btn" value="<?php _e('Reset the password' , 'leyka');?>">
							</div>

						</form>

                    </div>
                </div>
                
            </div>

        </main>
    </section>

</div>

<?php get_footer(); ?>