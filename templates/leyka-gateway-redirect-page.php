<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo __('Redirecting to the gateway payment page', 'leyka');?></title>
    <style type="text/css">
        body {
            margin: 0;
            padding: 40px 30px;
            background: #fff;
            color: #444;
            font: normal 14px / 1.4 Arial, Helvetica, sans-serif;
        }
        h3 {
            font-size: 2em;
            margin-bottom: 0.8em;
            background: url(<?php echo LEYKA_PLUGIN_BASE_URL;?>img/ajax-loader-h.gif) no-repeat right center;
                   
        }
        div,
        form {
            margin: 0 auto;
            max-width: 300px;
        }
        
        #leyka-copy {
            margin-top: 5px;            
            font-size: 11px;
            line-height: 18px;
            padding: 4px 30px 4px 4px; 	
            border: 1px solid #cbe5b5;
            background: #eef6e8;
            color: #666;
        }
        
        #leyka-copy p {
            margin: 0;
        }
        
        #leyka-copy a,
        #leyka-copy a:hover {
            color:  #1DB318;
        }
    </style>
</head>

<body>
    <div>
        <?php echo apply_filters('leyka_gateway_redirect_message', __('<h3>Thank you!</h3><p>In a few seconds you will be redirected to the payment system website, where you can complete your donation.</p>', 'leyka'));?>
    </div>

    <form id='leyka-auto-submit' action='<?php echo leyka()->payment_url;?>' method='post'>

        <?php
        foreach(leyka()->payment_vars as $name => $value) {
            echo "<input type='hidden' name='$name' value='$value' />";
        }?>
        
        <noscript>
            <div><?php echo __('If you are not redirected to the payment page automatically, please press this button', 'leyka');?></div>

            <input type="submit" name="leyka-gateway-submit" value="<?php echo __('Proceed to the payment approval page', 'leyka');?>" />
        </noscript>

    </form>
    <?php leyka_pf_footer();?>
    <script type="text/javascript">
        setTimeout(function(){ document.getElementById('leyka-auto-submit').submit(); }, <?php echo WP_DEBUG ? 15000 : 5000;?>);
    </script>
</body>
</html>