<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlet: Main donation types stats
 * Description: A portlet to display simple statistics for main donation types (single & recurring).
 *
 * Title: Main statistics
 * Thumbnail: /img/stats-donation-types.svg
 **/

$data = Leyka_Donations_Main_Stats_Portlet_Controller::get_instance()->get_template_data($params);
echo '<pre>'.print_r($params, 1).'</pre>';
echo '<pre>'.print_r($data, 1).'</pre>';