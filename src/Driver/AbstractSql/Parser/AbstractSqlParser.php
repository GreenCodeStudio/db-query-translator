<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\AbstractSql\Parser;

use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Addition;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\BooleanAnd;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Comparison;
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
        throw new ParserException($reason, $this->position, $this->code);

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
        $canBeKeyword = $this->position + strlen($keyword) == strlen($this->code) || preg_match('/[^a-zA-Z0-9_]/', $this->code[$this->position + strlen($keyword)]);
        return $canBeKeyword && strtolower(substr($this->code, $this->position, strlen($keyword))) === strtolower($keyword);
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

            [$isEnd, $lastNode] = $this->parseExpressionOneStep($lastNode, $exitLevel);
            if ($isEnd) {
                break;
            }
        }
        return $lastNode;
    }

    protected function parseExpressionOneStep($lastNode, int $exitLevel = 0)
    {
        if ($this->isKeyword('AS') || $this->isKeyword('FROM') || $this->isKeyword('WHERE') || $this->isKeyword('GROUP') || $this->isKeyword('ASC') || $this->isKeyword('DESC')) {
            return [true, $lastNode];
        } else if ($this->isChar('/,/')) {
            return [true, $lastNode];
        } else if ($this->isChar('/[0-9]/')) {
            if ($lastNode !== null) {
                $this->throw('Unexpected number');
            }
            $number = $this->readUntill('/[^0-9]/');
            return [false, new Literal('int', $number)];
        } else if ($this->isChar('/[\'"]/')) {
            if ($lastNode !== null) {
                $this->throw('Unexpected string');
            }
            $quote = $this->code[$this->position];
            $this->position++;
            $string = $this->readUntill('/'.$quote.'/');
            $this->position++;
            return [false, new Literal('string', $string)];
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
            return [false, new Identifier($name, $table)];
        } else if ($this->isKeyword('AND')) {
            if ($exitLevel > 1) {
                return [true, $lastNode];
            }
            if ($lastNode === null) {
                $this->throw('Unexpected AND');
            }
            $this->skipKeyword('AND');
            $this->skipWhitespace();
            return [false, new BooleanAnd($lastNode, $this->parseExpression(1))];
        } else if ($this->isChar('/=/')) {
            if ($exitLevel > 2) {
                return [true, $lastNode];
            }
            $this->position++;
            $this->skipWhitespace();
            return [false, new Equals($lastNode, $this->parseExpression(2))];
        } else if ($this->isChar('/[><]=?/')) {
            if ($exitLevel > 2) {
                return [true, $lastNode];
            }
            $lessThan = false;
            $orEqual = false;
            if ($this->isChar('/</')) {
                $lessThan = true;
            }
            $this->position++;
            if ($this->isChar('/=/')) {
                $orEqual = true;
                $this->position++;
            }
            $this->skipWhitespace();
            return [false, new Comparison($lastNode, $this->parseExpression(2), $lessThan, $orEqual)];
        } else if ($this->isChar('/\+/')) {
            if ($exitLevel > 3) {
                return [true, $lastNode];
            }
            $this->position++;
            $this->skipWhitespace();
            return [false, new Addition($lastNode, $this->parseExpression(3))];
        } else if ($this->isChar('/-/')) {
            if ($exitLevel > 3) {
                return [true, $lastNode];
            }
            $this->position++;
            $this->skipWhitespace();
            return [false, new Subtraction($lastNode, $this->parseExpression(3))];
        } else if ($this->isChar('/\*/')) {
            if ($exitLevel > 4) {
                return [true, $lastNode];
            }
            $this->position++;
            $this->skipWhitespace();
            return [false, new Multiplication($lastNode, $this->parseExpression(4))];
        } else if ($this->isChar('/\//')) {
            if ($exitLevel > 4) {
                return [true, $lastNode];
            }
            $this->position++;
            $this->skipWhitespace();
            return [false, new Division($lastNode, $this->parseExpression(4))];
        } else if ($this->isChar('/%/')) {
            if ($exitLevel > 4) {
                return [true, $lastNode];
            }
            $this->position++;
            $this->skipWhitespace();
            return [false, new Modulo($lastNode, $this->parseExpression(4))];
        } else if ($this->isChar('/\?/')) {
            if ($lastNode !== null) {
                $this->throw('Unexpected parameter');
            }
            $this->position++;
            return [false, new Parameter('')];
        } else if ($this->isChar('/:/')) {
            if ($lastNode !== null) {
                $this->throw('Unexpected parameter');
            }
            $this->position++;
            $name = $this->readUntill('/[.,\'"`+\-*\/ ]/');
            return [false, new Parameter($name)];
        } else if ($this->isAnyKeyword()) {
            return [true, $lastNode];
        } else {
            if ($lastNode !== null) {
                $this->throw('Unexpected identifier');
            }
            $name = $this->readUntill('/[.,\'"`+\-*\/ \s]/');
            $table = null;
            $this->skipWhitespace();
            if ($this->isChar('/\./')) {
                $this->position++;
                $this->skipWhitespace();
                $table = $name;
                $name = $this->readSubIdentifier();
            }
            return [false, new Identifier($name, $table)];
        }
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

        if ($this->isKeyword('DISTINCT')) {
            $this->skipKeyword('DISTINCT');
            $ret->distinct = true;
            $this->skipWhitespace();
        }

        while (!$this->endOfCode()) {
            $this->skipWhitespace();
            if ($this->isKeyword('*')) {
                $ret->columns[] = new \Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectAll();
                $this->skipKeyword('*');
            } else {
                $startPosition = $this->position;
                $expression = $this->parseExpression();
                if ($expression instanceof Identifier) {
                    $name = $expression->name;
                } else {
                    $name = substr($this->code, $startPosition, $this->position - $startPosition);
                    $name = trim($name);
                }
                if ($this->isKeyword('AS')) {
                    $this->skipKeyword('AS');
                    $this->skipWhitespace();
                    $name = $this->readUntill('/[\s,]/');
                }
                $ret->columns[] = new SelectColumn($name, $expression);
            }
            $this->skipWhitespace();
            if ($this->isChar('/,/')) {
                $this->position++;
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

        if ($this->isKeyword('ORDER')) {
            $this->skipKeyword('ORDER');
            $this->skipWhitespace();
            $this->skipKeyword('BY');
            $this->skipWhitespace();
            while (!$this->endOfCode()) {
                $expression = $this->parseExpression();
                $this->skipWhitespace();
                $descending = false;
                if ($this->isKeyword('DESC')) {
                    $this->skipKeyword('DESC');
                    $descending = true;
                } else if ($this->isKeyword('ASC')) {
                    $this->skipKeyword('ASC');
                }
                $ret->orderBy[] = (object)[
                    'expression' => $expression,
                    'descending' => $descending
                ];
                $this->skipWhitespace();
                if ($this->isChar('/,/')) {
                    $this->position++;
                    $this->skipWhitespace();
                } else {
                    break;
                }
            }
        }
        while (!$this->endOfCode() && $this->isKeyword('LIMIT') || $this->isKeyword('OFFSET') || $this->isKeyword('FETCH')) {

            if ($this->isKeyword('LIMIT')) {
                [$offset, $limit] = $this->parseLimit();
                if ($offset !== null) {
                    $ret->offset = $offset;
                }
                $ret->limit = $limit;
            }

            $this->skipWhitespace();
            if ($this->isKeyword('OFFSET')) {
                $ret->offset = $this->parseOffset();
            }
            $this->skipWhitespace();
            if ($this->isKeyword('FETCH')) {
                $ret->limit = $this->parseFetch();
            }
        }

        return $ret;
    }

    protected
    function parseLimit()
    {
        $this->skipKeyword('LIMIT');
        $this->skipWhitespace();
        $limit = (int)$this->readUntill('/[^0-9]/');
        $offset = null;
        $this->skipWhitespace();
        if ($this->isChar('/,/')) {
            $this->position++;
            $this->skipWhitespace();
            $offset = $limit;
            $limit = (int)$this->readUntill('/[^0-9]/');
        }
        return [$offset, $limit];
    }

    protected
    function parseOffset()
    {
        $this->skipKeyword('OFFSET');
        $this->skipWhitespace();
        return (int)$this->readUntill('/[^0-9]/');
    }

    protected
    function parseFetch()
    {
        $this->throw('FETCH not supported in this dialect');
    }

    protected
    function readTable()
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
        if (!$this->isAnyKeyword()) {

            $alias = $this->readSubIdentifier();
            if (empty($alias)) {
                $alias = $firstName;
            }
        }
        return new Table($firstName, $alias);
    }

    protected
    function isAnyKeyword()
    {
        $keywords = ['JOIN', 'LEFT', 'RIGHT', 'INNER', 'OUTER', 'ON', 'WHERE', 'GROUP', 'ORDER', 'LIMIT', 'FETCH', 'OFFSET', 'GROUP', 'BY', 'ASC', 'DESC', 'AND', 'OR', 'AS', 'FROM', 'SELECT', 'HAVING'];
        foreach ($keywords as $keyword) {
            if ($this->isKeyword($keyword)) {
                return true;
            }
        }
        return false;
    }
}
