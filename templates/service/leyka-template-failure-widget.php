<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Failed donation page block.
 * Description: A template for the interactive actions block shown on the failed donation page.
 **/

$donation_id = leyka_remembered_data('donation_id');
$campaign = null;
$campaign_id = null;

if($donation_id) {

    $donation = Leyka_Donations::get_instance()->get_donation($donation_id);
    $campaign_id = $donation ? $donation->campaign_id : null;
    $campaign = new Leyka_Campaign($campaign_id);

}?>

<div id="leyka-pf-" class="leyka-pf">
    <?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo leyka_get_svg( LEYKA_PLUGIN_DIR . 'assets/svg/svg.svg' );
    ?>

    <div class="leyka-pf__final-screen leyka-pf__final-error">

        <svg class="svg-icon icon"><use xlink:href="#pic-red-cross"></svg>
        <div class="text"><div class="leyka-js-error-text"><?php esc_html_e('Payment error', 'leyka');?></div></div>
        <div class="error-text"><div><?php esc_html_e('Perhaps there are problems in the Internet connection, in the operation of the payment system or an internal system error. The money will return to your account.', 'leyka');?></div></div>

        <div class="error-text"><div>
            <?php
            $support_email = leyka_get_website_tech_support_email();
            $campaign_url  = $campaign ? $campaign->url : home_url('/');

            if ( $support_email ) {
                echo wp_kses_post( sprintf(
                    /* translators: 1: Campaign url, 2: Support email. */
                    __('We\'ve received the error report and are working to fix it. Please try to <a href="%1$s" class="leyka-js-try-again">donate again</a>. If the error continues to occur, please use another payment method or <a href="mailto:%2$s">contact our technical support</a>.', 'leyka'),
                    $campaign_url,
                    $support_email
                ) );
            } else {
                echo wp_kses_post(sprintf(
                    /* translators: %s: Campaign url. */
                    __('We\'ve received the error report and are working to fix it. Please try to <a href="%s" class="leyka-js-try-again">donate again</a>. If the error continues to occur, please use another payment method or try donating again later.', 'leyka'),
                    $campaign_url
                ));
           }?></div></div>
        <div class="leyka-logo"> </div>

    </div>

</div>
