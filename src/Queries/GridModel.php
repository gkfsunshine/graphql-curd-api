<?php declare(strict_types=1);

namespace Graph\Curd\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class GridModel
{
    protected $builder;/*数据库对象*/

    protected $originalModel;/*数据库源对象*/

    protected $relations = null;/*模型关联对象*/

    public $quickModel;

    public function __construct($builder, EloquentModel $model,QuickModel $quickModel)
    {
        $this->builder = $builder;
        $this->originalModel = $model;
        $this->quickModel = $quickModel;
    }

    /**
     * @return Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    public function setBuilder($builder)
    {
        $this->builder = $builder;
    }

    public function getOriginalModel()
    {
        return $this->originalModel;
    }

    public function setOriginalModel($originalModel)
    {
        return $this->originalModel = $originalModel;
    }

    public function getRelations()
    {
        return $this->quickModel->getGridRelation();
    }

}
