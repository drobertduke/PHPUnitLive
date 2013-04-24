<?php
/**
 */
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

