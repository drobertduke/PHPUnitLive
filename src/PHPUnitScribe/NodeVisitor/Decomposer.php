<?php
/**
 */
class PHPUnitScribe_NodeVisitor_Decomposer extends PHPParser_NodeVisitorAbstract
{

    protected $context_stack = array();

    protected $decomposing_queue = array();

    protected function pop_context()
    {
        return array_shift($this->context_stack);
    }

    protected function peek_context()
    {
        return $this->context_stack[0];
    }

    protected function push_context($context)
    {
        array_unshift($this->context_stack, $context);
    }

    protected function get_statement_array(PHPParser_Node $node)
    {
        if ($this->contains_statement_array(get_class($node)))
        {
            return $node->stmts;
        }
        return null;
    }

    protected function is_decomposition_enabled()
    {
        foreach ($this->context_stack as $context)
        {
            if ($this->contains_statement_array($context))
            {
                return false;
            }
        }
        return true;
    }

    protected function contains_statement_array($node_name)
    {
        return $node_name === 'PHPParser_Node_Stmt_Function' ||
            $node_name === 'PHPParser_Node_Stmt_ClassMethod' ||
            $node_name === 'PHPParser_Node_Expr_Closure';
    }

    protected function in_nested_context()
    {
        if (count($this->context_stack) === 0)
        {
            return false;
        }
        if (count($this->context_stack) == 1)
        {
            echo "context stack is 1\n";
            var_dump($this->context_stack);
        }
        if (count($this->context_stack) == 1 &&
            $this->context_stack[0] === 'PHPParser_Node_Expr_Assign')
        {
            echo "not nested!\n";
            return false;
        }

        return true;
    }

    public function enterNode(PHPParser_Node $node)
    {
        $this->push_context(get_class($node));
        if (is_array($inner_stmts = $this->get_statement_array($node)))
        {
            $inner_traverser = new PHPParser_NodeTraverser();
            $inner_decomposer = new PHPUnitScribe_NodeVisitor_Decomposer();
            $inner_traverser->addVisitor($inner_decomposer);
            $node->stmts = $inner_traverser->traverse($inner_stmts);
        }
    }

    public function leaveNode(PHPParser_Node $node)
    {
        $this->pop_context();
        $var = null;
        if (PHPUnitScribe_Interceptor::is_mockable_reference($node) &&
            $this->in_nested_context() &&
            count($this->context_stack) > 0  &&
            $this->is_decomposition_enabled())
        {
            $var_name = PHPUnitScribe_Interceptor::get_new_var_name();
            $var = new PHPParser_Node_Expr_Variable($var_name);
            $assigner = new PHPParser_Node_Expr_Assign($var, $node);
            $this->decomposing_queue[] = $assigner;
            return $var;
        }
        else if (count($this->context_stack) === 0 && count($this->decomposing_queue) > 0)
        {
            $stmts_to_return = $this->decomposing_queue;
            $stmts_to_return[] = $node;
            $this->decomposing_queue = array();
            return $stmts_to_return;
        }
        return $node;
    }
}
