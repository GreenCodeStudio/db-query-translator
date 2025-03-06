<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Query;
class Select
{

    public array $columns = [];
    public ?\Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table $from = null;
    public $where = null;
    public array $join = [];
    public ?int $offset = null;
    public ?int $limit = null;
    public array $orderBy = [];
    /**
     * @var true
     */
    public bool $distinct = false;
}
