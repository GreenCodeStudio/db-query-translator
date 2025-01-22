<?php

namespace Mkrawczyk\DbQueryTranslator\LocalExecutor;

use MKrawczyk\FunQuery\FunQuery;

class LocalDB
{
    public $tables = [];

    public function add(string $name, $data)
    {
        $this->tables[$name] = FunQuery::create($data);
    }
}
