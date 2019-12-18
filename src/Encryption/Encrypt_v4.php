<?php
namespace Graph\Curd\Encryption;

class Encrypt_v4
{
    // 数据加密设置 默认秘钥
    const DATA_AUTH_KEY        = 'mU3ljGw<N^:%#QWc|p]kf*!E[)-uZJ7erS_.4{@R'; // 默认数据加密KEY

    /**
     * 加密方法
     * @param string $data 要加密的字符串
     * @param string $key  加密密钥
     * @param int $expire  过期时间 单位 秒
     * @return string
     */
    function system_encrypt($data, $key = '', $expire = 0){
        $key  = md5(empty($key) ? self::DATA_AUTH_KEY : $key); //   c4ca4238a0b923820dcc509a6f75849b
        $data = base64_encode($data);//MTIzNDU2
        $x    = 0;
        $len  = strlen($data);
        $l    = strlen($key);
        $char = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }

        $str = sprintf('%010d', $expire ? $expire + time():0);
        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256); //  MDAwMDAwMDAwMLCIrNuCdohq
        }

        return str_replace(['+','/'],['-','_'],base64_encode($str));
    }

    /**
     * 解密方法
     * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
     * @param  string $key  加密密钥
     * @return string
     */
    function system_decrypt($data, $key = ''){
        $key    = md5(empty($key) ? self::DATA_AUTH_KEY : $key);
        $data   = str_replace(['-','_'],['+','/'],$data);
        $mod4   = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        $data   = base64_decode($data);

        $expire = substr($data,0,10);
        $data   = substr($data,10);

        if($expire > 0 && $expire < time()) {
            return '';
        }
        $x      = 0;
        $len    = strlen($data);
        $l      = strlen($key);
        $char   = $str = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            }else{
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return base64_decode($str);
    }
}
