<html>
<head>
    <title><?php echo __('Redirecting to the gateway payment page', 'leyka');?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <?php wp_head();?>
</head>

<body>
    <div>
        <?php echo apply_filters('leyka_gateway_redirect_message', __('<h3>Thank you!</h3><p>In a few seconds you will be redirected to the payment system website, where you can complete your donation.</p>', 'leyka'));

        $gateway_pm = explode('-', $_POST['leyka_payment_method']);

        do_action('leyka_'.$gateway_pm[0].'_redirect_page_content', $gateway_pm[1], leyka()->donation_id);?>
    </div>

    <form id="leyka-auto-submit" action="<?php echo leyka()->payment_url;?>" method="get">

        <?php foreach(leyka()->payment_vars as $name => $value) {
            echo '<input type="hidden" name="'.$name.'" value="'.$value.'">'."\n";
        }?>

        <noscript>
            <div><?php _e("If you weren't not redirected to the payment page automatically, please press this button", 'leyka');?></div>

            <input type="submit" name="leyka-gateway-submit" value="<?php echo __('Proceed to the payment approval page', 'leyka');?>">
        </noscript>

    </form>
    <?php leyka_pf_footer();

    if(leyka()->auto_redirect) {?>

    <script type="text/javascript">
        setTimeout(function(){ document.getElementById('leyka-auto-submit').submit(); }, <?php echo WP_DEBUG ? 10000 : 5000;?>);
    </script>

    <?php }

    wp_footer();?>
</body>
</html>