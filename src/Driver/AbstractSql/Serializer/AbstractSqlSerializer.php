<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Serializer;

use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Addition;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Equals;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Identifier;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Literal;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Parameter;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectAll;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectColumn;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;

abstract class AbstractSqlSerializer
{
    public function serialize($node): string
    {
        if ($node === null) {
            throw new \Exception('Node is null');
        }
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
            if ($node->from) {
                $ret .= ' FROM '.$this->serialize($node->from);
            }
            foreach ($node->join as $join) {
                $ret .= ' '.$join->type.' JOIN '.$this->serialize($join->table).' ON '.$this->serialize($join->on);
            }
            if ($node->where) {
                $ret .= ' WHERE '.$this->serialize($node->where);
            }
            $ret .= $this->serializeLimit($node);
            return $ret;
        } else if ($node instanceof SelectAll) {
            return '*';
        } else if ($node instanceof SelectColumn) {
            return $this->serialize($node->expression).' AS '.$node->name;
        } else if ($node instanceof Table) {
            return $node->tableName;
        } else if ($node instanceof Addition) {
            return $this->serialize($node->left).' + '.$this->serialize($node->right);
        } else if ($node instanceof Equals) {
            return $this->serialize($node->left).' = '.$this->serialize($node->right);
        } else if ($node instanceof Identifier) {
            if ($node->table) {
                return $node->table.'.'.$node->name;
            } else {
                return $node->name;
            }
        } else if ($node instanceof Literal) {
            if ($node->type == 'int') {
                return (string)$node->value;
            } else {
                throw new \Exception('Unknown literal type '.$node->type);
            }
        } else if ($node instanceof Parameter) {
            return ':'.$node->name;
        } else {
            throw new \Exception('Unknown node type '.get_class($node));
        }
    }

    protected function serializeLimit(Select $select)
    {
        $ret = '';
        if ($select->limit !== null) {
            $ret .= ' LIMIT '.$select->limit;
        }
        if ($select->offset !== null) {
            $ret .= ' OFFSET '.$select->offset;
        }
        return $ret;
    }
}
