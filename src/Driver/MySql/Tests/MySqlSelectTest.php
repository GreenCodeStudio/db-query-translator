<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\MySql\Tests;

use Mkrawczyk\DbQueryTranslator\Driver\MySql\MySqlDriver;
use Mkrawczyk\DbQueryTranslator\Driver\SqlServer\SqlServerDriver;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Addition;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\BooleanAnd;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Comparison;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Equals;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Identifier;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Parameter;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectColumn;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PHPUnit\Framework\TestCase;

class MySqlSelectTest extends TestCase
{
    public function testSelectAll()
    {
        $driver = new MySqlDriver();
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
    public function testSelectExpressions()
    {

        $driver = new MySqlDriver();
        $sql = "SELECT 1+1, 2+2 as four";
        $sqlWanted = "SELECT 1 + 1 AS `1+1`, 2 + 2 AS `four`";
        $parsed = $driver->parse($sql);

        $this->assertInstanceOf(Select::class, $parsed);
        $this->assertCount(2, $parsed->columns);
        $this->assertInstanceOf(SelectColumn::class, $parsed->columns[0]);
        $this->assertInstanceOf(Addition::class, $parsed->columns[0]->expression);
        $this->assertEquals('1', $parsed->columns[0]->expression->left->value);
        $this->assertEquals('1', $parsed->columns[0]->expression->right->value);
        $this->assertEquals('1+1', $parsed->columns[0]->name);
        $this->assertInstanceOf(SelectColumn::class, $parsed->columns[1]);
        $this->assertInstanceOf(Addition::class, $parsed->columns[1]->expression);
        $this->assertEquals('2', $parsed->columns[1]->expression->left->value);
        $this->assertEquals('2', $parsed->columns[1]->expression->right->value);
        $this->assertEquals('four', $parsed->columns[1]->name);

        $this->assertNull($parsed->from);

        $serialized = $driver->serialize($parsed);
        $this->assertEquals($sqlWanted, $serialized);

    }
    public function testWhere()
    {
        $driver = new MySqlDriver();
        $sql = "SELECT one =1 FROM `example` WHERE one + 1 = 2";
        $sqlWanted = "SELECT `one` = 1 AS `one =1` FROM `example` WHERE `one` + 1 = 2";

        $parsed = $driver->parse($sql);

        $this->assertInstanceOf(Select::class, $parsed);
        $this->assertCount(1, $parsed->columns);
        $this->assertInstanceOf(SelectColumn::class, $parsed->columns[0]);
        $this->assertInstanceOf(Equals::class, $parsed->columns[0]->expression);
        $this->assertInstanceOf(Identifier::class, $parsed->columns[0]->expression->left);
        $this->assertEquals('one', $parsed->columns[0]->expression->left->name);
        $this->assertEquals('1', $parsed->columns[0]->expression->right->value);
        $this->assertEquals('one =1', $parsed->columns[0]->name);
        $this->assertInstanceOf(Equals::class, $parsed->where);
        $this->assertInstanceOf(Addition::class, $parsed->where->left);
        $this->assertEquals('one', $parsed->where->left->left->name);
        $this->assertEquals('1', $parsed->where->left->right->value);
        $this->assertEquals('2', $parsed->where->right->value);

        $serialized = $driver->serialize($parsed);
        $this->assertEquals($sqlWanted, $serialized);
    }
    public function testJoin()
    {
        $driver = new MySqlDriver();
        $sql = "SELECT * FROM document d JOIN document_history_item dhi ON d.id = dhi.document_id WHERE dhi.training_id = :trainingId";
        $sqlWanted = "SELECT * FROM `document` `d` INNER JOIN `document_history_item` `dhi` ON `d`.`id` = `dhi`.`document_id` WHERE `dhi`.`training_id` = :trainingId";

        $parsed = $driver->parse($sql);

        $this->assertInstanceOf(Select::class, $parsed);
        $this->assertInstanceOf(\Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table::class, $parsed->from);
        $this->assertEquals('document', $parsed->from->tableName);
        $this->assertEquals('d', $parsed->from->alias);
        $this->assertCount(1, $parsed->join);
        $this->assertInstanceOf(\Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table::class, $parsed->join[0]->table);
        $this->assertEquals('document_history_item', $parsed->join[0]->table->tableName);
        $this->assertEquals('dhi', $parsed->join[0]->table->alias);
        $this->assertInstanceOf(Equals::class, $parsed->join[0]->on);
        $this->assertInstanceOf(Identifier::class, $parsed->join[0]->on->left);
        $this->assertEquals('id', $parsed->join[0]->on->left->name);
        $this->assertEquals('d', $parsed->join[0]->on->left->table);
        $this->assertInstanceOf(Identifier::class, $parsed->join[0]->on->right);
        $this->assertEquals('document_id', $parsed->join[0]->on->right->name);
        $this->assertEquals('dhi', $parsed->join[0]->on->right->table);

        $this->assertInstanceOf(Equals::class, $parsed->where);
        $this->assertInstanceOf(Identifier::class, $parsed->where->left);
        $this->assertEquals('training_id', $parsed->where->left->name);
        $this->assertEquals('dhi', $parsed->where->left->table);
        $this->assertInstanceOf(Parameter::class, $parsed->where->right);
        $this->assertEquals('trainingId', $parsed->where->right->name);

        $serialized = $driver->serialize($parsed);
        $this->assertEquals($sqlWanted, $serialized);
    }

    public function testOffset()
    {
        $driver = new MySqlDriver();
        $sql = "SELECT * FROM document LIMIT 10,20";
        $sqlWanted = "SELECT * FROM `document` LIMIT 20 OFFSET 10";

        $parsed = $driver->parse($sql);

        $this->assertInstanceOf(Select::class, $parsed);
        $this->assertInstanceOf(\Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table::class, $parsed->from);
        $this->assertEquals('document', $parsed->from->tableName);
        $this->assertEquals(10, $parsed->offset);
        $this->assertEquals(20, $parsed->limit);


        $serialized = $driver->serialize($parsed);
        $this->assertEquals($sqlWanted, $serialized);
    }
    public function testOrderBy()
    {
        $driver = new MySqlDriver();
        $sql = "SELECT * FROM `document` ORDER BY `id` ASC, `name` DESC";

        $parsed = $driver->parse($sql);

        $this->assertInstanceOf(Select::class, $parsed);
        $this->assertEquals($parsed->orderBy[0]->expression->name, 'id');
        $this->assertEquals($parsed->orderBy[0]->descending, false);
        $this->assertEquals($parsed->orderBy[1]->expression->name, 'name');
        $this->assertEquals($parsed->orderBy[1]->descending, true);




        $serialized = $driver->serialize($parsed);
        $this->assertEquals($sql, $serialized);
    }
    public function testSelectName()
    {
        $driver = new MySqlDriver();
        $sql="SELECT p.id, p.`name`, `p`.internal_id FROM product p";
        $parsed = $driver->parse($sql);

        $this->assertInstanceOf(Select::class, $parsed);
        $this->assertEquals('id', $parsed->columns[0]->name);
        $this->assertEquals('name', $parsed->columns[1]->name);
        $this->assertEquals('internal_id', $parsed->columns[2]->name);
    }
    public function testRealWorldExample001()
    {
        $driver = new MySqlDriver();
        $sql="SELECT * FROM notification WHERE id_user = ? AND expires >= ? ORDER BY stamp DESC";
        $sqlWanted = "SELECT * FROM `notification` WHERE `id_user` = ? AND `expires` >= ? ORDER BY `stamp` DESC";
        $parsed = $driver->parse($sql);

        $this->assertInstanceOf(Select::class, $parsed);
        $this->assertInstanceOf(BooleanAnd::class, $parsed->where);
        $this->assertInstanceOf(Equals::class, $parsed->where->left);
        $this->assertInstanceOf(Identifier::class, $parsed->where->left->left);
        $this->assertEquals('id_user', $parsed->where->left->left->name);
        $this->assertInstanceOf(Parameter::class, $parsed->where->left->right);
        $this->assertInstanceOf(Comparison::class, $parsed->where->right);
        $this->assertFalse($parsed->where->right->lessThan);
        $this->assertTrue($parsed->where->right->orEqual);
        $this->assertInstanceOf(Identifier::class, $parsed->where->right->left);
        $this->assertEquals('expires', $parsed->where->right->left->name);
        $this->assertInstanceOf(Parameter::class, $parsed->where->right->right);

        $serialized = $driver->serialize($parsed);
        $this->assertEquals($sqlWanted, $serialized);

    }
    public function testNoSpaces()
    {
        $driver = new MySqlDriver();
        $sql = "SELECT id,name\t,value\nFROM\tproduct";
        $sqlWanted = "SELECT `id` AS `id`, `name` AS `name`, `value` AS `value` FROM `product`";
        $parsed = $driver->parse($sql);

        $this->assertInstanceOf(Select::class, $parsed);
        $this->assertEquals('id', $parsed->columns[0]->name);
        $this->assertEquals('name', $parsed->columns[1]->name);
        $this->assertEquals('value', $parsed->columns[2]->name);
        $this->assertInstanceOf(\Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table::class, $parsed->from);
        $this->assertEquals('product', $parsed->from->tableName);

        $serialized = $driver->serialize($parsed);
        $this->assertEquals($sqlWanted, $serialized);

    }

    public function testSubQuery(){
        $driver = new MySqlDriver();
        $sql = "SELECT (SELECT 1 as one) as one";
        $sqlWanted = "SELECT (SELECT 1 AS `one`) AS `one`";
        $parsed = $driver->parse($sql);

        $this->assertInstanceOf(Select::class, $parsed);
        $this->assertInstanceOf(SelectColumn::class, $parsed->columns[0]);
        $this->assertInstanceOf(Select::class, $parsed->columns[0]->expression);
        $this->assertInstanceOf(SelectColumn::class, $parsed->columns[0]->expression->columns[0]);
        $this->assertInstanceOf(\Mkrawczyk\DbQueryTranslator\Nodes\Expression\Literal::class, $parsed->columns[0]->expression->columns[0]->expression);
        $this->assertEquals('1', $parsed->columns[0]->expression->columns[0]->expression->value);
        $this->assertEquals('one', $parsed->columns[0]->name);

        $serialized = $driver->serialize($parsed);
        $this->assertEquals($sqlWanted, $serialized);

    }
}
