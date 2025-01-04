<?php

namespace Mkrawczyk\DbQueryTranslator\Tests\Integrational;

use PHPUnit\Framework\TestCase;

class CrossTest extends TestCase
{
    public function getQueries()
    {
        return [[
            'MySql' =>
                [
                    "SELECT * FROM `example`",
                    "SELECT * FROM example"
                ],
            'SqlServer' =>
                [
                    "SELECT * FROM [example]",
                    "SELECT * FROM example"
                ]
        ],[
            'MySql' =>
                [
                    "SELECT 2+2 as four",
                    "SELECT 2   +2 as four"
                ],
            'SqlServer' =>
                [
                    "SELECT 2+2 as four",
                    "SELECT 2   +2 as four"
                ]
        ],

            ];
    }

    public function getDrivers()
    {
        return
            [
                'MySql' => new \Mkrawczyk\DbQueryTranslator\Driver\MySql\MySqlDriver(),
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
//    public function testMongo()
//    {
//        $mongoConnection = new \Mkrawczyk\DbQueryTranslator\Tests\E2E\MongoConnection('mongodb://localhost:27017');
//        $resp=$mongoConnection->query([
//            ['$project' => ['name' =>  ['$convert'=>['input'=>3, 'to'=>'int']]]],
//        ]);
//        var_dump($resp);
//    }
}
