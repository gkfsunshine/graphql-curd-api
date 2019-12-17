<?php declare(strict_types=1);

namespace App\Graph\Grid\Filter\Tools;

use App\Graph\Grid\Filter\AbstractFilter;

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
