<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Expression;

class Join
{
    public $table;
    public $on;

    public function __construct($table, $on)
    {
        $this->table = $table;
        $this->on = $on;
    }
}
