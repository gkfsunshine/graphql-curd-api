<?php declare(strict_types=1);

namespace Graph\Curd\Helpers;

class Response
{
    public static function responseJson($data)
    {
        return response()->json($data);
    }
}
