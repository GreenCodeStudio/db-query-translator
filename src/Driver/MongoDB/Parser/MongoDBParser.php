<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\MongoDB\Parser;

use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Addition;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Identifier;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Literal;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectColumn;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;

class MongoDBParser
{

    private Select $query;

    public function parseAggregate(array $aggregate)
    {
        $this->query = new Select();
        $result = [];
        foreach ($aggregate as $stage => $params) {
            $result[] = $this->parseStage($stage, $params);
        }
        return $this->query;
    }

    private function parseStage(string $stage, mixed $params)
    {
        if ($stage === '$project') {
            $isId= false;
            foreach ($params as $field => $value) {
                if($value!=0) {
                    $expression = $this->parseExpression($value);
                    $this->query->columns[] = new SelectColumn($field, $expression);
                }
                if($field === '_id'){
                    $isId= true;
                }
            }
            if(!$isId){
                $this->query->columns[] = new SelectColumn('_id', new Identifier('_id'));
            }

        }else if($stage === '$match'){
//            $this->query->where = $params;
        }
        else {
            throw new \Exception("Unsupported stage: $stage");
        }
    }

    private function parseExpression(mixed $value)
    {
        if (is_array($value)) {
            if (isset($value['$add'])) {
                return new Addition($this->parseExpression($value['$add'][0]), $this->parseExpression($value['$add'][1]));
            }
        }else if(is_int($value)){
            return new Literal('int', $value);
        }
        else {
            throw new \Exception("Unsupported expression type: ".gettype($value));
        }
    }
}
