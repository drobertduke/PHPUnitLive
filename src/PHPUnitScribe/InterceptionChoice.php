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

//define("PHPUnitScribe_Instrumented_Namespace", "phpunitscribe_instrumented_namespace");

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
     * @param $candidate_statement
     * @param $object_types
     * @return bool
     * @throws Exception
     * Has to implement equality for any PHPParser_Node_Expr
     */
    public function matches($candidate_statement, $object_types)
    {
        // What do we do with object types?
        if (!PHPUnitScribe_Interceptor::is_interceptable_reference($candidate_statement))
        {
            throw new Exception("Original statement should've been an interceptable_reference\n" .
                "Instead it was " . get_class($candidate_statement));
        }
        if (get_class($candidate_statement) != get_class($this->statement))
        {
            return false;
        }
        $candidate_components = PHPUnitScribe_Interceptor::find_all_components_of_node($candidate_statement);
        foreach ($candidate_components as $property_name => $candidate_component)
        {
            if ($candidate_component->$property_name instanceof PHPParser_Node_Name)
            {
                if ($candidate_statement->$property_name->parts != $this->statement->$property_name->parts)
                {
                    return false;
                }
            }
            elseif (is_string($candidate_component->$property_name))
            {
                if ($candidate_statement->$property_name != $this->statement->$property_name)
                {
                    return false;
                }
            }
            else
            {
                throw new Exception("Component wasn't a string or Node_Name" . var_export($candidate_statement, true));
            }
        }
        return true;
    }

    private function check_equality($node1, $node2)
    {

    }

    public function get_result()
    {
        return $this->result;
    }
}
