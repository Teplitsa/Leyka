<?php
/**
 * The template for displaying leyka persistent campaign
 *
 * @link https://leyka.te-st.ru/campaign/demo-kampaniya/
 *
 * @package Leyka
 * @since 1.0.0
 */

$recurring_subscriptions = leyka_get_init_recurring_donations();

include(LEYKA_PLUGIN_DIR . 'templates/account/header.php'); ?>

<div id="content" class="site-content leyka-campaign-content">
    
    <section id="primary" class="content-area">
        <main id="main" class="site-main">
            <div class="entry-content">

                <div id="leyka-pf-" class="leyka-pf leyka-pf-star">
                    <div class="leyka-account-form">
        
                        <form class="leyka-screen-form">
                            
                            <?php if($recurring_subscriptions) {?>
                            
                            <h2><?php esc_html_e('Which campaign you want to unsubscibe from?', 'leyka');?></h2>
                            
                            <div class="list">
                                <div class="items">
                                	<?php foreach($recurring_subscriptions as $init_donation) {?>
                                    <div class="item">
                                        <span class="campaign-title"><?php echo $init_donation->campaign_payment_title;?></span>
                                        <a href="#" class="action-disconnect"><?php esc_html_e('Disable');?></a>
                                    </div>
                                	<?php } ?>
                                </div>
                            </div>
                            
                            <?php } else {?>
                            
                            <h2><?php esc_html_e('You have no active recurring subscriptions.', 'leyka');?></h2>
                            
                            <?php } ?>
        
                            <div class="leyka-star-submit">
                                <a href="<?php echo site_url('/donor-account/');?>" class="leyka-star-single-link"><?php esc_html_e('To main' , 'leyka');?></a>
                            </div>
        
                        </form>

                    </div>
                </div>
                
            </div>

        </main>
    </section>

</div>

<?php get_footer(); ?>