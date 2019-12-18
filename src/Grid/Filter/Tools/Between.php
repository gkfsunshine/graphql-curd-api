<?php declare(strict_types=1);

namespace Graph\Curd\Grid\Filter\Tools;

use Graph\Curd\Grid\Filter\AbstractFilter;

class Between extends AbstractFilter
{
    protected $operator = 'bt';

    protected $query='whereBetween';

    protected $exprFormat='=';


    protected function getWhereArguments()
    {
        return [$this->column,explode(',',$this->value)];
    }
}
