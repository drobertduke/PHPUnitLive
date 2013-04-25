<?php
/**
 */
class PHPUnitScribe_NodeVisitor_Decomposer extends PHPParser_NodeVisitorAbstract
{

    protected $context_stack = array();

    protected $decomposing_queue = array();

    protected $inner_stmts_decomposed = array();

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
            $node_name === 'PHPParser_Node_Expr_Closure' ||
            $node_name === 'PHPParser_Node_Stmt_If' ||
            $node_name === 'PHPParser_Node_Stmt_Case' ||
            $node_name === 'PHPParser_Node_Stmt_Catch' ||
            $node_name === 'PHPParser_Node_Stmt_Class' ||
            $node_name === 'PHPParser_Node_Stmt_Declare' ||
            $node_name === 'PHPParser_Node_Stmt_Do' ||
            $node_name === 'PHPParser_Node_Stmt_Else' ||
            $node_name === 'PHPParser_Node_Stmt_Elseif' ||
            $node_name === 'PHPParser_Node_Stmt_For' ||
            $node_name === 'PHPParser_Node_Stmt_Foreach' ||
            $node_name === 'PHPParser_Node_Stmt_Interface' ||
            $node_name === 'PHPParser_Node_Stmt_Namespace' ||
            $node_name === 'PHPParser_Node_Stmt_Trait' ||
            $node_name === 'PHPParser_Node_Stmt_TryCatch' ||
            $node_name === 'PHPParser_Node_Stmt_While';
    }

    protected function in_nested_context()
    {
        if (count($this->context_stack) === 0)
        {
            return false;
        }
        if (count($this->context_stack) == 1 &&
            $this->context_stack[0] === 'PHPParser_Node_Expr_Assign')
        {
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
            //$node->stmts = $inner_traverser->traverse($inner_stmts);
            $this->inner_stmts_decomposed = $inner_traverser->traverse($inner_stmts);
            // Zero out the inner section so we don't do extra work
            $node->stmts = array();
        }
    }

    public function leaveNode(PHPParser_Node $node)
    {
        $this->pop_context();
        $var = null;
        if (count($this->inner_stmts_decomposed) > 0 &&
            is_array($inner_stmts = $this->get_statement_array($node)))
        {
            $node->stmts = $this->inner_stmts_decomposed;
            $this->inner_stmts_decomposed = array();
        }
        if (PHPUnitScribe_Interceptor::is_mockable_reference($node) &&
            $this->in_nested_context())
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
