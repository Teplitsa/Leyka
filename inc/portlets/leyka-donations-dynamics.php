<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlet: Donations dynamics
 * Description: A portlet to display donations dynamics.
 *
 * Title: Donations dynamics
 * Thumbnail: /img/icon-bar-chartie.svg
 **/

$data = Leyka_Donations_Dynamics_Portlet_Controller::get_instance()->get_template_data($params);?>

<div class="dynamics-bar-chart">
<!--    --><?php //echo '<pre>'.print_r($data, 1).'</pre>';?>
</div>