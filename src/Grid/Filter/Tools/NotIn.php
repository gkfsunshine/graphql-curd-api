<?php declare(strict_types=1);

namespace App\Graph\Grid\Filter\Tools;

use App\Graph\Grid\Filter\AbstractFilter;

class NotIn extends AbstractFilter
{
    protected $operator = 'ini';

    protected $query='whereNotIn';

    protected function getWhereArguments()
    {
        return [$this->column,explode(',',$this->value)];
    }

}
