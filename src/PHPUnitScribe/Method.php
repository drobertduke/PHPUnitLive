<?php
/**
 */
class PHPUnitScribe_Method
{
    /** @var \PHPParser_Node_Stmt_ClassMethod */
    protected $stmt;
    public function __construct(PHPParser_Node_Stmt_ClassMethod $stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->stmt->name;
    }
}
