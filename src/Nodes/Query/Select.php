<?php

namespace Mkrawczyk\DbQueryTranslator\Nodes\Query;
class Select
{

    public array $columns = [];
    public ?\Mkrawczyk\DbQueryTranslator\Nodes\Expression\Table $from = null;
    public  $where;
}
