<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\AbstractSQL\Parser;

abstract class AbstractSQLParser
{

    protected string $code;
    protected int $position = 0;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    protected function throw(string $reason)
    {
        throw new \Exception("Parser error on position ".$this->position.": ".$reason);
    }



    protected function skipWhitespace(){
        while($this->position < strlen($this->code) && ctype_space($this->code[$this->position])){
            $this->position++;
        }
    }
    protected function isKeyword(string $keyword): bool{
        if($this->position + strlen($keyword) > strlen($this->code)){
            return false;
        }
        return strtolower(substr($this->code, $this->position, strlen($keyword))) === strtolower($keyword);
    }
    protected function skipKeyword(string ...$keywords){
        foreach($keywords as $keyword){
            if($this->isKeyword($keyword)){
                $this->position += strlen($keyword);
                return;
            }
        }
        $this->throw('Expected keyword '.implode(', ', $keywords));
    }

    protected  function endOfCode(): bool{
        return $this->position >= strlen($this->code);
    }
    protected function readUntill(string $regexp){
        $ret = '';
        while($this->position < strlen($this->code) && !preg_match($regexp, $this->code[$this->position])){
            $ret .= $this->code[$this->position];
            $this->position++;
        }
        return $ret;
    }
    protected abstract function readSelect();
}
