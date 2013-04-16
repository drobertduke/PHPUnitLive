<?php
/**
 * The user's interface and test execution environment
 */
class PHPUnitScribe_UserEnvironment
{
    /** @var PHPUnitScribe_TestEditor */
    protected $test_editor;
    /** @var string */
    protected $test_method_name;
    protected $carryover_statments;
    /** @var \PHPUnitScribe_TestFile */
    protected $test_file;
    public function __construct($test_file_name, $test_method_name, $carryover_statements)
    {
        $this->test_file = new PHPUnitScribe_TestFile($test_file_name);
        $this->test_method_name = $test_method_name;
        //$this->test_editor = new PHPUnitScribe_TestEditor($test_file, $test_function);
        $this->carryover_statements = $carryover_statements;
    }

    protected function get_test_method()
    {
        echo "TEST METHODS\n";
        foreach ($this->test_file->get_test_classes() as $test_class)
        {
            echo "class {$test_class->get_name()}\n";
            foreach ($test_class->get_test_methods() as $test_method)
            {
                echo "method {$test_method->get_name()}\n";
                /** @var $test_method PHPUnitScribe_TestMethod */
                if ($test_method->get_name() === $this->test_method_name)
                {
                    return $test_method;
                }
            }
        }
        throw new Exception("No test method matching the name {$this->test_method_name} was found in " .
                            $this->test_file->name());
    }

    public function start()
    {
        $this->setup_phpunit();
        $this->get_test_method()->instrument_executed_files();

    }

    protected function setup_phpunit()
    {
        echo "Setting up phpunit\n";
    }


}
