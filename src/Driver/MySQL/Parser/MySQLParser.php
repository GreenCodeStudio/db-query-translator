<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\MySQL\Parser;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSQL\Parser\AbstractSQLParser;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;

class MySQLParser extends AbstractSQLParser
{
    public function __construct(string $code)
    {
        parent::__construct($code);
    }
    public function parse()
    {
        if($this->isKeyword('SELECT')) {
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
                if($this->isKeyword(',')){
                    $this->skipKeyword(',');
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
        }else{
            $this->throw('Keyword expected');
        }
    }
    private function readTable(){
        $this->skipWhitespace();

        if($this->isKeyword('`')) {
            $this->skipKeyword('`');
            $firstName = $this->readUntill('/`/');
            $this->skipKeyword('`');
        }else{
            $firstName=$this->readUntill('/\s/');
        }
        return new Table($firstName);
    }

}
