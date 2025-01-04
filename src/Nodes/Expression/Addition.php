<?php
namespace Mkrawczyk\DbQueryTranslator\Nodes\Expression;
class Addition
{
    private mixed $left;
    private mixed $right;

    public function __construct($left, $right)
    {
        $this->left = $left;
        $this->right = $right;
    }
}
