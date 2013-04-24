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
        if ($this->contains_statement_array($node))
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

    protected function contains_statement_array(PHPParser_Node $node)
    {
        return $node instanceof PHPParser_Node_Stmt_Function ||
            $node instanceof PHPParser_Node_Stmt_ClassMethod ||
            $node instanceof PHPParser_Node_Expr_Closure;
    }

    protected $decomposition_enabled = true;

    public function enterNode(PHPParser_Node $node)
    {
        $this->push_context($node);
        if ($node->name == 'do_a_thing')
        {
            echo "calling do_a_thing\n";
        }
        if (is_array($inner_stmts = $this->get_statement_array($node)))
        {
            $inner_traverser = new PHPParser_NodeTraverser();
            $inner_decomposer = new PHPUnitScribe_NodeVisitor_Decomposer();
            $inner_traverser->addVisitor($inner_decomposer);
            echo "traversing " . get_class($node) . " " . get_class($inner_stmts[0]) . "\n";
            //var_dump($inner_stmts);
            $node->stmts = $inner_traverser->traverse($inner_stmts);
        }
    }

    public function leaveNode(PHPParser_Node $node)
    {
        $this->pop_context();
        echo "exiting " . get_class($node);
        if ($node->hasAttribute('name'))
        {
            echo "exiting " . $node->name . "\n";
        }
        if (is_array($inner_stmts = $this->get_statement_array($node)))
        {
            echo "GOT STATEMENT ARRAY " . get_class($node) . "\n";
        }
        $var = null;
        if (PHPUnitScribe_Interceptor::is_mockable_reference($node) &&
            count($this->context_stack) > 0  &&
            $this->is_decomposition_enabled())
        {
            echo "DECOMPT " . get_class($node);
            if (is_string($node->name)) { echo $node->name . "\n";}
            $var_name = PHPUnitScribe_Interceptor::get_new_var_name();
            $var = new PHPParser_Node_Expr_Variable($var_name);
            if ($var->name == 'PHPUnitScribe_TempVar8')
            {
                echo "adding TempVar8\n";
                var_dump($this->context_stack);
            }
            $assigner = new PHPParser_Node_Expr_Assign($var, $node);
            $this->decomposing_queue[] = $assigner;
            return $var;
        }
        else if (count($this->context_stack) === 0 && count($this->decomposing_queue) > 0)
        {
            echo "printing multiple\n";
            $stmts_to_return = $this->decomposing_queue;
            $stmts_to_return[] = $node;
            $this->decomposing_queue = array();
            $printer = new PHPParser_PrettyPrinter_Default();
            //echo $printer->prettyPrint($stmts_to_return);
            return $stmts_to_return;
        }

        echo "returning the origina " . get_class($node);
        if (is_string($node->name)) { echo $node->name . "\n";}

        return $node;
    }
}
