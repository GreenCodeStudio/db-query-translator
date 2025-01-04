<?php

namespace Mkrawczyk\DbQueryTranslator\Tests\E2E;

use Mkrawczyk\DbQueryTranslator\Driver\MySql\MySqlDriver;

class Connections
{
    public static function getConnections()
    {
        return [
            'MySql' => (object)[
                'driver' => new MySqlDriver(),
                'pdo' => new \PDO('mysql:host=127.0.0.1;dbname=test_db', 'root', 'root')
            ],
            'SqlServer'=> (object)[
                'driver' => new \Mkrawczyk\DbQueryTranslator\Driver\SqlServer\SqlServerDriver(),
                'pdo' => new \PDO('sqlsrv:Server=localhost;Database=master', 'sa', 'RootRoot1')
            ]
        ];
    }
}
