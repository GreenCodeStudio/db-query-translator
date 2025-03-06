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
            ],

            [
                'MySql' => [
                    "SELECT * FROM abcd LIMIT 10,20",
                    "SELECT * FROM abcd LIMIT 20 OFFSET 10"
                ],
                'SqlServer' => [
                    "SELECT * FROM abcd OFFSET 10 ROWS FETCH NEXT 20 ROWS ONLY"
                ],
                'Postgres' => [
                    "SELECT * FROM abcd OFFSET 10 LIMIT 20"
                ],
                'SQLite' => [
                    "SELECT * FROM abcd LIMIT 20 OFFSET 10"
                ]
            ], [
                'MySql' => [
                    "SELECT * FROM document d JOIN document_history_item dhi ON d.id = dhi.document_id WHERE dhi.training_id = :trainingId",
                    "SELECT * FROM `document` d JOIN `document_history_item` dhi ON `d`.`id` = `dhi`.`document_id` WHERE `dhi`.`training_id` = :trainingId"
                ],
                'SqlServer' => [
                    "SELECT * FROM document d JOIN document_history_item dhi ON d.id = dhi.document_id WHERE dhi.training_id = :trainingId",
                    "SELECT * FROM [document] d JOIN [document_history_item] dhi ON [d].[id] = [dhi].[document_id] WHERE [dhi].[training_id] = :trainingId"
                ],
            ],
            [
                'MySql' => [
                    "SELECT p.id, p.name, p.internal_id FROM product p WHERE p.active = 1 ORDER BY p.internal_id",
                    "SELECT p.id,p.name,p.internal_id FROM product p WHERE p.active = 1 ORDER BY p.internal_id",
                    "SELECT `p`.`id`, `p`.`name`, `p`.`internal_id` FROM `product` p WHERE `p`.`active` = 1 ORDER BY `p`.`internal_id`"
                ],
                'SqlServer' => [
                    "SELECT p.id, p.name, p.internal_id FROM product p WHERE p.active = 1 ORDER BY p.internal_id",
                    "SELECT [p].[id], [p].[name], [p].[internal_id] FROM [product] p WHERE [p].[active] = 1 ORDER BY [p].[internal_id]"
                ],
                'Postgres' => [
                    "SELECT p.id, p.name, p.internal_id FROM product p WHERE p.active = 1 ORDER BY p.internal_id"
                ],
                'SQLite' => [
                    "SELECT p.id, p.name, p.internal_id FROM product p WHERE p.active = 1 ORDER BY p.internal_id"
                ]
            ], [
                'MySql' => [
                    "SELECT count(*) as cnt
                FROM training t
                LEFT JOIN rtraining_user rtu ON t.id = rtu.training_id AND rtu.user_id = :userId"

                ],
                'SqlServer' => [
                    "SELECT count(*) as cnt
                FROM training t
                LEFT JOIN rtraining_user rtu ON t.id = rtu.training_id AND rtu.user_id = :userId"
                ],
            ],
            [
                'MySql' => [
                    "SELECT sa.*, u.first_name as user_firstName, u.last_name as user_lastName
FROM supervisor_assingment sa
JOIN user u ON sa.user_id = u.id
WHERE sa.assigned_at < :to AND (sa.unassigned_at > :from OR sa.unassigned_at IS NULL) AND u.erased IS NULL
ORDER BY sa.assigned_at ASC"
                ]
            ],
            [
                /*'MySql' => [
                    "SELECT r.id, (
                    SELECT po2.id
                    FROM product_order po2
                        JOIN recipe ON recipe.id=po2.recipe_id
                        JOIN recipe_machine ON recipe_machine.recipe_id=recipe.id
                    WHERE recipe_machine.form_id=f.id AND po2.start < po.start
                    ORDER BY po2.finish DESC
                    LIMIT 1) as last_form_order_id
                FROM redevelopment r
                JOIN form f ON f.id=r.form_id
                JOIN product_order po ON po.id = r.product_order_id",
                ],
                'SqlServer' => [
                    "SELECT r.id, (
                    SELECT po2.id
                    FROM product_order po2
                        JOIN recipe ON recipe.id=po2.recipe_id
                        JOIN recipe_machine ON recipe_machine.recipe_id=recipe.id
                    WHERE recipe_machine.form_id=f.id AND po2.start < po.start
                    ORDER BY po2.finish DESC
                    OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY) as last_form_order_id"
                ]*/
            ],
            [
                'MySql' => ["SELECT DISTINCT id, name FROM example"],
                'SqlServer' => ["SELECT DISTINCT id, name FROM example"],
                'Postgres' => ["SELECT DISTINCT id, name FROM example"],
            ],
            [
                'MySql' => ["SELECT * FROM example ORDER BY a", "SELECT * FROM example ORDER BY a ASC"],
                'SqlServer' => ["SELECT * FROM example ORDER BY a", "SELECT * FROM example ORDER BY a ASC"],
                'Postgres' => ["SELECT * FROM example ORDER BY a", "SELECT * FROM example ORDER BY a ASC"],
            ],
            [
                'MySql' => ["SELECT * FROM example WHERE a > 5 AND (b < 10 OR c = 3)"],
                'SqlServer' => ["SELECT * FROM example WHERE a > 5 AND (b < 10 OR c = 3)"],
                'Postgres' => ["SELECT * FROM example WHERE a > 5 AND (b < 10 OR c = 3)"],
            ],
            [
                'MySql' => ["SELECT !a OR !b as x FROM example", "SELECT !a || !b as x FROM example", "SELECT NOT a OR NOT b as x FROM example", "SELECT (NOT a) OR (NOT b) as x FROM example"],

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
