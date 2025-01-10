<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Expression;
class Identifier
{
    public string $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
