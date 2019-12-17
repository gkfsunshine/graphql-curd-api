<?php declare(strict_types=1);

namespace App\Graph\Grid\Filter\Tools;

use App\Graph\Grid\Filter\AbstractFilter;

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
