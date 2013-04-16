<?php
/**
 */
class PHPUnitScribe_TestMethod extends PHPUnitScribe_Method
{
    public function instrument_executed_files()
    {
        echo "Instrumenting executed filed\n";
        $traverser = new PHPParser_NodeTraverser();
        $traverser->addVisitor(new PHPParser_NodeVisitor_NameResolver);
        $traverser->addVisitor(new PHPUnitScribe_NodeVisitor_ExecutedFileList);
        $stmts = array($this->stmt);
        $traverser->traverse($stmts);
    }

}
