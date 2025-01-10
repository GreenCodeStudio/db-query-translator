<?php

namespace Mkrawczyk\DbQueryTranslator\Tests\Integrational;

use PHPUnit\Framework\TestCase;

class CrossTest extends TestCase
{
    public function getQueries()
    {
        return [
            [
                'MySql' =>
                    [
                        "SELECT * FROM `example`",
                        "SELECT * FROM example"
                    ],
                'SqlServer' =>
                    [
                        "SELECT * FROM [example]",
                        "SELECT * FROM example"
                    ],
            ],
            [
                'MySql' =>
                    [
                        "SELECT 2+2 as four",
                        "SELECT 2   +2 as four"
                    ],
                'SqlServer' =>
                    [
                        "SELECT 2+2 as four",
                        "SELECT 2   +2 as four"
                    ],
                'MongoDB' =>
                    [
                        [
                            '$project' => ['four' => ['$add' => [2, 2]], '_id' => 0]
                        ]
                    ]
            ],
            [
                'MySql' =>
                    [
                        "SELECT * FROM `example` WHERE `id` = 1",
                        "SELECT * FROM example WHERE id = 1"
                    ],
                'SqlServer' =>
                    [
                        "SELECT * FROM [example] WHERE [id] = 1",
                        "SELECT * FROM example WHERE id = 1"
                    ],
            ],
//            [
//                'MySql'=>[
//                    'SELECT one, two + 3, 3-4, 5*6, 7/8, `nine` %10 FROM example',
//                ],
//                'SqlServer'=>[
//                    'SELECT one, two + 3, 3-4, 5*6, 7/8, [nine] %10 FROM example',
//                ],
//            ],
//            [
//                'MySql'=>[
//                    'SELECT * FROM example WHERE id = 1 AND name = "John"',
//                ],
//                'SqlServer'=>[
//                    'SELECT * FROM example WHERE id = 1 AND name = \'John\'',
//                ],
//            ]

        ];
    }

    public function getDrivers()
    {
        return
            [
                'MySql' => new \Mkrawczyk\DbQueryTranslator\Driver\MySql\MySqlDriver(),
                'SqlServer' => new \Mkrawczyk\DbQueryTranslator\Driver\SqlServer\SqlServerDriver(),
                'MongoDB' => new \Mkrawczyk\DbQueryTranslator\Driver\MongoDB\MongoDBDriver(),
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
            $this->checkEachPair($parsed, json_encode($group));
        }
    }

    public function checkEachPair($parsed, $message)
    {
        for ($i = 0; $i < count($parsed); $i++) {
            for ($j = $i + 1; $j < count($parsed); $j++) {
                $this->assertEquals($parsed[$i], $parsed[$j], $message);
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
