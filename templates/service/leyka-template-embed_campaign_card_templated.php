<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Embed Campaign Templated Card
 **/

$campaign = new Leyka_Campaign(get_post());

//if($campaign->template == 'revo') {
//	include('leyka-template-embed_campaign_card_templated_revo.php');
//}
//else {
	include('leyka-template-embed_campaign_card.php');
//}
