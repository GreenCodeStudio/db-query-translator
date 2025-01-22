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
                'SQLite' =>
                    [
                        "SELECT * FROM example",
                    ],
                'Postgres' =>
                    [
                        "SELECT * FROM example",
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
            [
                'MySql' => [
                    'SELECT one, two + 3, 3-4, 5*6, 7/8, `nine` %10 as ninemod FROM example',
                ],
                'SqlServer' => [
                    'SELECT one, two + 3, 3-4, 5*6, 7/8, [nine] %10 as ninemod FROM example',
                ],
            ],
            [
                'MySql' => [
                    'SELECT * FROM example WHERE id = 1 AND name = "John"',
                ],
                'SqlServer' => [
                    'SELECT * FROM example WHERE id = 1 AND name = \'John\'',
                ],
            ]            ,
            [
                'MySql' => [
                    "SELECT * FROM document d JOIN document_history_item dhi ON d.id = dhi.document_id WHERE dhi.training_id = :trainingId",
                    "SELECT * FROM `document` d JOIN `document_history_item` dhi ON `d`.`id` = `dhi`.`document_id` WHERE `dhi`.`training_id` = :trainingId"
                ],
                'SqlServer' => [
                    "SELECT * FROM document d JOIN document_history_item dhi ON d.id = dhi.document_id WHERE dhi.training_id = :trainingId",
                    "SELECT * FROM [document] d JOIN [document_history_item] dhi ON [d].[id] = [dhi].[document_id] WHERE [dhi].[training_id] = :trainingId"
                ],
            ]
        ];
    }

    public function getDrivers()
    {
        return
            [
                'MySql' => new \Mkrawczyk\DbQueryTranslator\Driver\MySql\MySqlDriver(),
                'SqlServer' => new \Mkrawczyk\DbQueryTranslator\Driver\SqlServer\SqlServerDriver(),
                'MongoDB' => new \Mkrawczyk\DbQueryTranslator\Driver\MongoDB\MongoDBDriver(),
                'Postgres' => new \Mkrawczyk\DbQueryTranslator\Driver\Postgres\PostgresDriver(),
                'SQLite' => new \Mkrawczyk\DbQueryTranslator\Driver\SQLite\SQLiteDriver(),
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
                    $parsed[] = [$driverName, $query, $driver->parse($query)];
                }
            }
            $this->checkEachPair($parsed);
        }
    }

    public function checkEachPair($parsed)
    {
        for ($i = 0; $i < count($parsed); $i++) {
            for ($j = $i + 1; $j < count($parsed); $j++) {
                $this->assertEquals($parsed[$i][2], $parsed[$j][2], json_encode([$parsed[$i][0], $parsed[$i][1], $parsed[$j][0], $parsed[$j][1]]));
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
