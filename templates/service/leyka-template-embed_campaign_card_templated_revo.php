<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Embed Campaign Card
 * Description: A template for an embed campaign cards. On a main website, normally, it is not in use.
 **/

$campaign = new Leyka_Campaign(get_post());?>

<!DOCTYPE html>
<html class="embed" <?php language_attributes(); ?> style="margin-top: 0px !important;">
<head>
	
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	
	<?php wp_head(); ?>

</head>
<body>
    <div id="embedded-card">
    <?php echo leyka_inline_campaign(array(
			'id' => $campaign->id,
			'template' => $campaign->template,
            'show_thumbnail' => leyka_options()->opt('revo_template_show_thumbnail'),
        ));
	?>
    </div>
</body>

</html>