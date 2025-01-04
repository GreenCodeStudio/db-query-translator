<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\SQLServer\Parser;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSQL\Parser\AbstractSqlParser;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
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
    protected function readSelect()
    {
        $this->skipKeyword('SELECT');
        $this->skipWhitespace();
        $ret=new Select();

        while(!$this->endOfCode()){
            if($this->isKeyword('*')){
                $ret->columns[] = new \Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectAll();
                $this->skipKeyword('*');
            }else{
                $this->throw('Not implemented');
            }
            $this->skipWhitespace();
            if($this->isKeyword('[')){
                $this->skipKeyword(']');
                $this->skipWhitespace();
            }else{
                break;
            }
        }
        if($this->isKeyword('FROM')){
            $this->skipKeyword('FROM');
            $this->skipWhitespace();
            $ret->from = $this->readTable();
        }
        return $ret;
    }
    private function readTable(){
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

}
