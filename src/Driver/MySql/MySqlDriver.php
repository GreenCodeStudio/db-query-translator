<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\MySql;

class MySqlDriver
{

    public function __construct()
    {
    }

    public function parse(string $sql)
    {
        $parser = new Parser\MySqlParser($sql);
        return $parser->parse();
    }

    public function serialize($node)
    {
        $serializer = new Serializer\MySqlSerializer();
        return $serializer->serialize($node);
    }
}
