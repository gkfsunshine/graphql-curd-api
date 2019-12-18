<?php declare(strict_types=1);

namespace Graph\Curd\Grid\Filter\Tools;

use Graph\Curd\Grid\Filter\AbstractFilter;

class NotEqual extends AbstractFilter
{
    protected $operator = 'neq';

    protected $query='where';

    protected $exprFormat='!=';
}
