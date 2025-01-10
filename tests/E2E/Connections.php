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
                'connection' => new PdoConnection('mysql:host=127.0.0.1;dbname=test_db', 'root', 'root')
            ],
            'SqlServer' => (object)[
                'driver' => new \Mkrawczyk\DbQueryTranslator\Driver\SqlServer\SqlServerDriver(),
                'connection' => new PdoConnection('sqlsrv:Server=localhost;Database=master', 'sa', 'RootRoot1')
            ],
            'MongoDB' => (object)[
                'driver' => new \Mkrawczyk\DbQueryTranslator\Driver\MongoDB\MongoDBDriver(),
                'connection' => new MongoConnection('mongodb://localhost:27017')
            ],
            'Oracle' => (object)[
                'driver' => new \Mkrawczyk\DbQueryTranslator\Driver\Oracle\OracleDriver(),
                'connection' => new PdoConnection('oci:dbname=//localhost:1521/xe', 'system', 'yourStrong(!)Password')
            ],
            'Postgresql' => (object)[
                'driver' => new \Mkrawczyk\DbQueryTranslator\Driver\Postgres\PostgresDriver(),
                'connection' => new PdoConnection('pgsql:host=localhost;port=5432;dbname=test_db', 'user', 'user')
            ],
        ];
    }
}
