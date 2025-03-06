<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\SqlServer\Serializer;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Serializer\AbstractSqlSerializer;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectColumn;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;


class SqlServerSerializer extends AbstractSqlSerializer
{
    public function serialize($node): string
    {
        if ($node instanceof Table) {
            return '['.$node->tableName.']';
        } else if ($node instanceof SelectColumn) {
            return $this->serialize($node->expression).' AS ['.$node->name.']';
        } else {
            return parent::serialize($node);
        }
    }

    protected function serializeLimit(Select $select)
    {
        $ret = '';

        if ($select->offset !== null) {
            $ret .= ' OFFSET '.$select->offset.' ROWS';
        }
        if ($select->limit !== null) {
            $ret .= ' FETCH NEXT '.$select->limit.' ROWS ONLY';
        }
        return $ret;
    }
}
