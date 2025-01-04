<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Query\Column;

class SelectColumn
{

    private string $name;
    private mixed $expression;

    public function __construct(string $name, $expression)
    {
        $this->name = $name;
        $this->expression = $expression;
    }
}
