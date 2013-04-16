<?php
/**
 * Global registry for instrumented files.
 * Tracks each instrumented file and instruments it only once.
 * This list is maintained across tests.
 * Maintains the global set of interceptors.  This set is
 * queried by the instrumented code to determine how to
 * handle mockable calls/references.
 */

class PHPUnitScribe_Interceptor
{
    /** @var null|PHPUnitScribe_TestEditor */
    static protected $editor = null;
    static protected $interactive_mode = false;
    static protected $files_instrumented = array();
    /** @var PHPUnitScribe_MockingChoice[] */
    static protected $mocking_choices = array();
    static protected $enabled = true;
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

    public static function register_mocking_choices(array $choices)
    {
        self::$mocking_choices = $choices;
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

    public static function intercept($statement)
    {
        if (!self::$enabled)
        {
            return PHPUnitScribe_MockingChoice_Over;
        }

        if (self::$editor)
        {
            return self::$editor->prompt_for_mock($statement);
        }
        else
        {
            foreach (self::$mocking_choices as $choice)
            {
                if ($choice->matches($statement))
                {
                    return $choice->get_result();
                }
            }

        }

    }

    public static function is_external_reference(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Expr_New)
        {
            return true;
        }
        return false;
    }

}
