<?php declare(strict_types=1);

namespace App\Graph\Grid\Filter\Tools;

use App\Graph\Grid\Filter\AbstractFilter;

class Lequal extends AbstractFilter
{
    protected $operator = 'leq';

    protected $query='where';

    protected $exprFormat='<=';


}
