<?php declare(strict_types=1);

namespace Graph\Curd\Grid\Filter;

use Graph\Curd\Exceptions\GridException;
use Graph\Curd\Queries\GridModel;
use Illuminate\Support\Facades\Schema;

abstract class  AbstractFilter
{
    protected $query='where';

    protected $operator='eq';

    protected $exprFormat='=';

    protected $method;

    protected $column;

    protected $value;

    protected $gridModel;

    protected static $tableSupport=[];

    protected static $filedColumns=[];

    public function __construct(GridModel $model,...$arguments)
    {
        $this->gridModel = $model;
        list($this->column,$this->value) = $arguments;

    }

    /**
     * @param bool $or
     * @throws GridException
     */
    public function addBasicWhereBinding($or=false)
    {
        $this->method = ($or === false ? $this->query : 'or'.lcfirst($this->query));
        $this->buildConditionQuery();
    }

    protected function condition(){}

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

    /**
     * 检测表字段
     *
     * @param $column
     * @return bool
     * @throws GridException
     */
    protected function checkTableColumns($column)
    {
        if(empty($column)){
            return false;
        }
        $tableColumns = $this->getTableColumns();/*获取当前表字段*/

        return in_array($column,$tableColumns,true);
    }

    protected function getWhereArguments()
    {
        return [$this->column,$this->exprFormat,$this->value];
    }

    /**
     * @return bool
     * @throws GridException
     */
    protected function buildConditionQuery()
    {
        $this->condition();
        $builder = $this->gridModel->getBuilder();
        if($this->checkTableColumns($this->column) === false){/*验证表字段*/
            return false;
        }
        $arguments = $this->getWhereArguments();

        $this->gridModel->setBuilder(call_user_func_array([$builder,$this->method],$arguments));
    }
}
