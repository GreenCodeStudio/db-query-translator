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
        var_dump($result);
        FunQuery::create($result)->each(fn($item) => var_dump($item));
        FunQuery::create($result)->map(fn(BSONDocument $item) => $item->getArrayCopy())->toArray();
    }
}
