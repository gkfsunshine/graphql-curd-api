<?php declare(strict_types=1);

namespace App\Graph\Grid\Filter\Tools;

use App\Graph\Grid\Filter\AbstractFilter;

class Gequal extends AbstractFilter
{
    protected $operator = 'geq';

    protected $query='where';

    protected $exprFormat='>=';


}
