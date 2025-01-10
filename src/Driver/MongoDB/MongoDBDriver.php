<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\MongoDB;

use Mkrawczyk\DbQueryTranslator\Driver\MongoDB\Serializer\MongoDBSerializer;

class MongoDBDriver
{
    public function serialize($node)
    {
        $serializer = new MongoDBSerializer();
        return $serializer->serialize($node);
    }
    public function parse(array $aggregate)
    {
        $parser = new Parser\MongoDBParser();
        return $parser->parseAggregate($aggregate);
    }
}
