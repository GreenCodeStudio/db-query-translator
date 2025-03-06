<?php
namespace Mkrawczyk\DbQueryTranslator\Nodes\Expression;

class Comparison
{
    public mixed $left;
    public mixed $right;
    public bool $lessThan = false;
    public bool $orEqual = false;

    public function __construct($left, $right, bool $lessThan, bool $orEqual)
    {
        $this->left = $left;
        $this->right = $right;
        $this->lessThan = $lessThan;
        $this->orEqual = $orEqual;
    }
}
