<?php

namespace Mkrawczyk\DbQueryTranslator\LocalExecutor;

use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Identifier;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Literal;
use Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectAll;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Column\SelectColumn;
use Mkrawczyk\DbQueryTranslator\Nodes\Query\Select;
use MKrawczyk\FunQuery\FunQuery;

class LocalExecutor
{
    public LocalDB $db;

    public function __construct(LocalDB $db)
    {
        $this->db = $db;
    }

    public static function ExecuteQuery(LocalDB $db, $query)
    {
        $executor = new LocalExecutor($db);
        return $executor->execute($query);
    }

    public function execute($node, $row = [])
    {
        if ($node instanceof Select) {
            if ($node->from) {
                $ret = $this->execute($node->from);
            } else {
                $ret = FunQuery::create([[]]);
            }
            $ret = $ret->select(function ($row) use ($node) {
                $ret = [];
                foreach ($node->columns as $column) {

                    if ($column instanceof SelectColumn) {
                        $ret[$column->name] = $this->execute($column->expression, $row);
                    } else if ($column instanceof SelectAll) {
                        foreach ($row as $r) {
                            $ret = [...$ret, ...$r];
                        }
                    }

                }
                return (object)$ret;
            });
            return $ret;
        } else if ($node instanceof Table) {
            return $this->db->tables[$node->tableName]->map(fn($x) => [$node->alias => $x]);
        } else if ($node instanceof Identifier) {
            if ($node->table) {
                return $row[$node->table][$node->name];
            }else{
                foreach ($row as $r){
                    if (isset($r[$node->name])){
                        return $r[$node->name];
                    }
                }
                return null;
            }
        }else if($node instanceof Literal){
            return $node->value;
        }

        else {
            throw new \Exception("Unknown node type ".get_class($node));
        }
    }
}
