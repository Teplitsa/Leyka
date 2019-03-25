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

<div id="leyka-pf-<?php echo $campaign_id;?>" class="leyka-pf leyka-pf-star" data-form-id="leyka-pf-<?php echo $campaign_id;?>-star-form">
<div class="leyka-payment-form leyka-tpl-star-form" data-template="star">

	<?php if(empty($_GET['set-password'])) { ?>

    <form class="leyka-screen-form leyka-screen-thankyou">
        
        <h2>Войти в личный кабинет</h2>
        
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
                            <span class="donor__textfield-label leyka_donor_name-label">Пароль</span>
                        </label>
                        <input id="<?php echo $field_id;?>" type="password" name="leyka_donor_name" value="" autocomplete="off">
                    </div>
                    <div class="leyka-star-field-error-frame">
                        <span class="donor__textfield-error leyka_donor_name-error">
                            Введите пароль
                        </span>
                    </div>
                </div>
                
            </div>
        </div>
    
        <div class="leyka-star-submit">
            <a href="#" class="leyka-star-btn">Войти</a>
        </div>
        
    </form>
	
	<?php } else { ?>
		

    <form class="leyka-screen-form">
        
        <h2>Установите пароль</h2>
        
        <!-- donor data -->
        <div class="section section--person">
    
            <div class="section__fields donor">

                <?php $field_id = 'leyka-'.wp_rand();?>
                <div class="donor__textfield donor__textfield--name required">
                    <div class="leyka-star-field-frame">
                        <label for="<?php echo $field_id;?>">
                            <span class="donor__textfield-label leyka_donor_name-label">Пароль</span>
                        </label>
                        <input id="<?php echo $field_id;?>" type="password" name="leyka_donor_name" value="" autocomplete="off">
                    </div>
                    <div class="leyka-star-field-error-frame">
                        <span class="donor__textfield-error leyka_donor_name-error">
                            Введите пароль
                        </span>
                    </div>
                </div>
                
                <?php $field_id = 'leyka-'.wp_rand();?>
                <div class="donor__textfield donor__textfield--name required">
                    <div class="leyka-star-field-frame">
                        <label for="<?php echo $field_id;?>">
                            <span class="donor__textfield-label leyka_donor_name-label">Повторите пароль</span>
                        </label>
                        <input id="<?php echo $field_id;?>" type="password" name="leyka_donor_name" value="" autocomplete="off">
                    </div>
                    <div class="leyka-star-field-error-frame">
                        <span class="donor__textfield-error leyka_donor_name-error">
                            Введите пароль
                        </span>
                    </div>
                </div>

            </div>
        </div>
    
        <div class="leyka-star-submit">
            <a href="#" class="leyka-star-btn">Установить</a>
        </div>
        
    </form>
		
	<?php } ?>

</div>
</div>
            
		</main><!-- #main -->
	</section><!-- #primary -->

	</div><!-- #content -->

<?php get_footer(); ?>