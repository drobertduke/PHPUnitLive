<?php
/**
 */
class PHPUnitScribe_NodeVisitor_Decomposer extends PHPParser_NodeVisitorAbstract
{

    public function enterNode(PHPParser_Node $node)
    {
        if (PHPUnitScribe_Interceptor::is_potential_compound_statement($node))
        {
            //PHPUnitScribe_Interceptor::increase_decomposition_layer();

        }
        return $node;
    }

    public function leaveNode(PHPParser_Node $node)
    {
        $var = null;
        if (PHPUnitScribe_Interceptor::is_mockable_reference($node) &&
            PHPUnitScribe_Interceptor::get_decomposition_layer() > 0)
        {
            $var_name = PHPUnitScribe_Interceptor::get_new_var_name();
            $var = new PHPParser_Node_Expr_Variable($var_name);
            $assigner = new PHPParser_Node_Expr_Assign($var, $node);
            PHPUnitScribe_Interceptor::enqueue_decomposition($assigner);
        }

        if (PHPUnitScribe_Interceptor::is_potential_compound_statement($node))
        {
            //PHPUnitScribe_Interceptor::decrease_decomposition_layer();
            $inner_candidate_nodes = PHPUnitScribe_Interceptor::get_potential_compound_nodes($node);
            if ($inner_candidate_nodes)
            {
                $new_traverser = new PHPParser_NodeTraverser();
                $new_traverser->addVisitor(new PHPUnitScribe_NodeVisitor_Decomposer);
                PHPUnitScribe_Interceptor::increase_decomposition_layer();
                $decomposed = $new_traverser->traverse($inner_candidate_nodes);
                PHPUnitScribe_Interceptor::decrease_decomposition_layer();
                $node = PHPUnitScribe_Interceptor::replace_compound_nodes($node, $decomposed);
                if (PHPUnitScribe_Interceptor::get_decomposition_layer() === 0)
                {
                    $stmts = array();
                    while ($decomposition = PHPUnitScribe_Interceptor::dequeue_decomposition())
                    {
                        $stmts[] = $decomposition;
                    }
                    $stmts[] = $node;
                    return $stmts;
                }
            }
        }

        if ($var)
        {
            return $var;
        }
        return $node;
    }
}
