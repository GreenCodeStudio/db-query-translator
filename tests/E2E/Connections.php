<?php

namespace Mkrawczyk\DbQueryTranslator\Tests\E2E;

use Mkrawczyk\DbQueryTranslator\Driver\MySql\MySqlDriver;

class Connections
{
    public static function getConnections()
    {
        return [
            'mysql' => (object)[
                'driver' => new MySqlDriver(),
                'connection' => new \PDO('mysql:host=mysql;dbname=test_db', 'root', 'root')
            ]
        ];
    }
}
