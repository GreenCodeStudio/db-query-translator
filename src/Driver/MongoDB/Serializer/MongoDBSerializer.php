<?php

namespace Mkrawczyk\DbQueryTranslator\Driver\MongoDB\Serializer;

use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Addition;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Literal;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;

class MongoDBSerializer
{
    public function serialize($node)
    {
        if ($node instanceof Select) {
            $ret = [];
            $project = [];
            foreach ($node->columns as $column) {
                $project[$column->name] = $this->serialize($column->expression);
            }
            $ret[] = ['$project' => $project];
            return $ret;
        } else if ($node instanceof Addition) {
            return ['$add' => [$this->serialize($node->left), $this->serialize($node->right)]];
        } else if ($node instanceof Literal) {
            if ($node->type === 'int') {
                return ['$convert' => ['input' => $node->value, 'to' => 'int']];
            } else {
                throw new \Exception('Unknown literal type');
            }
        } else {
            throw new \Exception('Unknown node type');
        }
    }
}
