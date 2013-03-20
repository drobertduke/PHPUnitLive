<?php
/**
 * Describes a mockable statement and the choice made
 * (into, over, replace, or premature return)
 */

define("PHPUnitScribe_MockingChoice_Into", "mocking_choice_into");
define("PHPUnitScribe_MockingChoice_Over", "mocking_choice_over");
define("PHPUnitScribe_MockingChoice_Replace", "mocking_choice_replace");
define("PHPUnitScribe_MockingChoice_PrematureReturn", "mocking_choice_premature_return");

define("PHPUnitScribe_Instrumented_Namespace", "phpunitscribe_instrumented_namespace");

class PHPUnitScribe_MockingChoice
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
