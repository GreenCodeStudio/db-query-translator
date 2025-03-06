<?php
namespace Mkrawczyk\DbQueryTranslator\Driver\SqlServer\Tests;

use Mkrawczyk\DbQueryTranslator\Driver\SqlServer\SqlServerDriver;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;

class SqlServerSelectTest extends \PHPUnit\Framework\TestCase
{

    public function testOffset()
    {
        $driver = new SqlServerDriver();
        $sql = "SELECT * FROM document OFFSET 10 ROWS FETCH NEXT 20 ROWS ONLY";
        $sqlWanted = "SELECT * FROM [document] OFFSET 10 ROWS FETCH NEXT 20 ROWS ONLY";

        $parsed = $driver->parse($sql);

        $this->assertInstanceOf(Select::class, $parsed);
        $this->assertInstanceOf(\Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table::class, $parsed->from);
        $this->assertEquals('document', $parsed->from->tableName);
        $this->assertEquals(10, $parsed->offset);
        $this->assertEquals(20, $parsed->limit);


        $serialized = $driver->serialize($parsed);
        $this->assertEquals($sqlWanted, $serialized);
    }
}
