<?php
/**
 */
class PHPUnitScribe_NodeVisitor_ShadowNamespacer extends PHPParser_NodeVisitorAbstract
{
    public function enterNode(PHPParser_Node $node)
    {
        /*
        if (PHPUnitScribe_Interceptor::is_external_reference($node))
        {
            $node->class->append(PHPUnitScribe_Instrumented_Namespace);
            return $node;
        }
        */
        return null;
    }

    public function leaveNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Class)
        {
            $name = new PHPParser_Node_Name("phpunitscribe_instrumented_namespace");
            $namespace_stmt = new PHPParser_Node_Stmt_Namespace($name, array($node));
            return $namespace_stmt;
        }
        return $node;
    }

}
