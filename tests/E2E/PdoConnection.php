<?php
namespace Mkrawczyk\DbQueryTranslator\Tests\E2E;

use PDO;

class PdoConnection
{
    public PDO $pdo;

    public function __construct(string $dsn, string $username, string $password)
    {
        $this->pdo = new PDO($dsn, $username, $password);
    }
    public function query($sql)
    {
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
