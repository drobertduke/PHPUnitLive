<?php
/**
 */
class User_Test
{
    public function test_a_thing()
    {
        PHPUnitScribe_Interceptor::instrument_files_once(array('../test/sut_script.php'));
        PHPUnitScribe_Interceptor::instrument_classes_once(array('test_SUTUser'));
    }

}
