<?php

namespace Mkrawczyk\DbQueryTranslator\LocalExecutor;

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

    public function execute($node)
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
                    $rowRet = [];
                    if ($column instanceof SelectColumn) {
                        $rowRet[$column->name] = $this->execute($column->expression);
                    } else if ($column instanceof SelectAll) {
                        $rowRet = [...$rowRet, ...$row];
                    }
                    return (object)$rowRet;
                }
                return $ret;
            });
            return $ret;
        } else if ($node instanceof Table) {
            return $this->db->tables[$node->tableName];
        }
    }
}
