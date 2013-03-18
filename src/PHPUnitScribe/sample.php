<?php

require '../../vendor/PHP-Parser/lib/bootstrap.php';
ini_set('xdebug.max_nesting_level', 2000);


class CodeFinder
{
    public static function findCode(ReflectionClass $class)
    {
        $file_name = $class->getFileName();
        $start_line = $class->getStartLine() - 1;
        $end_line = $class->getEndLine();
        $length = $end_line - $start_line;
        $source = file($file_name);
        $body = implode("", array_slice($source, $start_line, $length));
        return $body;
    }
}

class Instrumenter
{
    const instrumented_namespace = 'PHPUnitScribe_Instrumented';
    /** @var resource[] */
    private static $temp_file_handles;

    public static function register_temp_file($handle)
    {
        $temp_file_handles[] = $handle;
    }

    public static function close_temp_files()
    {
        foreach(self::$temp_file_handles as $temp_file_handle)
        {
            fclose($temp_file_handle);
        }
    }

    public static function get_instrumented_class(ReflectionClass $class)
    {
        $instrumented_class_name = '\\' . self::instrumented_namespace . '\\' . $class->getName();
        if (class_exists($instrumented_class_name)) {
            return $instrumented_class_name;
        }
        else {
            return self::generate_instrumented_class($class);
        }
    }

    public static function generate_instrumented_class(ReflectionClass $class)
    {
        echo "generating instrumentation\n";
        $new_code = "<?php namespace " . self::instrumented_namespace . ";\n" .
            "include_once 'Interloper.php'\n";
        $base_code = '<?php ' . CodeFinder::findCode($class);

        $parser = new PHPParser_Parser(new PHPParser_Lexer);
        $stmts = $parser->parse($base_code);
        $traverser = new PHPParser_NodeTraverser;
        $printer = new PHPParser_PrettyPrinter_Default();

        $traverser->addVisitor(new PHPParser_NodeVisitor_NameResolver());
        $traverser->addVisitor(new NodeVisitor_Instrumentor());
        $stmts = $traverser->traverse($stmts);
        $new_code .= $printer->prettyPrint($stmts);
        //include_once 'Interloper.php';
        //eval($new_code);
        $temp_file = tmpfile();
        fwrite($temp_file, $new_code);
        self::register_temp_file($temp_file);
        return self::instrumented_namespace . "\\" . $class->getName();
    }
}

class NodeVisitor_Instrumentor extends PHPParser_NodeVisitorAbstract
{
    public function leaveNode(PHPParser_Node $node) {
        echo "enterpinr a now with intstrupemetn\n";
        var_dump($node);
        if ($node instanceof PHPParser_Node_Expr_MethodCall) {
            $node = $this->generate_router($node);
            return $node;
        }
    }

    private function generate_router($method_call) {
        echo "generating router\n";
        return new PHPParser_Node_Expr_StaticCall(
            new PHPParser_Node_Name(
                array('PHPUnitScribe_Interloper')
            ),
            'route',
            array(
                new PHPParser_Node_Arg(
                    new PHPParser_Node_Expr_Closure(
                        array(
                            'stmts' => array($method_call)
                        )
                    )
                )
            )
        );
    }

}

class NodeVisitor_REPL_MethodFinder extends PHPParser_NodeVisitorAbstract
{
    private function generateInclude()
    {
       new PHPParser_Node_
    }

    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Expr_New)
        {
            $class = new ReflectionClass($node->class->toString());
            $instrumented_class_name = Instrumenter::get_instrumented_class($class);
            $instrumented_class_file = Instrumenter::get_temp_file_handle($instrumented_class_name);
            $node->class->parts = array(Instrumenter::instrumented_namespace, $class->getName());
            return $node;
        }

        if ($node instanceof PHPParser_Node_Expr_MethodCall) {
            $class_getter_nodes = NodeGenerator::repl_get_class($node->var->name);
            $printer = new PHPParser_PrettyPrinter_Default();
            $to_eval = $printer->prettyPrint(array($class_getter_nodes));
            //$to_eval = 'return get_class(REPL_Vars::$instance->' . $node->var->name . ');';
            echo "GETTING CLASS LIKE $to_eval";
            $class_name = eval($to_eval);
            $method = new ReflectionMethod($class_name, $node->name);
            Instrumenter::instrument_method($method);

            /*
            $body = CodeFinder::findCode($method);
            echo "METHOD " . $node->name . "\n";
            print_r($body);
            */
        }
    }
	public function afterTraverse(array $nodes)
	{
        $printer = new PHPParser_PrettyPrinter_Default();
        $code = $printer->prettyPrint($nodes);
        echo "EXECUTING $code \n";
        eval($code);
	}
}

class REPL_Constants
{
    static $NAMESPACE = 'REPL_Namespace';
}

class REPL_Vars
{
    protected static $vars = null;
    public static $instance = null;

    public static function init() {
        self::$instance = new REPL_Vars;
    }
    public function &__get($name) {
        return static::$vars[$name];
    }
    public function __set($name, $value) {
        static::$vars[$name] = $value;
    }
    public function __isset($name) {
        return isset(static::$vars[$name]);
    }
    public function __unset($name) {
        unset(static::$vars[$name]);
    }
}

class NodeGenerator
{
    public static function repl_var($var) {
        $node = new PHPParser_Node_Expr_PropertyFetch(
            new PHPParser_Node_Expr_StaticPropertyFetch(
                new PHPParser_Node_Name('REPL_Vars'),
                'instance'
            ), $var
        );
        return $node;
    }

    public static function repl_get_class($var) {
        $var_node = static::repl_var($var);
        $node = new PHPParser_Node_Stmt_Return(
            new PHPParser_Node_Expr_FuncCall(
                new PHPParser_Node_Name('get_class'),
                array($var_node)
            )
        );
        return $node;
    }
}

class NodeVisitor_REPL_Namespacer extends PHPParser_NodeVisitorAbstract
{
    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Expr_Variable)
        {
            return NodeGenerator::repl_var($node->name);
        }
    }

}

//$code = file_get_contents('base_facebook.php');
//$code = '<?php C_Name::static_func(global_func("hi")); $b = new C_Name; $b->do_a_thing(3);';
$code = file_get_contents('sut.php');
$parser = new PHPParser_Parser(new PHPParser_Lexer);
$traverser     = new PHPParser_NodeTraverser;
$printer = new PHPParser_PrettyPrinter_Default();

$traverser->addVisitor(new PHPParser_NodeVisitor_NameResolver); // we will need resolved names
$traverser->addVisitor(new NodeVisitor_REPL_Namespacer());
$traverser->addVisitor(new NodeVisitor_REPL_MethodFinder);

try {
    $stmts = $parser->parse($code);
} catch (PHPParser_Error $e) {
    echo 'Parse Error: ', $e->getMessage();
}

include 'My_Class.php';
foreach ($stmts as $stmt)
{
    $traverser->traverse(array($stmt));
}

//$generated = $printer->prettyPrint($stmts);
//eval($generated);

//$nodeDumper = new PHPParser_NodeDumper;
//echo '<pre>' . htmlspecialchars($nodeDumper->dump($stmts)) . '</pre>';
