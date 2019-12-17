<?php declare(strict_types=1);

namespace App\Graph\Grid\Filter\Tools;

use App\Graph\Grid\Filter\AbstractFilter;

class In extends AbstractFilter
{
    protected $operator = 'in';

    protected $query='whereIn';

    protected function getWhereArguments()
    {
        return [$this->column,explode(',',$this->value)];
    }

}
