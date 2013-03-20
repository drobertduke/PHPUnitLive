<?php
/**
 * Contains all the information needed to write out a test.
 * Includes the list of files/classes that need instrumentation,
 * the mocking instructions, and the testing statements and
 * asserts
 */
class PHPUnitScribe_TestBuilder
{
    protected $name;
    protected $file_names = array();
    protected $class_names = array();
    protected $mocking_choices = array();
    protected $statements = array();

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function add_class($class_name)
    {
        if (!in_array($class_name, $this->class_names))
        {
            $this->class_names[] = $class_name;
        }
    }

    public function add_file($file_name)
    {
        if (!in_array($file_name, $this->file_names))
        {
            $this->file_names[] = $file_name;
        }
    }

    public function add_mocking_choice($choice)
    {
        $this->mocking_choices[] = $choice;
    }

    public function add_statement($statement)
    {
        $this->statements[] = $statement;
    }

    public function get_file_names()
    {
        return $this->file_names;
    }

    public function get_class_names()
    {
        return $this->class_names;
    }

    public function get_mocking_choices()
    {
        return $this->mocking_choices;
    }

    public function get_statements()
    {
        return $this->statements;
    }
}
