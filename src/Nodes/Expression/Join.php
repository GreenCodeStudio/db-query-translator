<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Expression;

class Join
{
    public $type;
    public $table;
    public $on;

    public function __construct($type, $table, $on)
    {
        $this->type = $type;
        $this->table = $table;
        $this->on = $on;
    }
}
