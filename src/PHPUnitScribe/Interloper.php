<?php
class PHPUnitScribe_Interloper
{
    public static function route(callable $original_call)
    {
       echo "prompting for decision \n";
    }

}
