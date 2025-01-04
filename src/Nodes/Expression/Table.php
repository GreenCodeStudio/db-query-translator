<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Expression;

class Table
{
    public string $tableName;

    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }
}
