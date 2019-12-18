<?php declare(strict_types=1);

namespace Graph\Curd\Grid\Filter\Tools;

use Graph\Curd\Grid\Filter\AbstractFilter;

class Equal extends AbstractFilter
{
    protected $operator = 'eq';

    protected $query='where';

    protected $exprFormat='=';


}
