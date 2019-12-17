<?php declare(strict_types=1);

namespace App\Graph\Grid\Filter\Tools;

use App\Graph\Grid\Filter\AbstractFilter;

class Equal extends AbstractFilter
{
    protected $operator = 'eq';

    protected $query='where';

    protected $exprFormat='=';


}
