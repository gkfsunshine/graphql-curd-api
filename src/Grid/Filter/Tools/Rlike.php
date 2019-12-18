<?php declare(strict_types=1);
namespace Graph\Curd\Grid\Filter\Tools;

use Graph\Curd\Grid\Filter\AbstractFilter;

class Rlike extends AbstractFilter
{
    protected $operator = 'cs';

    protected $exprFormat = 'like';

    protected $query='where';

    protected function condition()
    {
        $this->value = $this->value.'%';
    }
}
