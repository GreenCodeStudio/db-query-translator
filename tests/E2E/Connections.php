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
                'connection' => new \PDO('mysql:host=mysql;dbname=test_db', 'root', 'root')
            ],
            'SqlServer'=> (object)[
                'driver' => new \Mkrawczyk\DbQueryTranslator\Driver\SqlServer\SqlServerDriver(),
                'connection' => new \PDO('sqlsrv:Server=sqlserver;Database=test_db', 'sa', 'RootRoot1')
            ]
        ];
    }
}
