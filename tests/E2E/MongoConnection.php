<?php

namespace Mkrawczyk\DbQueryTranslator\Tests\E2E;

use MKrawczyk\FunQuery\FunQuery;
use MongoDB\Model\BSONDocument;

class MongoConnection
{
    public function __construct()
    {
        $this->mongo = new \MongoDB\Client("mongodb://localhost:27017");

        if (FunQuery::from($this->mongo->test_db->listCollectionNames())->some(fn($collection) => $collection === 'test_collection')) {
            $this->mongo->test_db->dropCollection('test_collection');
        }

        $this->mongo->test_db->createCollection('test_collection');
        $this->mongo->test_db->test_collection->insertOne([]);
    }

    public function query($aggregate)
    {
        $collection = $this->mongo->test_db->test_collection;
        $result= $collection->aggregate($aggregate)->toArray();
        var_dump("Mongo Query Result");
        var_dump($result);
        var_dump(json_decode(json_encode($result), true));
        return json_decode(json_encode($result), true);
    }
}
