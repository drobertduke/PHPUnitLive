<?php
/**
 * A REPL-like interface for writing/editing a test
 */
class PHPUnitScribe_TestEditor
{
    /** @var \PHPUnitScribe_TestBuilder */
    protected $test_builder;
    /** @var PHPParser_Parser */
    protected $parser;
    /** @var \PHPParser_PrettyPrinter_Default */
    protected $printer;
    protected $statements;

    public function __construct($test_file, $test_function)
    {
        $this->test_file = new PHPUnitScribe_TestFile($test_file, $test_function);
        $this->parser = new PHPParser_Parser(new PHPParser_Lexer());
        $this->printer = new PHPParser_PrettyPrinter_Default();
    }

    protected function run_test_setup()
    {
        echo "Running the shadow test's setup\n";
    }

    protected function generate_shadow()
    {
        $stmts = $this->get_test_statements();
        // Not sure where I left off here
        //$this->parser->parse()
    }

    protected function get_test_function_name()
    {
        return $this->test_builder->get_function_name();
    }

    protected function get_test_statements()
    {
        return $this->test_builder->get_statements();
    }

    protected function load_test()
    {
        $this->shadow_test = $this->generate_shadow();
        $file_dependencies = $this->test_builder->get_file_names();
        $class_dependencies = $this->test_builder->get_class_names();
        PHPUnitScribe_Interceptor::instrument_files_once($file_dependencies);
        PHPUnitScribe_Interceptor::instrument_classes_once($class_dependencies);

        $choices = $this->test_builder->get_interceptions();
        PHPUnitScribe_Interceptor::setup_with_interceptions($choices);
    }

    protected function read()
    {
        echo "reading statement\n";
        return new PHPParser_Node_Expr_Exit();
    }

    public function execute($should_fast_forward)
    {
        // This function encloses the user's scope
        // Protect against name collisions
        PHPUnitScribe_Interceptor::register_editor($this);
        $this->load_test();
        $statement_container = $this->test_builder->get_statement_container();
        $existing_statements = $statement_container->get_statements();
        $printer = new PHPParser_PrettyPrinter_Default();
        $existing_code = $printer->prettyPrint($existing_statements);
        eval($existing_code);
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

        PHPUnitScribe_Interceptor::set_interactive_mode(true);
        echo "Prompt!>";
        $readline = trim(fgets(STDIN));
        $parser = new PHPParser_Parser(new PHPParser_Lexer());
        $statements = $parser->parse('<?php ' . $readline);
        $statement_container = new PHPUnitScribe_Statements($statements);
        $statement_container->execute();
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

    public function prompt_for_interception($printed_statement, $allow_assignment)
    {
        $printer = new PHPParser_PrettyPrinter_Default();
        echo "prompting\n";
        echo "prompting to intercept $printed_statement\n";
        echo "Step (O)ver\n";
        echo "Step i(N)to\n";
        if ($allow_assignment)
        {
            echo "(I)ntercept this statement to return a value\n";
        }
        echo "(R)eturn from function prematurely\n";
        $cmd = readline('>> ');
        if ($cmd === 'o')
        {
            return array(PHPUnitScribe_InterceptionChoice_Over, null);
        }
        else if ($allow_assignment && $cmd === 'i')
        {
            $replacement_text_raw = readline("Replace with: ");
            $return_value = "return $replacement_text_raw";
            if (substr($return_value, -1) != ';')
            {
                $return_value .= ';';
            }
            $replacement = eval($return_value);
            echo "dumping replacement\n";
            var_dump($replacement);
            return array(PHPUnitScribe_InterceptionChoice_Replace, $replacement);
        }
        else if ($cmd === 'n')
        {
            return array(PHPUnitScribe_InterceptionChoice_Into, null);
        }
        else if ($cmd === 'r')
        {
            $return_value_raw = readline("Return value:");
            $return_value = "return $return_value_raw";
            if (substr($return_value, -1) != ';')
            {
                $return_value .= ';';
            }
            $return = eval($return_value);
            return array(PHPUnitScribe_InterceptionChoice_PrematureReturn, $return);
        }
    }



}
