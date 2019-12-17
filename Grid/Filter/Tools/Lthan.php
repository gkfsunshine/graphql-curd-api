<?php declare(strict_types=1);

namespace App\Graph\Grid\Filter\Tools;

use App\Graph\Grid\Filter\AbstractFilter;

class Lthan extends AbstractFilter
{
    protected $operator = 'lt';

    protected $query='where';

    protected $exprFormat='<';


}
