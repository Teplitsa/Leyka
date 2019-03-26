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

						<form class="leyka-screen-form">
							
							<h2>Восстановление пароля</h2>
							
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
					
								</div>
							</div>
						
							<div class="leyka-star-submit">
								<a href="#" class="leyka-star-btn">Прислать новый пароль</a>
							</div>
							
						</form>

                    </div>
                </div>
                
            </div>

        </main>
    </section>

</div>

<?php get_footer(); ?>