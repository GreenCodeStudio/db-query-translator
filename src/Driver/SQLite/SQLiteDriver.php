<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\SQLite;
class SQLiteDriver
{
    public function serialize($node)
    {
        $serializer = new Serializer\SQLiteSerializer();
        return $serializer->serialize($node);
    }

    public function parse($sql)
    {
        $parser = new Parser\SQLiteParser($sql);
        return $parser->parse();
    }
}
