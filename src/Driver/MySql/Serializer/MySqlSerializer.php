<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\MySql\Serializer;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Serializer\AbstractSqlSerializer;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Identifier;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectAll;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectColumn;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;

class MySqlSerializer extends AbstractSqlSerializer
{
    public function serialize($node, $parentExitLevel = 0): string
    {

        $exitLevel = self::$exitLevels[get_class($node)] ?? 100;
        if ($node instanceof Table) {
            if ($node->alias != $node->tableName) {
                return '`'.$node->tableName.'` `'.$node->alias.'`';
            } else {
                return '`'.$node->tableName.'`';
            }
        } else if ($node instanceof SelectColumn) {
            return $this->serialize($node->expression, $exitLevel).' AS `'.$node->name.'`';
        } else if ($node instanceof Identifier) {
            if ($node->table) {
                return '`'.$node->table.'`.`'.$node->name.'`';
            } else {
                return '`'.$node->name.'`';
            }
        } else {
            return parent::serialize($node, $parentExitLevel);
        }
    }

}
