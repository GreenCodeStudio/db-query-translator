<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\SqlServer;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\AbstractSqlDriver;
use Mkrawczyk\DbQueryTranslator\Driver\SqlServer\Serializer\SqlServerSerializer;

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
        $serializer = new SqlServerSerializer();
        return $serializer->serialize($node);
    }
}
