<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\Postgres;

class PostgresDriver
{
    public function serialize($node)
    {
        $serializer = new Serializer\PostgresSerializer();
        return $serializer->serialize($node);
    }

    public function parse($sql)
    {
        $parser = new Parser\PostgresParser($sql);
        return $parser->parse();
    }
}
