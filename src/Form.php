<?php
namespace App\Graph;

use App\Graph\Concerns\HasForm;
use App\Graph\Exceptions\FormException;
use App\Graph\Exceptions\GridException;
use App\Graph\Helpers\Request;
use App\Graph\Queries\QuickModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class Form
{
    use HasForm;

    protected $gridModel;

    protected $relationModel;

    protected static $filedColumns = [];

    protected static $tableSupport = [];

    protected static $insertAction = true;

    public function __construct()
    {
        $quickModel  = new QuickModel();
        $this->gridModel = $quickModel->getGridModel();
        $this->relationModel = collect();
        $this->init();
    }

    private function init()
    {

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws FormException
     */
    public function store()
    {
        \DB::enableQueryLog();
        \DB::beginTransaction();
        try{
            $this->setMasterAttributeSave();/*主表master*/
            $this->setRelationSave();/*关联表relation*/
            \DB::commit();
        }catch (\Exception $e){
            \DB::rollBack();
            throw new FormException($e->getMessage());
        }

        return \response()->json([
            'code' => 200,
            'msg'  => 'success',
            'data' => [
                'insertGetId' => $this->gridModel->getBuilder()->id
            ],
            'query'=> \DB::getQueryLog()
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws FormException
     * @throws GridException
     */
    public function update($id)
    {
        $modelInstance = $this->gridModel->getOriginalModel();
        $updates = $this->prepareInsert();
        $modelInstance = $modelInstance->with($this->relationModel->unique()->toArray())->findOrFail($id);
        \DB::enableQueryLog();
        \DB::beginTransaction();
        try{
            foreach ($updates as $column => $value) {
                $modelInstance->setAttribute($column, $value);
            }
            $modelInstance->save();
            self::$insertAction = false;
            $this->gridModel->setBuilder($modelInstance);
            $this->setRelationSave();

            \DB::commit();
        }catch (\Exception $e){
            \DB::rollBack();
            throw new FormException($e->getMessage());
        }

        return \response()->json([
            'code' => 200,
            'msg'  => 'success',
            'query'=> \DB::getQueryLog()
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $modelInstance = $this->gridModel->getOriginalModel();
        $relations = $this->gridModel->getRelations();

        collect(explode(',', $id))->filter()->each(function ($id) use ($modelInstance,$relations){
            $builder = $modelInstance->newQuery();
            $model = $builder->with($relations->unique()->toArray())->findOrFail($id);
            if (false) {
                $model->forceDelete();
                return;
            }

            $model->delete();
        });

        $response = [
            'status'  => true,
            'message' => 'delete_succeeded',
        ];

        return response()->json($response);
    }

    /**
     * @return array
     * @throws GridException
     */
    protected function prepareInsert()
    {
        $originalModel = $this->gridModel->getOriginalModel();
        $input = Request::getInputJsonRaw();
        //过滤主键
        $primaryKey = $originalModel->getKeyName();
        $columns = $this->getTableColumns();
        $prepareInsert = [];
        foreach ($input as $column=>$value){
            if(!is_array($column) && !is_object($value) && in_array($column,$columns,true) && $primaryKey != $column){
                Arr::set($prepareInsert,$column,htmlentities(trim($value)));
            }
            if(is_array($value) || is_object($value)){
                if(method_exists($originalModel,$column)){
                    $relation = call_user_func([$originalModel, $column]);
                    if($relation instanceof Relation){
                        $this->relationModel->push($column);
                    }
                }
            }
        }

        return $prepareInsert;
    }

    /**
     * @throws GridException
     */
    protected function setMasterAttributeSave()
    {
        $builder = $this->gridModel->getBuilder();
        $pareInsetData = $this->prepareInsert();
        foreach ($pareInsetData as $column=>$value){
            //to do 验证唯一性  数据类型
            $builder->setAttribute($column, $value);
        }

        $builder->save();
    }

    /**
     * 获取表字段
     *
     * @param string $tableName
     * @return mixed
     * @throws GridException
     */
    private function getTableColumns($tableName='')
    {
        if(isset(self::$filedColumns[$tableName]) && !empty(self::$filedColumns[$tableName])){
            return self::$filedColumns[$tableName];
        }
        $currentTableName = $tableName !=='' ? $tableName : $this->gridModel->getOriginalModel()->getTable();
        if(!in_array($currentTableName,self::$tableSupport,true) &&  !Schema::hasTable($currentTableName)){
            throw new GridException($currentTableName.' not found!');
        }
        array_push(self::$tableSupport,$currentTableName);

        if(empty(self::$filedColumns[$currentTableName])){
            self::$filedColumns[$currentTableName] = Schema::getColumnListing($currentTableName);
        }

        return self::$filedColumns[$currentTableName];
    }

    protected function setRelationSave()
    {
        $input = Request::getInputJsonRaw();
        $originalModel = self::$insertAction ? $this->gridModel->getOriginalModel() : $this->gridModel->getBuilder();
        if($this->relationModel->isNotEmpty()){
            $relationMode = $this->relationModel->unique();
            foreach ($relationMode as $relationName){
                $relationData = $input[$relationName]??[];
                count($relationData) == count($relationData, 1) && $relationData = [$relationData];

                if(!empty($relationData)){
                    $relation = call_user_func([$originalModel, $relationName]);
                    switch (true){
                        case  $relation instanceof BelongsToMany :
                            $tmpId = [];
                            foreach ($relationData as $v){
                                array_push($tmpId,$v['id']);
                            }
                            $relation->sync($tmpId);
                            break;
                        case $relation instanceof BelongsTo:
                            $parent = $originalModel->$relationName;
                            if (is_null($parent)) {
                                $parent = $relation->getRelated();
                            }
                            foreach ($input[$relationName] as $column => $value) {
                                $parent->setAttribute($column, $value);
                            }
                            $parent->save();
                            $foreignKeyMethod = version_compare(app()->version(), '5.8.0', '<') ? 'getForeignKey' : 'getForeignKeyName';
                            if (!$originalModel->{$relation->{$foreignKeyMethod}()}) {
                                $originalModel->{$relation->{$foreignKeyMethod}()} = $parent->getKey();

                                $originalModel->save();
                            }
                            break;
                        case $relation instanceof HasOne:
                            $related = $originalModel->$relationName;
                            if (is_null($related)) {
                                $related = $relation->getRelated();
                                $qualifiedParentKeyName = $relation->getQualifiedParentKeyName();
                                $localKey = \Arr::last(explode('.', $qualifiedParentKeyName));
                                $related->{$relation->getForeignKeyName()} = $originalModel->{$localKey};
                            }
                            foreach ($input[$relationName] as $column => $value) {
                                $related->setAttribute($column, $value);
                            }

                            $related->save();
                            break;
                        case $relation instanceof HasMany:
                            foreach ($input[$relationName] as $related) {
                                $relation = $originalModel->$relationName();
                                $keyName = $relation->getRelated()->getKeyName();
                                $instance = $relation->findOrNew(\Arr::get($related, $keyName));
                                if (isset($related['__REMOVE__']) && $related['__REMOVE__'] == 1) {
                                    $instance->delete();
                                    continue;
                                }
                                \Arr::forget($related, '__REMOVE__');
                                $instance->fill($related);
                                $instance->save();
                            }
                            break;
                    }
                }
            }
        }
    }
}
