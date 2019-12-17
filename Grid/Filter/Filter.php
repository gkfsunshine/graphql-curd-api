<?php declare(strict_types=1);

namespace App\Graph\Grid\Filter;

use App\Graph\Exceptions\GridException;
use App\Graph\Grid\Filter\Tools\Between;
use App\Graph\Grid\Filter\Tools\Equal;
use App\Graph\Grid\Filter\Tools\Gequal;
use App\Graph\Grid\Filter\Tools\Gthan;
use App\Graph\Grid\Filter\Tools\In;
use App\Graph\Grid\Filter\Tools\Lequal;
use App\Graph\Grid\Filter\Tools\Like;
use App\Graph\Grid\Filter\Tools\llike;
use App\Graph\Grid\Filter\Tools\Lthan;
use App\Graph\Grid\Filter\Tools\NotBetween;
use App\Graph\Grid\Filter\Tools\NotEqual;
use App\Graph\Grid\Filter\Tools\NotIn;
use App\Graph\Grid\Filter\Tools\Rlike;
use App\Graph\Helpers\Request;
use App\Graph\Queries\GridModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

class Filter
{
    /**
     * @var array
     */
    protected static $supports = [
        'eq'   => Equal::class,
        'neq'  => NotEqual::class,
        'cs'   => Like::class,
        'lcs'  => llike::class,
        'rcs'  => Rlike::class,
        'gt'   => Gthan::class,
        'lt'   => Lthan::class,
        'in'   => In::class,
        'nin'  => NotIn::class,
        'geq'  => Gequal::class,
        'leq'  => Lequal::class,
        'bt'   => Between::class,
        'nbt'  => NotBetween::class
    ];

    protected $gridModel;

    protected $data;

    protected $meta =[];

    public function __construct(GridModel $model)
    {
        $this->gridModel = $model;
    }

    /**
     * @throws GridException
     */
    public function execute()
    {
        $this->analysisMasterFilter();
    }
    /**
     * 解析主表查询
     *
     * @throws GridException
     */
    private function analysisMasterFilter()
    {
        $input = Request::getInputJsonRaw();

        //主表查询
        $filtersStr  = $input['filter']??'';
        $this->filterMatch($filtersStr);

        //管理表查询
        $relationStr = $input['relations']??'';
        $this->buildRelation($relationStr);

        //主表排序
        //sort order
        $sortStr = $input['sort_by']??'';
        $this->buildOrderBy($sortStr);

        //page
        $pageStr = $input['page']??'';
        $this->buildPaginator($pageStr);

    }

    protected function buildPaginator($pageStr)
    {
        $builder = $this->gridModel->getBuilder();
        if(!empty($pageStr) && substr_count($pageStr,',') === 1){
            list($page,$perPage) = explode(',',$pageStr);
            $paginate = $builder->paginate($perPage,$columns = ['*'], $pageName = 'page', $page)->toArray();
            $this->data = $paginate['data'];
            $this->meta['paginator'] = self::getArrayFilter($paginate,'current_page,last_page,per_page,total');
        }else{
            $this->data = $builder->get();
        }
    }

    public function getData()
    {
        return $this->data;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    final static function getArrayFilter($data,$filter)
    {
        $tmp = [];
        foreach ($data as $key=>$val){
            if(in_array($key,explode(',',$filter))){
                $tmp[$key] = $val;
            }
        }

        return $tmp;
    }

    protected function buildOrderBy($sortStr)
    {
        if(!empty($sortStr)){
            if(preg_match('/\{[a-zA-Z0-9_]{2,},(desc|asc)\}+/',$sortStr)){
                $builder = $this->gridModel->getBuilder();
                while (strlen($sortStr)>0){
                    $parseStr = $this->parseStrForCharacter($sortStr);
                    $effectiveSortStr = $parseStr['effect_str'];
                    if(substr_count($effectiveSortStr,',') !== 1){
                        break;
                    }

                    list($column,$direction) = explode(',',$effectiveSortStr,2);
                    $builder = $builder->orderBy($column,$direction); // to do 验证字段
                    $sortStr = $parseStr['cut_str'];
                    if(empty($sortStr) || strlen($sortStr)<5){/*至少有7个字符 {a,b,c}*/
                        break;
                    }
                }
            }
        }
    }

    /**
     * @param $relationStr
     * @throws GridException
     */
    protected function buildRelation($relationStr)
    {
        $builder = $this->gridModel->getBuilder();
        $originalModel = $this->gridModel->getOriginalModel();
        if(!empty($relationStr)){
            while (strlen($relationStr)>0){
                if(preg_match('/^[0-9,a-zA-Z]+\(.*\)$/',$relationStr)){
                    $relationName = substr($relationStr,0,strpos($relationStr,'('));
                    $parseStr = $this->interceptBetweenTwoBrackets($relationStr);
                    $singleRelationStr = $parseStr['effect_str'];
                    $relationStr = substr($parseStr['cut_str'],1);
                    if(method_exists($originalModel,$relationName)){
                        $relation = call_user_func([$originalModel, $relationName]);
                        if($relation instanceof Relation){
                            $builder = $builder->with($relationName);
                            //判断是否filter
                            if(preg_match_all("/(?:filter:\[)(.*)(?:\])/i",$singleRelationStr, $match)){
                                switch (true){
                                    case $relation instanceof BelongsToMany :
                                        $relationFilterStr = $match[1][0];
                                        $builder = $builder->whereExists(function ($query) use($relation,$originalModel,$relationFilterStr){
                                            $query->from($relation->getTable())
                                                ->whereRaw($relation->getQualifiedForeignPivotKeyName().' = '.$originalModel->getTable().'.'.$originalModel->getKeyName());

                                            $relationModel = $relation->getModel();
                                            $this->gridModel->setOriginalModel($relationModel);
                                            $this->gridModel->setBuilder($relationModel);
                                            $this->filterMatch($relationFilterStr);
                                            $relationQuery = $this->gridModel->getBuilder()->get();
                                            $query->whereIn($relation->getRelatedPivotKeyName(),$relationQuery->pluck('id'));
                                        });
                                        break;
                                    case  $relation instanceof BelongsTo:
                                    case  $relation instanceof HasOne :
                                    case  $relation instanceof HasMany :
                                        $relationFilterStr = $match[1][0];
                                        $builder = $builder->whereExists(function($query) use ($relation,$originalModel,$relationFilterStr){
                                            $query->from($relation->getModel()->getTable())
                                                ->whereRaw($relation->getQualifiedForeignKeyName().' = '.$originalModel->getTable().'.'.$originalModel->getKeyName());
                                            $this->gridModel->setBuilder($query);
                                            $this->filterMatch($relationFilterStr);
                                        });
                                        break;
                                }
                            }
                        }
                    }
                    if(!is_string($relationStr) || strlen($relationStr) < 4){
                        break;
                    }
                }else{
                    break;
                }
            }

        }else{
            $relations = $this->gridModel->quickModel->getGridRelation();
            foreach ($relations as $relation){
                $builder = $builder->with($relation);
            }
        }

        $this->gridModel->setBuilder($builder);
    }

    /**
     * 截取两个字符之前的字符串
     *
     * @param string $str
     * @param string $prefix
     * @param string $next
     * @return array
     */
    private function parseStrForCharacter($str='',$prefix='{',$next='}')
    {
        $st = stripos($str,$prefix);
        $ed = stripos($str,$next);

        return [
            'effect_str'=> substr($str,$st+1,$ed-$st-1),
            'cut_str'   => substr($str,$ed+1)
        ];
    }

    /**
     * 截取括号中的字符
     *
     * @param string $str
     * @param string $prefix
     * @param string $next
     * @return array
     * @throws GridException
     */
    private function interceptBetweenTwoBrackets($str='',$prefix='(',$next=')')
    {
        $st = stripos($str,$prefix);
        $tmpBracket = [];
        $ed = 0;
        for ($i = 0;$i < strlen($str);$i++){
            if($str[$i]==$prefix){
                $tmpBracket[] = $str[$i];
            }
            if ($str[$i] == $next){
                if(count($tmpBracket) == 1){/*最后一个*/
                    $ed = $i;
                }else{
                    array_pop($tmpBracket);
                }
            }
        }
        if($st<$ed){
            return [
                'effect_str'=> substr($str,$st+1,$ed-$st-1),
                'cut_str'   => substr($str,$ed+1)
            ];
        }else{
            throw new GridException('字符串解析失败！');
        }
    }

    /**
     * @param $filtersStr
     * @param $model
     * @return mixed
     * @throws GridException
     */
    protected function filterMatch($filtersStr)
    {
        $model = $this->gridModel->getBuilder();
        if(!empty($filtersStr)){
            $effectiveFilters = [];
            if(!Str::contains($filtersStr,'or')){/*证明没or*/
                $filterStrLen = strlen($filtersStr);
                while ($filterStrLen>0){
                    $parseStr = self::parseStrForCharacter($filtersStr);
                    $effectiveFilters[] = $parseStr['effect_str'];
                    $filtersStr = $parseStr['cut_str'];
                    if(empty($filtersStr) || strlen($filtersStr)<7){/*至少有7个字符 {a,b,c}*/
                        break;
                    }
                }
                if(!empty($effectiveFilters)){
                    $this->buildWhere($effectiveFilters);
                }
            }else{/*存在or的情况*/
                /*正则配置是否有()*/
                //preg_match('/\(\{[a-z,A-Z0-9]{7,}\}(or|and)?(\{[a-z0-9,A-Z]{7,}\})*\)/','{username,eq,admin}{username,eq,admin1}or({name,cs,d}or{id,bt,0,1})',$tmp);
                //'^\{[a-z,A-Z]{7,}\}';  '^or\{[a-z,A-Z0-9]{7,}\}$'; '^and\{[a-z,A-Z0-9]{7,}\}$'; '^or\{[a-z,A-Z0-9]{7,}\}$'; '/\(\{[a-z,A-Z0-9]{7,}\}(or|and)?(\{[a-z0-9,A-Z]{7,}\})*\)&/'}
                while (strlen($filtersStr)>=7){
                    if(preg_match('/^(and)?\{[_a-z,A-Z0-9]{7,}\}/',$filtersStr,$matchs)){
                        $parseStr = self::parseStrForCharacter($filtersStr);
                        $filter   = $parseStr['effect_str'];
                        $this->buildWhere([$filter],true);
                        $filtersStr = $parseStr['cut_str'];

                    }elseif (preg_match('/^or\{[_0-9a-zA-Z,]{7,}\}+/',$filtersStr,$matchs)){
                        $parseStr = self::parseStrForCharacter($filtersStr);
                        $filter = $parseStr['effect_str'];
                        $this->buildWhere([$filter],true);
                        $filtersStr = $parseStr['cut_str'];

                    }elseif (preg_match('/^or\(.*/',$filtersStr,$matchs)){
                        //or({name,cs,d}or({name,eq,1233}{id,bt,0,1})){admin,cs,999}
                        $parseStr = self::interceptBetweenTwoBrackets($filtersStr);
                        $effectiveFilters = $parseStr['effect_str'];
                        $filtersStr = $parseStr['cut_str'];
                        $model = $model->OrWhere(function($query) use ($effectiveFilters){
                            $this->filterMatch($effectiveFilters);
                        });

                    }elseif (preg_match('/^\(\{[a-zA-Z,_0-9]{7,}.*/',$filtersStr,$matchs)){
                        $parseStr = self::interceptBetweenTwoBrackets($filtersStr);
                        $effectiveFilters = $parseStr['effect_str'];
                        $filtersStr = $parseStr['cut_str'];
                        $model = $model->where(function($query) use ($effectiveFilters){
                            $this->filterMatch($effectiveFilters);
                        });
                    }else{/*匹配错误*/
                        break;
                    }

                    if(empty($filtersStr) || strlen($filtersStr)<7){/*至少有7个字符 {a,b,c}*/
                        break;
                    }
                }
            }
        }

        return $model;
    }

    protected function resolveFilter($abstract, ...$arguments)
    {
        if (isset(static::$supports[$abstract])) {
            return new static::$supports[$abstract]($this->gridModel,...$arguments);
        }
    }

    /**
     * @param string $operator
     * @param string $str
     * @return bool
     */
    protected function strCount($operator='eq',$str='')
    {
        switch (strtolower($operator)){
//            case 'in':
//            case 'nin':
//                $strOperator = substr_count($str,',') >= 0;
//                break;
            case 'bt':
            case 'nbt':
                $strOperator = substr_count($str,',') === 1;
                break;
            default:
                $strOperator = substr_count($str,',') >= 0;
        }

        return $strOperator;
    }

    /**
     * @param $effectiveFilters
     * @param bool $or
     * @throws GridException
     */
    protected function buildWhere($effectiveFilters,$or=false)
    {
        if(!empty($effectiveFilters)){
            foreach ($effectiveFilters as $filter){
                if(!Str::contains($filter,',') || empty($filter) || substr_count($filter,',') < 2){
                    continue;
                }
                list($columns,$operator,$value) = explode(',',$filter,3);
                if($this->strCount($operator,$value) === false){
                    continue;
                }

                $abstractFilter = $this->resolveFilter($operator,$columns,$value);
                if($abstractFilter instanceof AbstractFilter){
                    $abstractFilter->addBasicWhereBinding($or);
                }
            }
        }
    }

}
