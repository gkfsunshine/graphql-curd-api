<?php declare(strict_types=1);

namespace App\Graph\Queries;

use App\Graph\Exceptions\GridException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class QuickModel
{
    protected $builder;

    protected $originalModel;

    protected $relations = null;

    /**
     * QuickModel constructor.
     * @throws GridException
     */
    public function __construct()
    {
        $this->relations = new Collection();
        $this->getAnnotationDefaultModel();
    }

    /**
     * @return GridModel
     */
    public function getGridModel()
    {
        return new \App\Graph\Queries\GridModel($this->builder,$this->originalModel,$this);
    }

    public function getGridRelation()
    {
        return $this->relations;
    }

    /**
     * 获取默认请求注解model
     *
     * @return object
     * @throws GridException
     */
    protected function getAnnotationDefaultModel() : object
    {
        $action = \Route::current()->getActionName();
        list($class, $method) = explode('@', $action);
        $class = strstr(substr(strrchr($class,'\\'),1),'Controller',true);
        $class = 'App\Models\\'.$class.'Model';
        if(class_exists($class)){
            try{
                $reflect = new \ReflectionClass($class);//获取反射类
                $this->builder = $this->originalModel = $reflect->newInstance();
                self::setAnnotationDefaultRelation($reflect);
            }catch (\Exception $e){
                throw $e;
            }// to do 日志
        }
        if($this->builder === null){
            throw new GridException($class.' can not annotation check please!');
        }

        return $this->builder;
    }

    /**
     * @param \ReflectionClass $reflect
     */
    private function setAnnotationDefaultRelation(\ReflectionClass  $reflect) : void
    {
        $methods = $reflect->getMethods();
        if(!empty($methods)){
            foreach ($methods as $method){
                if(method_exists($this->originalModel,$method->name)){
                    $returnType = $method->getReturnType();
                    if(!empty($returnType)){
                        $getName = $returnType->getName();
                        if($getName){
                            $relation = call_user_func([$this->originalModel, $method->name]);
                            if($relation instanceof Relation){
                                $this->relations->push($method->name);
                            }
                        }
                    }
                }
            }
        }
    }

}
