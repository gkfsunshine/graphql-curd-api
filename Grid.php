<?php declare(strict_types=1);

namespace App\Graph;

use App\Graph\Concerns\HasFilter;
use App\Graph\Grid\Filter\Filter;
use App\Graph\Helpers\Response;
use App\Graph\Queries\QuickModel;

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
