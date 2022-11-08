<?php

/**
 * autoload class
 */


spl_autoload_register(function ($classname) {
    $baseDir = dirname(dirname(__FILE__));
    $classname = str_replace("\\", "/", $classname);
    $addClassfile =  $baseDir . DIRECTORY_SEPARATOR . $classname . '.php';

    if (is_file($addClassfile)) {
        include_once $addClassfile;
        return true;
    }
});

include_once dirname(__FILE__) . "/./Helper.php";