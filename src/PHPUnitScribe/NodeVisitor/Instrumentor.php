<?php
/**
 * Replaces calls/references external to the current function,
 * adds control structures which allower PHPUnitScribe_Interceptor
 * to make mocking choices about the call/reference
 */
class PHPUnitScribe_NodeVisitor_Instrumentor extends PHPParser_NodeVisitorAbstract
{

    /** @var string[] */
    protected $context_stack = array();

    protected function pop_context()
    {
        return array_shift($this->context_stack);
    }

    /**
     * @param string $context
     */
    protected function push_context($context)
    {
        array_unshift($this->context_stack, $context);
    }

    /**
     * @return bool
     */
    protected function in_instrumentable_context()
    {
        if (count($this->context_stack) === 0)
        {
            return true;
        }
        $context = $this->context_stack[0];
        if (PHPUnitScribe_Interceptor::node_contains_inner_stmts($context))
        {
            return true;
        }
        return false;
    }

    /**
     * @param PHPParser_Node $node
     * @return PHPParser_Node[]
     */
    protected function get_interception_functions($node)
    {
        $printer = new PHPParser_PrettyPrinter_Default();
        $assigned_var_printed = 'unused_var_placeholder';
        $return_printed = 'return null;';
        if ($node instanceof PHPParser_Node_Expr_Assign)
        {
            $replacement = new PHPParser_Node_Expr_Variable('PHPUnitScribe_replacement');
            $new_assignment = new PHPParser_Node_Expr_Assign($node->var, $replacement);
            $assigned_var_printed = $printer->prettyPrint(array($new_assignment));
        }
        elseif ($node instanceof PHPParser_Node_Stmt_Return)
        {
            $replacement = new PHPParser_Node_Expr_Variable('PHPUnitScribe_replacement');
            $return_stmt = new PHPParser_Node_Stmt_Return($replacement);
            $return_printed = $printer->prettyPrint(array($return_stmt));
        }
        $printed_statement = $printer->prettyPrint(array($node));
        $printed_statement_escaped = addslashes(str_replace("\n", "", $printed_statement));

        $parser = new PHPParser_Parser(new PHPParser_Lexer());
        $reflection_class = new ReflectionClass('PHPUnitScribe_Template_Interceptor');
        $template_code = file_get_contents($reflection_class->getFileName());
        $interceptor_template = new PHPParser_Template($parser, $template_code);
        $properties = array(
            array(
                'statement' => $printed_statement,
                'statement_escaped' => $printed_statement_escaped,
                'replacement_statement' => $assigned_var_printed,
                'return_statement' => $return_printed
            )
        );
        $template_stmts = $interceptor_template->getStmts($properties[0]);
        $interceptor_functions = $template_stmts[0]->stmts;
        return $interceptor_functions;
    }

    public function enterNode(PHPParser_Node $node)
    {
        $this->push_context(get_class($node));
        return $node;
    }

    public function leaveNode(PHPParser_Node $node)
    {
        $this->pop_context();
        if (!$this->in_instrumentable_context())
        {
            return $node;
        }
        if ($node instanceof PHPParser_Node_Expr_Assign &&
            is_array($node->expr))
        {
            var_dump($node);
        }
        if ($node instanceof PHPParser_Node_Expr_Assign &&
            PHPUnitScribe_Interceptor::is_interceptable_reference($node->expr))
        {
            $interception_functions = $this->get_interception_functions($node);
            return $interception_functions[0]->stmts;
        }
        elseif (PHPUnitScribe_Interceptor::is_interceptable_reference($node))
        {
            $interception_functions = $this->get_interception_functions($node);
            return $interception_functions[1]->stmts;
        }
        return $node;
    }

}
