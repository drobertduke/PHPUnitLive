<?php
/**
 *
 */
include_once 'PHPUnit/Autoload.php';
class Decomposer_Test extends PHPUnit_Framework_TestCase
{
    public function test_decomposer()
    {
        $code = file_get_contents(__DIR__ . '/data/Decomposer_Test_Class.php');
        $parser = new PHPParser_Parser(new PHPParser_Lexer());
        $statements = $parser->parse($code);
        $statement_container = new PHPUnitScribe_Statements($statements);
        $instrumented_statements = $statement_container->get_instrumented_statements();

        $printer = new PHPParser_PrettyPrinter_Default();
        //var_dump($instrumented_statements->get_statements());
        $code = $instrumented_statements->get_code();
        echo $code;
        //var_dump($printer->prettyPrint(array($statements)));
    }

    public function test_interception()
    {
        $code = file_get_contents(__DIR__ . '/data/Decomposer_Test_Class.php');
        $parser = new PHPParser_Parser(new PHPParser_Lexer());
        $statements = $parser->parse($code);
        $statement_container = new PHPUnitScribe_Statements($statements);
        $instrumented_statements = $statement_container->get_instrumented_statements();
        $code = $instrumented_statements->get_code();

        $temp_file = tempnam("/tmp", "PHPUnitScribe_");
        echo "TEMP FILE $temp_file\n";
        file_put_contents($temp_file, '<?php ' . $code);
        include $temp_file;

        $editor = new PHPUnitScribe_TestEditor($temp_file, 'do_a_thing');
        PHPUnitScribe_Interceptor::register_editor($editor);

        //$class_name = "\\phpunitscribe_instrumented_namespace\\Decomposer_Test_Class";
        //$obj = new $class_name();
        $obj = new Decomposer_Test_Class();
        $obj->do_a_thing();
        echo "dumping interceptions\n";
        var_dump(PHPUnitScribe_Interceptor::get_interceptions());
    }

    public function test_simple()
    {
        $code = file_get_contents(__DIR__ . '/data/Decomposer_Simple_Class.php');
        $parser = new PHPParser_Parser(new PHPParser_Lexer());
        $statements = $parser->parse($code);
        $statement_container = new PHPUnitScribe_Statements($statements);
        $instrumented_statements = $statement_container->get_instrumented_statements();

        $code = $instrumented_statements->get_code();
        echo $code;
    }
}

