<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Expression;

class Table
{
    public string $tableName;
    public string $alias;

    public function __construct(string $tableName, ?string $alias = null)
    {
        $this->tableName = $tableName;
        $this->alias = $alias ?? $tableName;
    }
}
