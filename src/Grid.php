<?php declare(strict_types=1);

namespace Graph\Curd;

use Graph\Curd\Concerns\HasFilter;
use Graph\Curd\Grid\Filter\Filter;
use Graph\Curd\Helpers\Response;
use Graph\Curd\Queries\QuickModel;

class Grid
{
    use HasFilter;

    protected $gridModel;

    public function __construct()
    {
        $quickModel  = new QuickModel();
        $this->gridModel = $quickModel->getGridModel();

        $this->init();
    }

    protected function init()
    {
        $this->initFilter();
    }

    public function getGridModel()
    {
        return $this->gridModel;
    }

    public function build()
    {
        \DB::enableQueryLog();
        $this->filter(function(Filter $filter){
            $filter->execute();
        });
        $report = [
            'record' => $this->filter(function(Filter $filter){
                return $filter->getData();
            }),
            'query'  => \DB::getQueryLog(),
            'meta'   => $this->filter(function(Filter $filter){
                return $filter->getMeta();
            })
        ];

        return Response::responseJson($report);
    }
}
