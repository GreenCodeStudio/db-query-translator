<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Expression;
class Column
{
    public string $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
