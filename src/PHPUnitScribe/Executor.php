<?php
/**
 */
class PHPUnitScribe_Executor
{

    /**
     * @param PHPParser_Node[] $stmts
     */
    public static function execute(array $stmts)
    {
        $printer = new PHPParser_PrettyPrinter_Default();
        $code = $printer->prettyPrint($stmts);
        eval($code);
    }

}
