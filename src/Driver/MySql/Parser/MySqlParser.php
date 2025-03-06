<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\MySql\Parser;

use Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Parser\AbstractSqlParser;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\BooleanAnd;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\BooleanNot;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\BooleanOr;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectColumn;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;

class MySqlParser extends AbstractSqlParser
{
    public function __construct(string $code)
    {
        parent::__construct($code);
    }

    public function parse()
    {
        if ($this->isKeyword('SELECT')) {
            return $this->readSelect();
        } else {
            $this->throw('Keyword expected');
        }
    }


    protected function getIdentifierQuoteRegexpStart(): ?string
    {
        return '/`/';
    }

    protected function getIdentifierQuoteRegexpEnd(): ?string
    {
        return '/`/';
    }

    protected function parseExpressionOneStep($lastNode, int $exitLevel = 0)
    {
        if ($this->isChar('/\|/')&&$this->isChar('/\|/',1)) {
            if ($exitLevel > 1) {
                return [true, $lastNode];
            }
            if ($lastNode === null) {
                $this->throw('Unexpected OR');
            }
            $this->position += 2;
            $this->skipWhitespace();
            return [false, new BooleanOr($lastNode, $this->parseExpression(1))];
        } else if ($this->isChar('/\&/')&&$this->isChar('/\&/',1)) {
            if ($exitLevel > 1) {
                return [true, $lastNode];
            }
            if ($lastNode === null) {
                $this->throw('Unexpected AND');
            }
            $this->position += 2;
            $this->skipWhitespace();
            return [false, new BooleanAnd($lastNode, $this->parseExpression(1))];
        } else if ($this->isChar('/!/')) {
            if ($exitLevel > 5) {
                return [true, $lastNode];
            }
            $this->position++;
            $this->skipWhitespace();
            if ($lastNode !== null) {
                $this->throw('Unexpected NOT');
            }
            return [false, new BooleanNot($this->parseExpression(5))];
        } else {
            return parent::parseExpressionOneStep($lastNode, $exitLevel);
        }
    }

}
