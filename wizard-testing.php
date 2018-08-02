<?php
require_once '../../../wp-load.php';

Leyka_Wizard_Render::get_instance()
    ->setController(Leyka_Init_Wizard_Settings_Controller::get_instance())
    ->renderPage();

echo '<pre>'.print_r('Current step: '.Leyka_Init_Wizard_Settings_Controller::get_instance()->current_step->id, 1).'</pre>';
echo '<pre>'.print_r('Next step: '.Leyka_Init_Wizard_Settings_Controller::get_instance()->next_step_full_id, 1).'</pre>';

//echo '<pre>'.print_r(Leyka_Init_Wizard_Settings_Controller::get_instance()->current_step, 1).'</pre>';