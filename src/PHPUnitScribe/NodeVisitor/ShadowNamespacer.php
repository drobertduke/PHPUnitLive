<?php
/**
 */
class PHPUnitScribe_NodeVisitor_ShadowNamespacer extends PHPParser_NodeVisitorAbstract
{
    public function enterNode(PHPParser_Node $node)
    {
        if (PHPUnitScribe_Interceptor::is_external_reference($node))
        {
            $node->class->append(PHPUnitScribe_Instrumented_Namespace);
            return $node;
        }
        return null;
    }

}
