<?php
/**
 * A REPL-like interface for writing/editing a test
 */
class PHPUnitScribe_TestEditor
{
    protected $test_builder;
    public function __construct($test_file, $test_function)
    {
        $this->test_builder = new PHPUnitScribe_TestBuilder($test_file, $test_function);
    }
    protected function setup_phpunit()
    {
        echo "Setting up phpunit\n";
    }

    protected function load_test()
    {
        $file_dependencies = $this->test_builder->get_file_names();
        $class_dependencies = $this->test_builder->get_class_names();
        PHPUnitScribe_Interceptor::instrument_files_once($file_dependencies);
        PHPUnitScribe_Interceptor::instrument_classes_once($class_dependencies);

        $choices = $this->test_builder->get_mocking_choices();
        PHPUnitScribe_Interceptor::register_mocking_choices($choices);
    }

    protected function read()
    {
        echo "reading statement\n";
        return new PHPParser_Node_Expr_Exit();
    }

    public function execute($should_fast_forward = false)
    {
        PHPUnitScribe_Interceptor::register_editor($this);
        $this->setup_phpunit();
        $this->load_test();
        $statement_container = $this->test_builder->get_statement_container();
        // If we're fast-forwarding, we run all the statements
        // previously recorded
        if ($should_fast_forward)
        {
            $statement_container->execute();
        }
        else
        {
            foreach ($statement_container->get_statements() as $statement)
            {
                $this->prompt_for_existing_statement($statement);
            }
        }

        $readline = null;
        PHPUnitScribe_Interceptor::set_interactive_mode(true);
        while ($readline != 'exit;')
        {
            $readline = trim(fgets(STDIN));
            $statement_container = new PHPUnitScribe_Statements($readline);
            $statement_container->execute();
        }
    }

    protected function prompt_for_existing_statement($statement)
    {
        $printer = new PHPParser_PrettyPrinter_Default();
        $printed = $printer->prettyPrint(array($statement));
        echo "prompting for existing statement $printed\n";
        echo "(R)un as previously defined\n";
        echo "Run and (E)dit execution\n";
        echo "(S)top running original statements\n";
        $cmd = trim(fgets(STDIN));
        if ($cmd === 'r')
        {
            PHPUnitScribe_Interceptor::set_interactive_mode(false);
            $statement_container = new PHPUnitScribe_Statements($statement);
            $statement_container->execute();
        }
        else if ($cmd === 'e')
        {
            PHPUnitScribe_Interceptor::set_interactive_mode(true);
            $statement_container = new PHPUnitScribe_Statements($statement);
            $statement_container->execute();
        }
        else if ($cmd === 's')
        {
            PHPUnitScribe_Interceptor::set_interactive_mode(false);
        }
        else
        {
            echo "not a valid command\n";
        }
    }

    public function prompt_for_mock($statement)
    {
        $printer = new PHPParser_PrettyPrinter_Default();
        $printed = $printer->prettyPrint(array($statement));
        echo "prompting to mock $printed\n";
        echo "(R)un with no mocking\n";
        echo "(M)ock this statement to return a value\n";
        echo "(I)nteractively step into this statement\n";
        echo "Return from function (p)rematurely\n";
        $cmd = trim(fgets(STDIN));
        if ($cmd === 'r')
        {
            return PHPUnitScribe_MockingChoice_Over;
        }
        else if ($cmd === 'm')
        {
            return PHPUnitScribe_MockingChoice_Replace;
        }
        else if ($cmd === 'i')
        {
            return PHPUnitScribe_MockingChoice_Into;
        }
        else if ($cmd === 'p')
        {
            return PHPUnitScribe_MockingChoice_PrematureReturn;
        }
    }



}
