<?php
/**
 * Represents a test file
 * Maintains a collection of test functions
 */
class PHPUnitScribe_TestClass
{
    /** @var PHPParser_Node_Stmt_Class */
    protected $stmt;

    /**
     * @param PHPParser_Node_Stmt_Class $stmt
     */
    public function __construct(PHPParser_Node_Stmt_Class $stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * @return PHPParser_Node[]
     */
    protected function get_class_contents()
    {
        return $this->stmt->stmts;
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->stmt->name;
    }

    /**
     * @return PHPParser_Node_Stmt_ClassMethod[]
     */
    protected function get_methods()
    {
        $methods = array();
        foreach ($this->get_class_contents() as $subnode)
        {
            if ($subnode instanceof PHPParser_Node_Stmt_ClassMethod)
            {
                $name = $subnode->name;
                if ($name === 'setup')
                {
                    $methods[] = new PHPUnitScribe_SetupMethod($subnode);
                }
                else if (strpos($name, 'test') === 0)
                {
                    $methods[] = new PHPUnitScribe_TestMethod($subnode);
                }
                else
                {
                    $methods[] = new PHPUnitScribe_Method($subnode);
                }
            }
        }
        return $methods;
    }

    /**
     * @return PHPUnitScribe_TestMethod[]
     */
    public function get_test_methods()
    {
        return array_filter($this->get_methods(), function($f)
        {
            return ($f instanceof PHPUnitScribe_TestMethod);
        });
    }

    /**
     * @return null|PHPUnitScribe_SetupMethod
     */
    public function get_setup_method()
    {
        foreach ($this->get_methods() as $method)
        {
            if ($method instanceof PHPUnitScribe_SetupMethod)
            {
                return $method;
            }
        }
        return null;
    }

}
