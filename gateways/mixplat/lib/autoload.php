<?php

define('MIXPLAT_CLIENT_PATH', dirname(__FILE__));

function mixplatLoadClass($className)
{
    $basedClass = str_replace(array('MixplatClient\\', '\\'), array('', '/'), $className);
    $path = MIXPLAT_CLIENT_PATH . DIRECTORY_SEPARATOR . $basedClass . '.php';

    if (file_exists($path)) {
        require $path;
    }
}

spl_autoload_register('mixplatLoadClass');
