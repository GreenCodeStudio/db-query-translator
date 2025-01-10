<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Expression;

class Division
{

    public mixed $left;
    public mixed $right;

    public function __construct($left, $right)
    {
        $this->left = $left;
        $this->right = $right;
    }
}
