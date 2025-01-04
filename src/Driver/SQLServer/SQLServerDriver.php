<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\SQLServer;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSQL\AbstractSQLDriver;

class SQLServerDriver
{
    public function __construct()
    {
    }

    public function parse(string $sql)
    {
        $parser = new Parser\SQLServerParser($sql);
        return $parser->parse();
    }

    public function serialize($node)
    {
        $serializer = new Serializer\SQLServerSerializer();
        return $serializer->serialize($node);
    }
}
