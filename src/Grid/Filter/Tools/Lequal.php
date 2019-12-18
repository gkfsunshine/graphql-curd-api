<?php declare(strict_types=1);

namespace Graph\Curd\Grid\Filter\Tools;

use Graph\Curd\Grid\Filter\AbstractFilter;

class Lequal extends AbstractFilter
{
    protected $operator = 'leq';

    protected $query='where';

    protected $exprFormat='<=';


}
