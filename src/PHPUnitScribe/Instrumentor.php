<?php
class PHPUnitScribe_Instrumentor
{

    public static function include_file($file_name)
    {
        $temp_file = tempnam(sys_get_temp_dir(), $file_name);
        $stmts = self::instrument_file($file_name);
        $printer = new PHPParser_PrettyPrinter_Default();
        $code = $printer->prettyPrint($stmts->get_statements());
        file_put_contents($temp_file, $code);
        include_once $temp_file;
    }

    public static function instrument_file($file_name)
    {
        $code = file_get_contents($file_name);
        $parser = new PHPParser_Parser(new PHPParser_Lexer());
        $statements = $parser->parse($code);
        $statement_container = new PHPUnitScribe_Statements($statements);
        return $statement_container->get_instrumented_statements();
    }

}
