<html>
<head>
    <title><?php echo __('Redirecting to the gateway payment page', 'leyka');?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <?php wp_head();?>
</head>

<body>
    <div>

        <?php echo apply_filters('leyka_gateway_redirect_message', __('<h3>Thank you!</h3><p>In a few seconds you will be redirected to the payment system website, where you can complete your donation.</p>', 'leyka'));

        $pm = leyka_pf_get_payment_method_value();

        do_action('leyka_'.$pm['gateway_id'].'_redirect_page_content', $pm['payment_method_id'], leyka()->donation_id);?>

    </div>

    <form id="leyka-auto-submit" action="<?php echo leyka()->payment_url;?>" method="post">

        <?php foreach((array)leyka()->payment_vars as $name => $value) {
            echo '<input type="hidden" name="'.$name.'" value="'.$value.'">'."\n";
        }?>

        <noscript>
            <div>
                <?php _e("If you weren't redirected to the payment page automatically, please press this button", 'leyka');?>
            </div>
            <input type="submit" name="leyka-gateway-submit" value="<?php echo __('Proceed to the payment approval page', 'leyka');?>">
        </noscript>

    </form>
    <?php leyka_pf_footer();

    if(leyka()->redirect_type === 'auto') {?>

        <script type="text/javascript">
            setTimeout(function(){ document.getElementById('leyka-auto-submit').submit(); }, <?php echo leyka_options()->opt('plugin_debug_mode') ? 10000 : 5000;?>);
        </script>

    <?php } else if(leyka()->redirect_type === 'redirect') {?>

        <script type="text/javascript">
            setTimeout(function(){ window.location.href = document.getElementById('leyka-auto-submit').action; }, <?php echo leyka_options()->opt('plugin_debug_mode') ? 10000 : 5000;?>);
        </script>

    <?php } else if(leyka()->redirect_type === 'redirect') {?>

        <script type="text/javascript">
            setTimeout(function(){ window.location.href = document.getElementById('leyka-auto-submit').action; }, <?php echo leyka_options()->opt('plugin_debug_mode') ? 10000 : 5000;?>);
        </script>

    <?php }

    wp_footer();?>
</body>
</html>