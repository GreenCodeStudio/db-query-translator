<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\SQLite\Parser;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Parser\AbstractSqlParser;

class SQLiteParser  extends AbstractSqlParser
{
    public function parse()
    {
        if($this->isKeyword('SELECT')) {
            return $this->readSelect();
        }else{
            $this->throw('Keyword expected');
        }
    }
}
