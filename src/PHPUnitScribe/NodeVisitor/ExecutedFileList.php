<?php
/**
 * Walks a test function looking for files/classes that should
 * be instrumented
 */
class PHPUnitScribe_NodeVisitor_ExecutedFileList extends PHPParser_NodeVisitorAbstract
{
    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Expr_StaticCall)
        {
            $names = $node->class->parts;
            if (in_array('PHPUnitScribe_Interceptor', $names))
            {
                PHPUnitScribe_Executor::execute(array($node));
            }
        }
        return null;
    }
}
