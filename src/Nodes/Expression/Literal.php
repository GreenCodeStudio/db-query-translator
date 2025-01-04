<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Expression;

class Literal
{
    public string $type;
    public $value;
    public function __construct(string $type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }
}
