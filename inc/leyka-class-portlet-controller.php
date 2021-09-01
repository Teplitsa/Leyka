<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlets Controller class.
 **/

abstract class Leyka_Portlet_Controller extends Leyka_Singleton {

    protected static $_instance;

    abstract function get_template_data(array $params = []);

}