<?php declare(strict_types=1);

namespace Graph\Curd\Grid\Filter\Tools;

use Graph\Curd\Grid\Filter\AbstractFilter;

class Lthan extends AbstractFilter
{
    protected $operator = 'lt';

    protected $query='where';

    protected $exprFormat='<';


}
