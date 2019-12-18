<?php
namespace Graph\Curd;

use Graph\Curd\Queries\QuickModel;

class Show
{

    public function __construct()
    {
        $quickModel = new QuickModel();
        $this->gridModel = $quickModel->getGridModel();
    }


    public function detail($id)
    {
        $builder   = $this->gridModel->getBuilder();
        $relations = $this->gridModel->getRelations();
        foreach ($relations as $relation){
            $builder = $builder->with($relation);
        }

        return \response()->json([
            'detail' => $builder->find($id)
        ]);
    }
}
