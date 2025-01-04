<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\MySql\Serializer;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Serializer\AbstractSqlSerializer;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectAll;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;

class MySqlSerializer extends AbstractSqlSerializer
{
    public function serialize($node): string
    {
        if ($node instanceof Table) {
            return '`'.$node->tableName.'`';
        }else{
            return parent::serialize($node);
        }
    }
}
