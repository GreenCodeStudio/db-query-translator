<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\MySQL;

class MySQLDriver
{

    public function __construct()
    {
    }

    public function parse(string $sql)
    {
        $parser = new Parser\MySQLParser($sql);
        return $parser->parse();
    }
}
