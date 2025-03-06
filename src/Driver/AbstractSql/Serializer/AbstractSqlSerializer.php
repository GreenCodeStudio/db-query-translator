<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Serializer;

use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Addition;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\BooleanAnd;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\BooleanNot;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\BooleanOr;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Comparison;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Division;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Equals;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Identifier;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Literal;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Multiplication;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Parameter;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Subtraction;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectAll;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectColumn;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;

abstract class AbstractSqlSerializer
{
    static protected $exitLevels = [
        Select::class => 0,
        SelectColumn::class=>1,//tmp
        BooleanOr::class => 1,
        BooleanAnd::class => 1,
        Equals::class => 2,
        Comparison::class => 2,
        Addition::class => 3,
        Subtraction::class => 3,
        Multiplication::class => 4,
        Division::class => 4,
        BooleanNot::class => 5
    ];

    public function serialize($node, $parentExitLevel = 0): string
    {
        $exitLevel = self::$exitLevels[get_class($node)] ?? 100;
        $ret='';
        if($exitLevel < $parentExitLevel){
            $ret .= '(';
        }
        if ($node === null) {
            throw new \Exception('Node is null');
        }
        if ($node instanceof Select) {
            $ret .= 'SELECT ';
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
            if (!empty($node->orderBy)) {
                $ret .= ' ORDER BY ';
                $first = true;
                foreach ($node->orderBy as $orderBy) {
                    if (!$first) {
                        $ret .= ', ';
                    }
                    $first = false;
                    $ret .= $this->serialize($orderBy->expression);
                    $ret .= $orderBy->descending ? ' DESC' : ' ASC';
                }
            }
            $ret .= $this->serializeLimit($node);
        } else if ($node instanceof SelectAll) {
            $ret .=  '*';
        } else if ($node instanceof SelectColumn) {
            $ret .=  $this->serialize($node->expression, $exitLevel).' AS '.$node->name;
        } else if ($node instanceof Table) {
            $ret .=  $node->tableName;
        } else if ($node instanceof Addition) {
            $ret .=  $this->serialize($node->left, $exitLevel).' + '.$this->serialize($node->right, $exitLevel);
        } else if ($node instanceof Equals) {
            $ret .=  $this->serialize($node->left, $exitLevel).' = '.$this->serialize($node->right, $exitLevel);
        } else if ($node instanceof BooleanAnd) {
            $ret .=  $this->serialize($node->left, $exitLevel).' AND '.$this->serialize($node->right, $exitLevel);
        } else if ($node instanceof BooleanOr) {
            $ret .=  $this->serialize($node->left, $exitLevel).' OR '.$this->serialize($node->right, $exitLevel);
        } else if ($node instanceof BooleanNot) {
            $ret .=  'NOT '.$this->serialize($node->expression, $exitLevel);
        } else if ($node instanceof Comparison) {
            $ret .=  $this->serialize($node->left, $exitLevel).' '.($node->lessThan ? '<' : '>').($node->orEqual ? '=' : '').' '.$this->serialize($node->right, $exitLevel);
        } else if ($node instanceof Identifier) {
            if ($node->table) {
                $ret .=  $node->table.'.'.$node->name;
            } else {
                $ret .=  $node->name;
            }
        } else if ($node instanceof Literal) {
            if ($node->type == 'int') {
                $ret .=  (string)$node->value;
            } else {
                throw new \Exception('Unknown literal type '.$node->type);
            }
        } else if ($node instanceof Parameter) {
            if (empty($node->name))
                $ret .=  '?';
            else
                $ret .=  ':'.$node->name;
        } else {
            throw new \Exception('Unknown node type '.get_class($node));
        }
        if($exitLevel < $parentExitLevel){
            $ret .= ')';
        }
        return $ret;
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
