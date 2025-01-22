<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Expression;
class Identifier
{
    public string $name;
    public ?string $table = null;

    public function __construct(string $name, ?string $table = null)
    {
        $this->name = $name;
        $this->table = $table;
    }
}
