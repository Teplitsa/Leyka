<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Embed Campaign Card
 * Description: A template for an embed campaign cards. On a main website, normally, it is not in use.
 **/

$campaign = new Leyka_Campaign(get_post());?>

<!DOCTYPE html>
<html class="embed" <?php language_attributes(); ?> style="margin-top: 0px !important;" lang="ru">

<head>

    <title>Пример формы для пожертвований</title>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <?php wp_head(); ?>

</head>
<body style="background-color: transparent;">
    <div id="embedded-card">
        <?php 
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo leyka_shortcode_campaign_card(['campaign_id' => $campaign->id,]);
        ?>
    </div>
</body>

</html>