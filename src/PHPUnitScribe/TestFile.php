<?php
/**
 */
class PHPUnitScribe_TestFile
{
    /** @var string */
    protected $file_name;
    /** @var \PHPParser_Parser */
    protected $parser;
    /** @var \PHPParser_PrettyPrinter_Default */
    protected $printer;
    /** @var PHPUnitScribe_TestClass[] */
    protected $test_classes;

    /**
     * @param string $file_name
     */
    public function __construct($file_name)
    {
        $this->file_name = $file_name;
        $this->parser = new PHPParser_Parser(new PHPParser_Lexer());
        $this->printer = new PHPParser_PrettyPrinter_Default();
        $this->test_classes = array();
    }

    /**
     * @return PHPUnitScribe_TestClass[]
     */
    public function get_test_classes()
    {
        $contents = file_get_contents($this->file_name);
        $stmts = $this->parser->parse($contents);
        foreach ($stmts as $stmt)
        {
            if ($stmt instanceof PHPParser_Node_Stmt_Class)
            {
                $this->test_classes[] = new PHPUnitScribe_TestClass($stmt);
            }
        }
        return $this->test_classes;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->file_name;
    }

}
