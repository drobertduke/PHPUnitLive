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
        $statements = $instrumented_statements->get_code();
        echo $statements;
        //var_dump($printer->prettyPrint(array($statements)));
    }
}
