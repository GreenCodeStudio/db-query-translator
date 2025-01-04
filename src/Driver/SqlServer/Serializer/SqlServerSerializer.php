<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\SqlServer\Serializer;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Serializer\AbstractSqlSerializer;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectColumn;


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
}
