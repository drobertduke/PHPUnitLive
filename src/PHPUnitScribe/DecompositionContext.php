<?php
/**
 */
class PHPUnitScribe_DecompositionContext
{
    protected $node_type;
    protected $nodes_to_decompose = array();

    public function __construct($node_type)
    {
        $this->node_type = $node_type;
    }

    public function add_node_to_decompose(PHPParser_Node $node)
    {
        $this->nodes_to_decompose[] = $node;
    }

    public function nodes_to_decompose()
    {
        return $this->nodes_to_decompose;
    }

}
