<?php declare(strict_types=1);

namespace Graph\Curd\Grid\Filter\Tools;

use Graph\Curd\Grid\Filter\AbstractFilter;

class In extends AbstractFilter
{
    protected $operator = 'in';

    protected $query='whereIn';

    protected function getWhereArguments()
    {
        return [$this->column,explode(',',$this->value)];
    }

}
