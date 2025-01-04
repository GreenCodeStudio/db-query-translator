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

    public function serialize($node)
    {
        $serializer = new Serializer\MySQLSerializer();
        return $serializer->serialize($node);
    }
}
