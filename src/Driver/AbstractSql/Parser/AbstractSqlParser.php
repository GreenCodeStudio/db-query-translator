<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Parser;

use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Addition;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\BooleanAnd;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Division;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Equals;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Identifier;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Join;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Literal;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Modulo;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Multiplication;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Parameter;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Subtraction;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectColumn;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;

abstract class AbstractSqlParser
{

    protected string $code;
    protected int $position = 0;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    protected function throw(string $reason)
    {
        throw new \Exception("Parser error on position ".$this->position.": ".$reason."\r\n".substr($this->code, $this->position - 10, 10)."\033[31;1;4m".substr($this->code, $this->position, 10)."\033[0m");
    }


    protected function skipWhitespace()
    {
        while ($this->position < strlen($this->code) && ctype_space($this->code[$this->position])) {
            $this->position++;
        }
    }

    protected function isKeyword(string $keyword): bool
    {
        if ($this->position + strlen($keyword) > strlen($this->code)) {
            return false;
        }
        return strtolower(substr($this->code, $this->position, strlen($keyword))) === strtolower($keyword);
    }

    protected function skipKeyword(string ...$keywords)
    {
        foreach ($keywords as $keyword) {
            if ($this->isKeyword($keyword)) {
                $this->position += strlen($keyword);
                return;
            }
        }
        $this->throw('Expected keyword '.implode(', ', $keywords));
    }

    protected function endOfCode(): bool
    {
        return $this->position >= strlen($this->code);
    }

    protected function readUntill(string $regexp)
    {
        $ret = '';
        while ($this->position < strlen($this->code) && !preg_match($regexp, $this->code[$this->position])) {
            $ret .= $this->code[$this->position];
            $this->position++;
        }
        return $ret;
    }

    protected function getIdentifierQuoteRegexpStart(): ?string
    {
        return null;
    }

    protected function getIdentifierQuoteRegexpEnd(): ?string
    {
        return null;
    }

    protected function parseExpression(int $exitLevel = 0)
    {
        $lastNode = null;
        while (!$this->endOfCode()) {
            $this->skipWhitespace();
            if ($this->isKeyword('AS') || $this->isKeyword('FROM') || $this->isKeyword('WHERE') || $this->isKeyword('GROUP')) {
                return $lastNode;
            } else if ($this->isKeyword(',')) {
                return $lastNode;
            } else if ($this->isChar('/[0-9]/')) {
                if ($lastNode !== null) {
                    $this->throw('Unexpected number');
                }
                $number = $this->readUntill('/[^0-9]/');
                $lastNode = new Literal('int', $number);
            } else if ($this->isChar('/[\'"]/')) {
                if ($lastNode !== null) {
                    $this->throw('Unexpected string');
                }
                $quote = $this->code[$this->position];
                $this->position++;
                $string = $this->readUntill('/'.$quote.'/');
                $this->position++;
                $lastNode = new Literal('string', $string);
            } else if ($this->getIdentifierQuoteRegexpStart() && $this->isChar($this->getIdentifierQuoteRegexpStart())) {
                if ($lastNode !== null) {
                    $this->throw('Unexpected identifier');
                }
                $this->position++;
                $name = $this->readUntill($this->getIdentifierQuoteRegexpEnd());
                $this->position++;
                $table = null;
                if ($this->isChar('/\./')) {
                    $this->position++;
                    $this->skipWhitespace();
                    $table = $name;
                    $name = $this->readSubIdentifier();
                }
                $lastNode = new Identifier($name, $table);
            } else if ($this->isKeyword('AND')) {
                if ($exitLevel > 1) {
                    return $lastNode;
                }
                if ($lastNode === null) {
                    $this->throw('Unexpected AND');
                }
                $this->skipKeyword('AND');
                $this->skipWhitespace();
                $lastNode = new BooleanAnd($lastNode, $this->parseExpression(1));
            } else if ($this->isChar('/=/')) {
                if ($exitLevel > 2) {
                    return $lastNode;
                }
                $this->position++;
                $this->skipWhitespace();
                $lastNode = new Equals($lastNode, $this->parseExpression(2));
            } else if ($this->isChar('/\+/')) {
                if ($exitLevel > 3) {
                    return $lastNode;
                }
                $this->position++;
                $this->skipWhitespace();
                $lastNode = new Addition($lastNode, $this->parseExpression(3));
            } else if ($this->isChar('/-/')) {
                if ($exitLevel > 3) {
                    return $lastNode;
                }
                $this->position++;
                $this->skipWhitespace();
                $lastNode = new Subtraction($lastNode, $this->parseExpression(3));
            } else if ($this->isChar('/\*/')) {
                if ($exitLevel > 4) {
                    return $lastNode;
                }
                $this->position++;
                $this->skipWhitespace();
                $lastNode = new Multiplication($lastNode, $this->parseExpression(4));
            } else if ($this->isChar('/\//')) {
                if ($exitLevel > 4) {
                    return $lastNode;
                }
                $this->position++;
                $this->skipWhitespace();
                $lastNode = new Division($lastNode, $this->parseExpression(4));
            } else if ($this->isChar('/%/')) {
                if ($exitLevel > 4) {
                    return $lastNode;
                }
                $this->position++;
                $this->skipWhitespace();
                $lastNode = new Modulo($lastNode, $this->parseExpression(4));
            } else if ($this->isChar('/:/')) {
                if ($lastNode !== null) {
                    $this->throw('Unexpected parameter');
                }
                $this->position++;
                $name = $this->readUntill('/[.,\'"`+\-*\/ ]/');
                $lastNode = new Parameter($name);
            } else {
                if ($lastNode !== null) {
                    $this->throw('Unexpected identifier');
                }
                $name = $this->readUntill('/[.,\'"`+\-*\/ ]/');
                $table = null;
                $this->skipWhitespace();
                if ($this->isChar('/\./')) {
                    $this->position++;
                    $this->skipWhitespace();
                    $table = $name;
                    $name = $this->readSubIdentifier();
                }
                $lastNode = new Identifier($name, $table);
            }
        }
        return $lastNode;
    }

    protected function readSubIdentifier()
    {
        if ($this->getIdentifierQuoteRegexpStart() && $this->isChar($this->getIdentifierQuoteRegexpStart())) {
            $this->position++;
            $x = $this->readUntill($this->getIdentifierQuoteRegexpEnd());
            $this->position++;
            return $x;
        } else {
            return $this->readUntill('/[.,\'"`+\-*\/ ]/');
        }
    }

    protected function isChar(string $regExp)
    {
        return preg_match($regExp, $this->code[$this->position]);
    }

    protected function readSelect()
    {
        $this->skipKeyword('SELECT');
        $this->skipWhitespace();
        $ret = new Select();

        while (!$this->endOfCode()) {
            $this->skipWhitespace();
            if ($this->isKeyword('*')) {
                $ret->columns[] = new \Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectAll();
                $this->skipKeyword('*');
            } else {
                $startPosition = $this->position;
                $expression = $this->parseExpression();
                $name = substr($this->code, $startPosition, $this->position - $startPosition);
                $name = trim($name);
                if ($this->isKeyword('AS')) {
                    $this->skipKeyword('AS');
                    $this->skipWhitespace();
                    $name = $this->readUntill('/[\s,]/');
                }
                $ret->columns[] = new SelectColumn($name, $expression);
            }
            $this->skipWhitespace();
            if ($this->isKeyword(',')) {
                $this->skipKeyword(',');
                $this->skipWhitespace();
            } else {
                break;
            }
        }
        if ($this->isKeyword('FROM')) {
            $this->skipKeyword('FROM');
            $this->skipWhitespace();
            $ret->from = $this->readTable();
        }
        $this->skipWhitespace();
        if ($this->isKeyword('JOIN') || $this->isKeyword('LEFT') || $this->isKeyword('RIGHT') || $this->isKeyword('INNER') || $this->isKeyword('OUTER')) {
            $type = 'INNER';
            if ($this->isKeyword('LEFT')) {
                $type = 'LEFT';
                $this->skipKeyword('LEFT');
            }
            if ($this->isKeyword('RIGHT')) {
                $type = 'RIGHT';
                $this->skipKeyword('RIGHT');
            }
            if ($this->isKeyword('INNER')) {
                $type = 'INNER';
                $this->skipKeyword('INNER');
            }
            if ($this->isKeyword('OUTER')) {
                $type = 'OUTER';
                $this->skipKeyword('OUTER');
            }
            $this->skipWhitespace();
            $this->skipKeyword('JOIN');
            $this->skipWhitespace();
            $table = $this->readTable();
            $this->skipWhitespace();
            $on = null;
            if ($this->isKeyword('ON')) {
                $this->skipKeyword('ON');
                $this->skipWhitespace();
                $on = $this->parseExpression();
            }
            $ret->join[] = new Join($type, $table, $on);
        }
        $this->skipWhitespace();
        if ($this->isKeyword('WHERE')) {
            $this->skipKeyword('WHERE');
            $this->skipWhitespace();
            $ret->where = $this->parseExpression();
        }
        return $ret;
    }

    protected function readTable()
    {
        $this->skipWhitespace();

        if ($this->getIdentifierQuoteRegexpStart() !== null && $this->isChar($this->getIdentifierQuoteRegexpStart())) {
            $this->position++;
            $firstName = $this->readUntill($this->getIdentifierQuoteRegexpEnd());
            $this->position++;
        } else {
            $firstName = $this->readUntill('/\s/');
        }
        $this->skipWhitespace();
        $alias = $firstName;
        if (!$this->isKeyword('JOIN') && !$this->isKeyword('LEFT') && !$this->isKeyword('RIGHT') && !$this->isKeyword('INNER') && !$this->isKeyword('OUTER') && !$this->isKeyword('ON') && !$this->isKeyword('WHERE') && !$this->isKeyword('GROUP') && !$this->isKeyword('ORDER') && !$this->isKeyword('LIMIT')) {

            $alias = $this->readSubIdentifier();
            if (empty($alias)) {
                $alias = $firstName;
            }
        }
        return new Table($firstName, $alias);
    }
}
