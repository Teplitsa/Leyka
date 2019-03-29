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
                            
                            <h2><?php esc_html_e('Which campaign you want to unsubscibe from?', 'leyka');?></h2>
                            
                            <div class="list">
                                <div class="items">
                                    <div class="item">
                                        <span class="campaign-title">Помогите изданию оставаться независимым источником информации</span>
                                        <a href="#" class="action-disconnect">Отключить</a>
                                    </div>
                                    <div class="item">
                                        <span class="campaign-title">На погашение штрафа от Роскомнадзора</span>
                                        <a href="#" class="action-disconnect">Отключить</a>
                                    </div>
                                    <div class="item">
                                        <span class="campaign-title">Поможем Григорию переехать</span>
                                        <a href="#" class="action-disconnect">Отключить</a>
                                    </div>
                                </div>
                            </div>
        
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