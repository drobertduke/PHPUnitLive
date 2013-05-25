<?php
/**
 * Describes a interceptable statement and the choice made
 * (into, over, replace, or premature return)
 */

define("PHPUnitScribe_InterceptionChoice_Into", "interception_choice_into");
define("PHPUnitScribe_InterceptionChoice_Over", "interception_choice_over");
define("PHPUnitScribe_InterceptionChoice_Replace", "interception_choice_replace");
define("PHPUnitScribe_InterceptionChoice_Suppress", "interception_choice_suppress");
define("PHPUnitScribe_InterceptionChoice_PrematureReturn", "interception_choice_premature_return");

define("PHPUnitScribe_Instrumented_Namespace", "phpunitscribe_instrumented_namespace");

class PHPUnitScribe_InterceptionChoice
{
    /** @var  PHPParser_Node */
    protected $statement;
    /** @var  PHPParser_Node */
    protected $result;
    public function __construct($statement, $result)
    {
        $this->statement = $statement;
        $this->result = $result;
    }

    /**
     * @param PHPParser_Node $statement
     * @return bool
     * Has to implement equality for any PHPParser_Node_Expr
     */
    public function matches($statement)
    {
        if (!PHPUnitScribe_Interceptor::is_interceptable_reference($statement))
        {
            throw new Exception("Original statement should've been an interceptable_reference\n" .
                "Instead it was " . get_class($statement));
        }
        if (get_class($statement) != get_class($this->statement))
        {
            return false;
        }
        if ($statement instanceof PHPParser_Node_Expr_New)
        {
            if ($statement->class instanceof PHPParser_Node_Name &&
                $this->statement->class instanceof PHPParser_Node_Name)
            {
                return $statement->class->parts === $this->statement->class->parts;
            }
            elseif ($statement->class instanceof PHPParser_Node_Expr)
            {

            }
            throw new Exception("Trying to match an Expr_New without a Node_Name class");
        }
        if ($statement instanceof PHPParser_Node_Expr_StaticCall)
        {
            if ($statement->class instanceof PHPParser_Node_Name &&
                $this->statement->class instanceof PHPParser_Node_Name &&
                is_string($statement->name) && is_string($this->statement->name))
            {
                return $statement->class->parts == $this->statement->class->parts &&
                    $statement->name === $this->statement->name;
            }
            throw new Exception("Trying to match a static call without a node_name class and string name");
        }
        if ($statement instanceof PHPParser_Node_Expr_FuncCall)
        {
            if ($statement->name instanceof PHPParser_Node_Name)
        }
    }

    public function get_result()
    {
        return $this->result;
    }
}
