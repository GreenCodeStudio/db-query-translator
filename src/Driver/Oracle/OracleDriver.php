<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\Oracle;
class OracleDriver
{
    public function serialize($node)
    {
        $serializer = new Serializer\OracleSerializer();
        return $serializer->serialize($node);
    }
}
