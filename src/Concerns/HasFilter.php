<?php declare(strict_types=1);

namespace Graph\Curd\Concerns;

use Graph\Curd\Grid\Filter\Filter;

trait HasFilter
{
    protected $filter;

    /**
     * 初始化filter
     */
    protected function initFilter()
    {
        $this->filter = new Filter($this->getGridModel());
    }

    /**
     * filter 中间
     *
     * @param \Closure $callback
     */
    protected function filter(\Closure $callback)
    {
        return call_user_func($callback, $this->filter);
    }

}
