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
            $assigned_var = $node->var;
            $expression = $node->expr;
        }
        $printer = new PHPParser_PrettyPrinter_Default();
        $assigned_var_printed = $printer->prettyPrint(array($assigned_var));
        $assigned_var_printed = substr($assigned_var_printed, 1, -1);
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
                'var' => $assigned_var_printed
            )
        );
        $template_stmts = $interceptor_template->getStmts($properties[0]);
        /*
        foreach ($properties as $property)

        {
            $template_stmts = $interceptor_template->getStmts($property);
            break;
        }
        */
        $interceptor_structure = $template_stmts[0]->stmts[0]->stmts;
        return $interceptor_structure;
    }

    public function leaveNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Expr_Assign &&
            PHPUnitScribe_Interceptor::is_mockable_reference($node->expr))
        {
            $interception_structure = $this->get_interception_structure($node);
            return $interception_structure;
        }
    }

}
