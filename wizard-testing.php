<?php
require_once '../../../wp-load.php';

Leyka_Wizard_Render::get_instance()
    ->setController(Leyka_Init_Wizard_Settings_Controller::get_instance())
    ->renderPage();