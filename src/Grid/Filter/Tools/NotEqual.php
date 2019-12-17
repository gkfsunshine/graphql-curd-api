<?php declare(strict_types=1);

namespace App\Graph\Grid\Filter\Tools;

use App\Graph\Grid\Filter\AbstractFilter;

class NotEqual extends AbstractFilter
{
    protected $operator = 'neq';

    protected $query='where';

    protected $exprFormat='!=';
}
