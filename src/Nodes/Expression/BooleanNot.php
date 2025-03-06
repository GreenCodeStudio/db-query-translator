<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Expression;

class BooleanNot
{

    public mixed $expression;

    public function __construct($expression)
    {
        $this->expression = $expression;
    }
    public function __toString()
    {
        return "BooleanNot";
    }
}
