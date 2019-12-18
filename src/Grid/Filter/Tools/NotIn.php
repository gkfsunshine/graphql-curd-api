<?php declare(strict_types=1);

namespace Graph\Curd\Grid\Filter\Tools;

use Graph\Curd\Grid\Filter\AbstractFilter;

class NotIn extends AbstractFilter
{
    protected $operator = 'ini';

    protected $query='whereNotIn';

    protected function getWhereArguments()
    {
        return [$this->column,explode(',',$this->value)];
    }

}
