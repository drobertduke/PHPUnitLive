<?php

class SUT_Autoloader {
    public static function autoload($class)
    {
        $root = dirname(__FILE__);
        $class_parts = explode("_", $class);
        $path = $root;
        foreach ($class_parts as $class_part)
        {
            $path .= '/' . $class_part;
        }
        $path .= '.php';
        if (is_readable($path))
        {
            include_once($path);
        }
    }
}
spl_autoload_extensions('.php');
spl_autoload_register('SUT_Autoloader::autoload');
