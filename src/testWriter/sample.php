<?php

require '../../vendor/nikic/php-parser/lib/bootstrap.php';
ini_set('xdebug.max_nesting_level', 2000);

/*
 * for each statement in repl
 * for each expr
 * find its code
 * instrument
 * run
 */

class NodeVisitor_MethodFinder extends PHPParser_NodeVisitorAbstract
{
	public function leaveNode(PHPParser_Node $node)
	{
		if ($node instanceof PHPParser_Node_Expr_MethodCall) {
			$class_name = eval('get_class($'. $node->var->name . ')');//get_class($node->var);
			$reflect = new ReflectionMethod($class_name, $node->name);
			$file_name = $reflect->getFileName();
			$start_line = $reflect->getStartLine() - 1;
			$end_line = $reflect->getEndLine();
			$length = $end_line - $start_line;
			$source = file($file_name);
			$body = implode("", array_slice($source, $start_line, $length));
			print_r($body);
		}
	}
}
//$code = file_get_contents('base_facebook.php');
//$code = '<?php C_Name::static_func(global_func("hi")); $b = new C_Name; $b->do_a_thing(3);';
$code = file_get_contents('sut.php');
$parser = new PHPParser_Parser(new PHPParser_Lexer);
$traverser     = new PHPParser_NodeTraverser;
$printer = new PHPParser_PrettyPrinter_Zend();

$traverser->addVisitor(new PHPParser_NodeVisitor_NameResolver); // we will need resolved names
//$traverser->addVisitor(new NodeVisitor_MethodFinder);

try {
    $stmts = $parser->parse($code);
} catch (PHPParser_Error $e) {
    echo 'Parse Error: ', $e->getMessage();
}

include 'My_Class.php';
$traverser->traverse($stmts);

//$generated = $printer->prettyPrint($stmts);
//eval($generated);

$nodeDumper = new PHPParser_NodeDumper;
echo '<pre>' . htmlspecialchars($nodeDumper->dump($stmts)) . '</pre>';
