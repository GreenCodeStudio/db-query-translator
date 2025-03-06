<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Parser;

class ParserException extends \Exception
{
    public int $position;
    public string $fullCode;
    public string $reason;

    public function __construct(string $reason, int $position, string $fullCode)
    {
        parent::__construct("Parser error on position ".$position.": ".$reason."\r\n".substr($fullCode, $position - 10, 10)."\033[31;1;4m".substr($fullCode, $position, 10)."\033[0m");
        $this->position = $position;
        $this->fullCode = $fullCode;
        $this->reason = $reason;
    }
}
