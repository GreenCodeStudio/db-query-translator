<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\SqLite;
class SqLiteDriver
{
    public function serialize($node)
    {
        $serializer = new Serializer\SqLiteSerializer();
        return $serializer->serialize($node);
    }
}
