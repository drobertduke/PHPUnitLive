<?php
/**
 * Global registry for instrumented files.
 * Tracks each instrumented file and instruments it only once.
 * This list is maintained across tests.
 * Maintains the global set of interceptors.  This set is
 * queried by the instrumented code to determine how to
 * handle interceptable calls/references.
 */

class PHPUnitScribe_Interceptor
{
    /** @var null|PHPUnitScribe_TestEditor */
    static protected $editor = null;
    static protected $interactive_mode = false;
    /** @var PHPUnitScribe_Statements[] */
    static protected $files_instrumented = array();
    /** @var PHPUnitScribe_InterceptionChoice[] */
    static protected $interceptions = array();
    static protected $enabled = true;
    static protected $var_num = 0;

    public static function include_shadow_files()
    {
        foreach (self::$files_instrumented as $file_name => $stmts)
        {
            $temp_file = tempnam(sys_get_temp_dir(), $file_name);
            $printer = new PHPParser_PrettyPrinter_Default();
            $code = $printer->prettyPrint($stmts->get_statements());
            file_put_contents($temp_file, $code);
            include_once $temp_file;
        }
    }

    private static function instrument_file_once($file_name)
    {
        if (!in_array($file_name, self::$files_instrumented))
        {
            echo "instrumenting $file_name\n";
            $code = file_get_contents($file_name);
            $parser = new PHPParser_Parser(new PHPParser_Lexer());
            $statements = $parser->parse($code);
            $statement_container = new PHPUnitScribe_Statements($statements);
            $instrumented_statements = $statement_container->get_instrumented_statements();
            self::$files_instrumented[$file_name] = $instrumented_statements;
        }
        else
        {
            echo "already instrumented $file_name\n";
        }
    }

    public static function enable()
    {
        self::$enabled = true;
    }

    public static function disable()
    {
        self::$enabled = false;
    }

    public static function instrument_files_once(array $files)
    {
        foreach ($files as $file)
        {
            self::instrument_file_once($file);
        }
    }
    public static function instrument_classes_once(array $class_names)
    {
        foreach ($class_names as $class_name)
        {
            if (class_exists($class_name))
            {
                echo "instrumenting class $class_name\n";
                $class = new ReflectionClass($class_name);
                $file_name = $class->getFileName();
                self::instrument_file_once($file_name);
            }
            else
            {
                echo "class not found $class_name\n";
            }
        }
    }

    public static function setup_with_interceptions(array $choices)
    {
        PHPUnitScribe_Autoloader::register_test_autoloader();
        self::$interceptions = $choices;
        echo "registering choices\n";
    }

    public static function set_interactive_mode($on)
    {
        if (!self::$editor)
        {
            echo "can't set interactive mode because not editor is registered\n";
            return false;
        }
        self::$interactive_mode = $on;
    }

    public static function register_editor($editor)
    {
        self::$editor = $editor;
    }

    public static function intercept($statement, $object_types, $allow_assignment = true, $statement_string)
    {
        echo "start interception\n";
        // If interactivity is disabled we just keep stepping in
        if (!self::$enabled)
        {
            echo "not enabled\n";
            return array(PHPUnitScribe_InterceptionChoice_Into, null);
        }

        if (self::$editor)
        {
            echo "has editor\n";
            return self::$editor->prompt_for_interception($statement, $object_types, $allow_assignment, $statement_string);
        }
        else
        {
            foreach (self::$interceptions as $interception)
            {
                if ($interception->matches($statement, $object_types))
                {
                    return $interception->get_result();
                }
            }
        }
        throw new Exception("The statement could not be intercepted: no choice found.  $statement");
    }

    public static function is_interceptable_reference(PHPParser_Node $node)
    {
        return
            self::is_external_reference($node) ||
            ($node instanceof PHPParser_Node_Expr_Assign && self::is_external_reference($node->expr)) ||
            $node instanceof PHPParser_Node_Expr_Exit ||
            $node instanceof PHPParser_Node_Expr_ShellExec;
    }

    public static function node_contains_inner_stmts($node_name)
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

    public static function is_external_reference(PHPParser_Node $node)
    {
        return
            $node instanceof PHPParser_Node_Expr_New ||
            $node instanceof PHPParser_Node_Expr_StaticCall ||
            $node instanceof PHPParser_Node_Expr_FuncCall ||
            $node instanceof PHPParser_Node_Expr_MethodCall ||
            $node instanceof PHPParser_Node_Expr_PropertyFetch ||
            $node instanceof PHPParser_Node_Expr_StaticPropertyFetch;
            //$node instanceof PHPParser_Node_Expr_Include;
    }

    protected static function is_conditional(PHPParser_Node $node)
    {
        return
            $node instanceof PHPParser_Node_Stmt_If ||
            $node instanceof PHPParser_Node_Stmt_ElseIf ||
            $node instanceof PHPParser_Node_Stmt_While ||
            $node instanceof PHPParser_Node_Stmt_Do ||
            $node instanceof PHPParser_Node_Stmt_Case ||
            $node instanceof PHPParser_Node_Stmt_Switch;
    }

    protected static function is_argumented(PHPParser_Node $node)
    {
        return
            $node instanceof PHPParser_Node_Expr_MethodCall ||
            $node instanceof PHPParser_Node_Expr_StaticCall ||
            $node instanceof PHPParser_Node_Expr_FuncCall ||
            $node instanceof PHPParser_Node_Expr_New;
    }

    protected static function is_return(PHPParser_Node $node)
    {
        return $node instanceof PHPParser_Node_Stmt_Return;
    }

    protected static function is_array_node(PHPParser_Node $node)
    {
        return $node instanceof PHPParser_Node_Expr_Array;
    }

    protected static function is_array_fetch(PHPParser_Node $node)
    {
        return $node instanceof PHPParser_Node_Expr_ArrayDimFetch;
    }

    public static function is_potential_compound_statement(PHPParser_Node $node)
    {
        return
            self::is_conditional($node) ||
            self::is_argumented($node) ||
            self::is_return($node) ||
            self::is_array_node($node) ||
            self::is_array_fetch($node) ||
            self::is_echo($node);
    }

    public static function is_echo(PHPParser_Node $node)
    {
        return $node instanceof PHPParser_Node_Stmt_Echo;
    }

    public static function get_potential_compound_nodes(PHPParser_Node $node)
    {
        $inner_nodes = array();
        if (self::is_conditional($node))
        {
            $inner_nodes = array($node->cond);
        }
        elseif (self::is_argumented($node))
        {
            $inner_nodes = $node->args;
            $inner_nodes[] = $node->var;
        }
        elseif (self::is_return($node))
        {
            $inner_nodes = array($node->expr);
        }
        elseif (self::is_array_node($node))
        {
            $inner_nodes = $node->items;
        }
        // wtf is this
        elseif (self::is_array_node($node))
        {
            $inner_nodes = array($node->dim);
        }
        elseif (self::is_echo($node))
        {
            $inner_nodes = $node->exprs;
        }
        return $inner_nodes;
    }

    public static function replace_compound_nodes(PHPParser_Node $node, array $replacements)
    {
        if (self::is_conditional($node))
        {
            $node->cond = $replacements[0];
        }
        elseif (self::is_argumented($node))
        {
            $node->args = $replacements;
        }
        elseif (self::is_return($node))
        {
            $node->expr = $replacements[0];
        }
        elseif (self::is_array_node($node))
        {
            $node->items = $replacements;
        }
        // wtf is this
        elseif (self::is_array_node($node))
        {
            $node->dim = $replacements[0];
        }
        elseif (self::is_echo($node))
        {
            $node->exprs = $replacements;
        }
        return $node;
    }

    public static function get_new_var_name()
    {
        self::$var_num++;
        return "PHPUnitScribe_TempVar" . self::$var_num;
    }

    public static function add_interception(PHPUnitScribe_InterceptionChoice $choice)
    {
        self::$interceptions[] = $choice;
    }

    public static function get_interceptions()
    {
        return self::$interceptions;
    }

    public static function is_replaceable($stmt)
    {
        return $stmt instanceof PHPParser_Node_Stmt_Return || $stmt instanceof PHPParser_Node_Expr_Assign;
    }

    private static function parse_stmt_string($stmt_string)
    {
        $parser = new PHPParser_Parser(new PHPParser_Lexer());
        $stmts = $parser->parse("<?php $stmt_string");
        if (count($stmts) !== 1)
        {
            throw new Exception("Expected 1 statement");
        }
        $stmt = $stmts[0];
        if (!self::is_interceptable_reference($stmt))
        {
            throw new Exception("Statement $stmt_string was not interceptable\n");
        }
        return $stmt;
    }

    /**
     * @param $stmt_string
     * @param $resolved_exprs
     * @return string
     * @throws Exception
     */
    public static function replace_unresolved_exprs($stmt_string, $resolved_exprs)
    {
        $stmt = self::parse_stmt_string($stmt_string);
        echo "original stmt\n";
        ini_set('xdebug.var_display_max_depth', -1);
        var_dump($stmt);
        $unresolved_exprs = self::get_unresolved_exprs_from_node($stmt);
        $printer = new PHPParser_PrettyPrinter_Default();
        $unique_string = 'Xf824H3jeiN91_f-nQwiA4TbbBdkWeYz9sPa';
        echo "resolved exprs\n";
        var_dump($resolved_exprs);
        $object_types = array();
        foreach ($unresolved_exprs as $part_name => $unresolved_expr)
        {
            if (!array_key_exists($part_name, $resolved_exprs))
            {
                echo "resolved expr idx $part_name\n";
                var_dump($unresolved_exprs);
                var_dump($resolved_exprs);
                throw new Exception("Resolved exprs can't be matched with unresolved exprs\n");
            }
            if (is_object($resolved_exprs[$part_name]))
            {
                $object_types[$part_name] = get_class($resolved_exprs[$part_name]);
            }
            else if (is_string($resolved_exprs[$part_name]))
            {
                // Replace the statement's part with a Node_Name containing a unique string
                $stmt->$part_name = new PHPParser_Node_Name($unique_string);
                // Render the statement
                $rendered_with_unique_string = $printer->prettyPrint(array($stmt));
                // Replace the unique string in the rendered statement with the resolved expr
                $string_replaced = str_replace($unique_string, $resolved_exprs[$part_name], $rendered_with_unique_string);
                // Parse the result and replace $stmt
                $stmt = self::parse_stmt_string($string_replaced);
            }
            else
            {
                throw new Exception("What did it resolve to?");
            }
        }
        echo "final stmt\n";
        var_dump($stmt);
        return array($object_types, $printer->prettyPrint(array($stmt)));
    }

    /**
     * @param $node
     * @return array
     */
    public static function find_all_components_of_node($node)
    {
        $components = array();
        if ($node instanceof PHPParser_Node_Expr_New)
        {
            $components['class'] = $node->class;
        }
        elseif ($node instanceof PHPParser_Node_Expr_StaticCall)
        {
            $components['class'] = $node->class;
            $components['name'] = $node->name;
        }
        elseif ($node instanceof PHPParser_Node_Expr_FuncCall)
        {
            $components['name'] = $node->name;
        }
        elseif ($node instanceof PHPParser_Node_Expr_MethodCall)
        {
            $components['name'] = $node->name;
            $components['var'] = $node->var;
        }
        elseif ($node instanceof PHPParser_Node_Expr_PropertyFetch)
        {
            $components['name'] = $node->name;
            $components['var'] = $node->var;
        }
        elseif ($node instanceof PHPParser_Node_Expr_StaticPropertyFetch)
        {
            $components['class'] = $node->class;
            $components['name'] = $node->name;
        }
        return $components;
    }

    /**
     * @param string $stmt_string
     * @return PHPParser_Node_Expr[]
     */
    public static function get_unresolved_exprs_from_string($stmt_string)
    {
        $stmt = self::parse_stmt_string($stmt_string);
        $unresolved_exprs = self::get_unresolved_exprs_from_node($stmt);
        $unresolved_exprs_printed = array();
        foreach ($unresolved_exprs as $part_name => $unresolved_expr)
        {
            $unresolved_exprs_printed[$part_name] = self::print_stmt($unresolved_expr);
        }
        echo "got unresolved exprs:\n";
        var_dump($unresolved_exprs_printed);
        return $unresolved_exprs_printed;
    }

    private static function print_stmt(PHPParser_Node $node)
    {
        $printer = new PHPParser_PrettyPrinter_Default();
        return $printer->prettyPrint(array($node));
    }

    /**
     * @param PHPParser_Node $node
     * @return PHPParser_Node_Expr[]
     */
    private static function get_unresolved_exprs_from_node($node)
    {
        $exprs = array();
        $components = self::find_all_components_of_node($node);
        foreach ($components as $property_name => $component)
        {
            if ($node->$property_name instanceof PHPParser_Node_Expr)
            {
                if (!($node->$property_name instanceof PHPParser_Node_Expr_Variable &&
                    $node->$property_name->name == 'this'))
                {
                    $exprs[$property_name] = $node->$property_name;
                }
            }
        }
        return $exprs;
    }
}

