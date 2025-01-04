<?php

namespace Mkrawczyk\DbQueryTranslator\Tests\Integrational;

use PHPUnit\Framework\TestCase;

class CrossTest extends TestCase
{
    public function getQueries()
    {
        return [[
            'MySQL' =>
                [
                    "SELECT * FROM `example`",
                    "SELECT * FROM example"
                ],
            'SqlServer' =>
                [
                    "SELECT * FROM [example]",
                    "SELECT * FROM example"
                ]
        ]];
    }

    public function getDrivers()
    {
        return
            [
                'MySQL' => new \Mkrawczyk\DbQueryTranslator\Driver\MySQL\MySqlDriver(),
                'SqlServer' => new \Mkrawczyk\DbQueryTranslator\Driver\SqlServer\SqlServerDriver()
            ];
    }

    public function testCross()
    {
        $drivers = $this->getDrivers();
        foreach ($this->getQueries() as $group) {
            $parsed = [];
            foreach ($group as $driverName => $queries) {
                $driver = $drivers[$driverName];
                foreach ($queries as $query) {
                    $parsed[] = $driver->parse($query);
                }
            }
            $this->checkEachPair($parsed);
        }
    }
    public function checkEachPair($parsed)
    {
        for($i = 0; $i < count($parsed); $i++){
            for($j = $i + 1; $j < count($parsed); $j++){
                $this->assertEquals($parsed[$i], $parsed[$j]);
            }
        }
    }
}
