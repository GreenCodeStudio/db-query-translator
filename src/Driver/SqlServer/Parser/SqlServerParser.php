<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\SqlServer\Parser;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Parser\AbstractSqlParser;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectColumn;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;

class SqlServerParser extends AbstractSqlParser
{
    public function __construct(string $code)
    {
        parent::__construct($code);
    }
    public function parse()
    {
        if($this->isKeyword('SELECT')) {
            return $this->readSelect();
        }else{
            $this->throw('Keyword expected');
        }
    }
    protected function readTable(){
        $this->skipWhitespace();

        if($this->isKeyword('[')) {
            $this->skipKeyword('[');
            $firstName = $this->readUntill('/]/');
            $this->skipKeyword(']');
        }else{
            $firstName=$this->readUntill('/\s/');
        }
        return new Table($firstName);
    }
    protected function getIdentifierQuoteRegexpStart():?string
    {
        return '/\[/';
    }
    protected function getIdentifierQuoteRegexpEnd():?string
    {
        return '/\]/';
    }
}
