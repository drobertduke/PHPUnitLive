<?php
/**
 * A set of PHP-Parser statements
 */
class PHPUnitScribe_Statements
{
    protected $statements = array();
    public function __construct(array $statements)
    {
        $this->statements = $statements;
    }

    public function get_instrumented_statements()
    {
        echo "instrumenting statements\n";
        $traverser = new PHPParser_NodeTraverser();
        $traverser->addVisitor(new PHPParser_NodeVisitor_NameResolver);
        $traverser->addVisitor(new PHPUnitScribe_NodeVisitor_Instrumentor);
        $statements_namespaced = array(
            new PHPParser_Node_Stmt_Namespace(PHPUnitScribe_Instrumented_Namespace, $this->statements)
        );
        $traverser->traverse($statements_namespaced);
        return new PHPUnitScribe_Statements($statements_namespaced);
    }

    public function execute()
    {
        echo "executing statements\n";
        PHPUnitParser_PrettyPrinter
    }

}
