<?php

namespace Mkrawczyk\DbQueryTranslator\Tests\E2E;

use PHPUnit\Framework\TestCase;

class MultiQueryTest extends TestCase
{
    public function getQueries()
    {
        return [
            [
                "SELECT 2+2 as four",
                "MySql"
            ],
        ];
    }

    public function testMultiQuery()
    {
        $connections = Connections::getConnections();

        foreach ($this->getQueries() as [$sql, $originalDialect]) {
            $results = [];
            $originalConnection = $connections[$originalDialect];
            $results[] =  $originalConnection->connection->query($sql);
            var_dump($results);
            foreach ($connections as $targetName => $targetConnection) {
                $parsed = $originalConnection->driver->parse($sql);
                $sql2 = $targetConnection->driver->serialize($parsed);
                echo $targetName.': '.$sql2.PHP_EOL;
                $results[] =  $targetConnection->connection->query($sql2);
            }
            $this->matchAnyPair($results, json_encode($sql, $originalDialect));
        }
    }

    protected function matchAnyPair($results, string $message)
    {
        for ($i = 0; $i < count($results); $i++) {
            for ($j = $i + 1; $j < count($results); $j++) {
                $this->assertEquals($results[$i], $results[$j], $message);
            }
        }
    }
}
