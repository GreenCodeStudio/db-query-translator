<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\Postgres\Parser;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Parser\AbstractSqlParser;

class PostgresParser extends AbstractSqlParser
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
