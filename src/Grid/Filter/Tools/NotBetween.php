<?php declare(strict_types=1);

namespace Graph\Curd\Grid\Filter\Tools;

use Graph\Curd\Grid\Filter\AbstractFilter;

class NotBetween extends AbstractFilter
{
    protected $operator = 'nbt';

    protected $query='whereNotBetween';

    protected $exprFormat='=';

    protected function getWhereArguments()
    {
        return [$this->column,explode(',',$this->value)];
    }


}
