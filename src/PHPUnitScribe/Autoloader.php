<?php
class PHPUnitScribe_Autoloader
{
    public static function register_test_autoloader()
    {
        $autoloaders = spl_autoload_functions();
        foreach ($autoloaders as $autoloader)
        {
            if (count($autoloader) === 2 &&
                $autoloader[0] === 'PHPUnitScribe_Autoloader' &&
                $autoloader[1] === 'shadow_autoloader')
            {
                return;
            }
        }
        spl_autoload_register(array('PHPUnitScribe_Autoloader', 'shadow_autoloader'));
    }

    public static function shadow_autoloader($class_name)
    {
        $class_parts = explode('/', $class_name);
        if (count($class_parts) > 1 && $class_parts[0] === 'PHPUnitScribe_Shadow')
        {
            // Get the original class name to find the original file
            array_shift($class_parts);
            $original_class = implode('/', $class_parts);
            $reflection = new ReflectionClass($original_class);
            $original_file = $reflection->getFileName();
            PHPUnitScribe_Instrumentor::include_file($original_file);
        }

    }


}
