<?php declare(strict_types=1);

namespace Graph\Curd\Grid\Filter\Tools;

use Graph\Curd\Grid\Filter\AbstractFilter;

class Gequal extends AbstractFilter
{
    protected $operator = 'geq';

    protected $query='where';

    protected $exprFormat='>=';


}
