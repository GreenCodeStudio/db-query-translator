<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Query\Column;

class SelectColumn
{

    public string $name;
    public mixed $expression;

    public function __construct(string $name, $expression)
    {
        $this->name = $name;
        $this->expression = $expression;
    }
    public function __toString()
    {
        return "SelectColumn(".$this->name.")";
    }
}
