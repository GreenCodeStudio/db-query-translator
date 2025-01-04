<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Serializer;

use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectAll;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;

abstract class AbstractSqlSerializer
{
    public function serialize($node): string
    {
        if ($node instanceof Select) {
            $ret = 'SELECT ';
            $first = true;
            foreach ($node->columns as $column) {
                if (!$first) {
                    $ret .= ', ';
                }
                $first = false;
                $ret .= $this->serialize($column);
            }
            $ret .= ' FROM '.$this->serialize($node->from);
            return $ret;
        } else if ($node instanceof SelectAll) {
            return '*';
        } else if ($node instanceof Table) {
            return $node->tableName;
        }else{
            throw new \Exception('Unknown node type');
        }
    }
}
