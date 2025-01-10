<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\Postgresql;

class PostgresqlDriver
{
    public function serialize($node)
    {
        $serializer = new Serializer\PostgresqlSerializer();
        return $serializer->serialize($node);
    }
}
