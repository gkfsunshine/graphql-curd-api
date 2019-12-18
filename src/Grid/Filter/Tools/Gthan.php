<?php declare(strict_types=1);

namespace Graph\Curd\Grid\Filter\Tools;

use Graph\Curd\Grid\Filter\AbstractFilter;

class Gthan extends AbstractFilter
{
    protected $operator = 'gt';

    protected $query='where';

    protected $exprFormat='>';


}
