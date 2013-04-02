<?php
/**
 * Contains all the information needed to write out a test.
 * Includes the list of files/classes that need instrumentation,
 * the mocking instructions, and the testing statements and
 * asserts
 */
class PHPUnitScribe_TestBuilder
{
    protected $test_file;
    protected $test_function;
    protected $instrumented_file_names = array();
    protected $instrumented_class_names = array();
    protected $mocking_choices = array();
    /** @var PHPUnitScribe_Statements */
    protected $statement_container;

    public function __construct($test_file, $test_function)
    {
        $this->test_file = $test_file;
        $this->test_function = $test_function;

        $functions_in_file = $this->get_functions();
        $statements = array();
        if (array_key_exists($test_function, $functions_in_file))
        {
            $statements = $functions_in_file[$test_function];
        }

        $this->statement_container = new PHPUnitScribe_Statements($statements);
    }

    private function get_functions()
    {
        $contents = file_get_contents($this->test_file);
        $functions = array();
        $parser = new PHPParser_Parser(new PHPParser_Lexer());
        $stmts = $parser->parse($contents);
        foreach($stmts as $top_level_stmt)
        {
            if ($top_level_stmt instanceof PHPParser_Node_Stmt_Class)
            {
                foreach($top_level_stmt->stmts as $class_stmt)
                {
                    if ($class_stmt instanceof PHPParser_Node_Stmt_ClassMethod)
                    {
                        $function_name = $class_stmt->name;
                        if (array_key_exists($function_name, $functions))
                        {
                            throw new Exception("Multiple methods with the same name " .
                                " ($function_name) exist in the file {$this->test_file}");
                        }
                        $functions[$function_name] = $class_stmt->stmts;
                    }
                }

            }
        }
        return $functions;
    }

    public function add_class($class_name)
    {
        if (!in_array($class_name, $this->instrumented_class_names))
        {
            $this->instrumented_class_names[] = $class_name;
        }
    }

    public function add_file($file_name)
    {
        if (!in_array($file_name, $this->instrumented_file_names))
        {
            $this->instrumented_file_names[] = $file_name;
        }
    }

    public function add_mocking_choice($choice)
    {
        $this->mocking_choices[] = $choice;
    }

    public function add_statement($statement)
    {
        $this->statement_container->add_statement($statement);
    }

    public function get_file_names()
    {
        return $this->instrumented_file_names;
    }

    public function get_class_names()
    {
        return $this->instrumented_class_names;
    }

    public function get_mocking_choices()
    {
        return $this->mocking_choices;
    }

    public function get_statement_container()
    {
        return $this->statement_container;
    }
}
