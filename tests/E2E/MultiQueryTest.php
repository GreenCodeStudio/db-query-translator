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
            $stmt = $originalConnection->pdo->query($sql);
            $results[] = $stmt->fetchAll();
            var_dump($results);
            foreach ($connections as $targetName => $targetConnection) {
                $parsed = $originalConnection->driver->parse($sql);
                $sql2 = $targetConnection->driver->serialize($parsed);
                echo $targetName.': '.$sql2.PHP_EOL;
                $stmt = $targetConnection->pdo->query($sql2);
                $results[] = $stmt->fetchAll();
            }
            $this->matchAnyPair($results);
        }
    }

    protected function matchAnyPair($results)
    {
        for ($i = 0; $i < count($results); $i++) {
            for ($j = $i + 1; $j < count($results); $j++) {
                $this->assertEquals($results[$i], $results[$j]);
            }
        }
    }
}
