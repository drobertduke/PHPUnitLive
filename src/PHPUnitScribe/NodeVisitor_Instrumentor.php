<?php
/**
 * Replaces calls/references external to the current function,
 * adds control structures which allower PHPUnitScribe_Interceptor
 * to make mocking choices about the call/reference
 */
class PHPUnitScribe_NodeVisitor_Instrumentor extends PHPParser_NodeVisitorAbstract
{
    /**
     * @param PHPParser_Node $node
     * @return PHPParser_Node[]
     */
    protected function get_interception_structure($node)
    {
        $assigned_var_name = 'unused_var_placeholder';
        $expression = $node;
        if ($node instanceof PHPParser_Node_Expr_Assign)
        {
            $assigned_var_name = $node->var->name;
            $expression = $node->expr;
        }
        $printer = new PHPParser_PrettyPrinter_Default();
        $printed_statement = $printer->prettyPrint(array($expression));
        $parser = new PHPParser_Parser(new PHPParser_Lexer());
        $reflection_class = new ReflectionClass('Interceptor_Template');
        $template_code = file_get_contents($reflection_class->getFileName());
        $interceptor_template = new PHPParser_Template($parser, $template_code);
        $properties = array(
            array('statement' => $printed_statement, 'var' => $assigned_var_name)
        );
        $interceptor_function = $interceptor_template->getStmts($properties)[0];
        $interceptor_structure = $interceptor_function->stmts;
        return $interceptor_structure;
    }

    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Expr_New)
        {
            $interception_structure = $this->get_interception_structure($node);
            return $interception_structure;
        }
    }

}
