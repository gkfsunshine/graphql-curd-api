<?php declare(strict_types=1);

namespace App\Graph\Helpers;

/**
 * Class Request
 * @package App\Graph\Helpers
 */
class Request
{
    public static function getInputJsonRaw()
    {
        return request()->json()->all();
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    /**
     *
     *
     * @param $method
     * @param $parameters
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $parameters)
    {
        if(method_exists($this,$method)){
            return $this->{$method}(...$parameters);
        }

        throw new \Exception('method'.$method.' not exist!');
    }
}
