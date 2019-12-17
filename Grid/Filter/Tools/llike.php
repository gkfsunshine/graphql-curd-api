<?php declare(strict_types=1);
namespace App\Graph\Grid\Filter\Tools;

use App\Graph\Grid\Filter\AbstractFilter;

class llike extends AbstractFilter
{
    protected $operator = 'lcs';

    protected $exprFormat = 'like';

    protected $query='where';

    protected function condition()
    {
        $this->value = '%'.$this->value;
    }
}