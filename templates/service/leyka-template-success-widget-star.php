<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Successful donation page block.
 * Description: A template for the interactive actions block shown on the successful donation page.
 **/

if( !leyka_options()->opt_template('show_success_widget_on_success') ) {
    return;
}

$donation_id = leyka_remembered_data('donation_id');

if( !$donation_id ) {
    return;
}?>

<div id="leyka-pf-" class="leyka-pf leyka-pf-star">

    <div class="leyka-pf__final-screen leyka-pf__final-thankyou">

        <form class="leyka-screen-form leyka-screen-thankyou">
            
            <h2><?php esc_html_e('Thank you for your donation!', 'leyka');?></h2>
            
            <p><?php esc_html_e('We will be happy with a small but monthly assistance, this gives us confidence in the future and the opportunity to plan our activities.', 'leyka');?></p>
        
            <div class="leyka-star-submit">
                <a href="<?php echo home_url();?>" class="leyka-star-btn"><?php esc_html_e('To main', 'leyka');?></a>
            </div>
            
        </form>

    </div>
    
</div>