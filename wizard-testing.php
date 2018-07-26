<?php
require_once '../../../wp-load.php';

Leyka_Init_Wizard::get_instance()
    ->setRender(Leyka_Wizard_Render::get_instance())
    ->display();