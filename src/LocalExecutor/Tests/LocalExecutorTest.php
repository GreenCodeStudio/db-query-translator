<?php

namespace Mkrawczyk\DbQueryTranslator\LocalExecutor\Tests;

use Mkrawczyk\DbQueryTranslator\LocalExecutor\LocalDB;
use Mkrawczyk\DbQueryTranslator\LocalExecutor\LocalExecutor;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Identifier;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Literal;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectAll;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectColumn;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;

class LocalExecutorTest extends \PHPUnit\Framework\TestCase
{
    private function getLocalDB(): LocalDB
    {
        $ret = new LocalDB();
        $ret->add('sample1', [['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Doe'], ['id' => 3, 'name' => 'Smith']]);
        return $ret;
    }

    public function testSelectAll()
    {
        $select = new Select();
        $select->columns[] = new SelectAll();
        $select->from = new Table('sample1');
        $result = LocalExecutor::ExecuteQuery($this->getLocalDB(), $select)->toArray();

        $wanted = [(object)['id' => 1, 'name' => 'John'], (object)['id' => 2, 'name' => 'Doe'], (object)['id' => 3, 'name' => 'Smith']];

        $this->assertEquals($wanted, $result);
    }

    public function testSelectSome()
    {

        $select = new Select();
        $select->columns[] = new SelectColumn('name1', new Identifier('name'));
        $select->columns[] = new SelectColumn('two', new Literal('int', 2));
        $select->from = new Table('sample1');
        $result = LocalExecutor::ExecuteQuery($this->getLocalDB(), $select)->toArray();

        $wanted = [(object)['name1' => 'John', 'two' => 2], (object)['name1' => 'Doe', 'two' => 2], (object)['name1' => 'Smith', 'two' => 2]];

        $this->assertEquals($wanted, $result);
    }
}
