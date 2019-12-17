<?php declare(strict_types=1);

namespace App\Graph\Grid\Filter\Tools;

use App\Graph\Grid\Filter\AbstractFilter;

class Gthan extends AbstractFilter
{
    protected $operator = 'gt';

    protected $query='where';

    protected $exprFormat='>';


}
