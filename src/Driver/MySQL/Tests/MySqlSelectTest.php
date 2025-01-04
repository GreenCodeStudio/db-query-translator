<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\MySQL\Tests;

use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;
use PHPUnit\Framework\TestCase;

class MySqlSelectTest extends TestCase
{
    public function testSelectAll()
    {
        $driver = new \Mkrawczyk\DbQueryTranslator\Driver\MySQL\MySqlDriver();
        $sql = "SELECT * FROM `example`";
        $parsed = $driver->parse($sql);

        $this->assertInstanceOf(Select::class, $parsed);
        $this->assertCount(1, $parsed->columns);
        $this->assertInstanceOf(\Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectAll::class, $parsed->columns[0]);
        $this->assertInstanceOf(\Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table::class, $parsed->from);
        $this->assertEquals('example', $parsed->from->tableName);

        $serialized = $driver->serialize($parsed);

        $this->assertEquals($sql, $serialized);
    }
}
