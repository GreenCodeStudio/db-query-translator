<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\SQLServer;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSQL\AbstractSQLDriver;

class SqlServerDriver
{
    public function __construct()
    {
    }

    public function parse(string $sql)
    {
        $parser = new Parser\SqlServerParser($sql);
        return $parser->parse();
    }

    public function serialize($node)
    {
        $serializer = new Serializer\SQLServerSerializer();
        return $serializer->serialize($node);
    }
}
