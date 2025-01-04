<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\SqlServer\Serializer;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Serializer\AbstractSqlSerializer;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;


class SqlServerSerializer extends AbstractSqlSerializer
{
    public function serialize($node): string
    {
        if ($node instanceof Table) {
            return '['.$node->tableName.']';
        }else{
            return parent::serialize($node);
        }
    }
}
