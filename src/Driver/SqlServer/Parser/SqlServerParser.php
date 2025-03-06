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

    protected function getIdentifierQuoteRegexpStart():?string
    {
        return '/\[/';
    }
    protected function getIdentifierQuoteRegexpEnd():?string
    {
        return '/\]/';
    }


    protected function parseLimit()
    {
        $this->throw('LIMIT not supported in this dialect');
    }

    protected function parseOffset()
    {
        $this->skipKeyword('OFFSET');
        $this->skipWhitespace();
        $offset = (int)$this->readUntill('/[^0-9]/');
        $this->skipWhitespace();
        $this->skipKeyword('ROWS');
        return $offset;
    }
    protected function parseFetch()
    {
        $this->skipKeyword('FETCH');
        $this->skipWhitespace();
        $this->skipKeyword('NEXT');
        $this->skipWhitespace();
        $limit = (int)$this->readUntill('/[^0-9]/');
        $this->skipWhitespace();
        $this->skipKeyword('ROWS');
        $this->skipWhitespace();
        $this->skipKeyword('ONLY');
        return $limit;
    }
}
