<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\Postgres;

class PostgresDriver
{
    public function serialize($node)
    {
        $serializer = new Serializer\PostgresSerializer();
        return $serializer->serialize($node);
    }
}
