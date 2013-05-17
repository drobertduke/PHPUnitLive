<?php
/**
 * Describes a interceptable statement and the choice made
 * (into, over, replace, or premature return)
 */

define("PHPUnitScribe_InterceptionChoice_Into", "interception_choice_into");
define("PHPUnitScribe_InterceptionChoice_Over", "interception_choice_over");
define("PHPUnitScribe_InterceptionChoice_Replace", "interception_choice_replace");
define("PHPUnitScribe_InterceptionChoice_PrematureReturn", "interception_choice_premature_return");

define("PHPUnitScribe_Instrumented_Namespace", "phpunitscribe_instrumented_namespace");

class PHPUnitScribe_InterceptionChoice
{
    protected $statement;
    protected $result;
    public function __construct($statement)
    {
        $this->statement = $statement;
    }
    public function set_result($result)
    {
        $this->result = $result;
    }

    public function matches($statement)
    {
        return $statement == $this->statement;
    }

    public function get_result()
    {
        return $this->result;
    }
}
