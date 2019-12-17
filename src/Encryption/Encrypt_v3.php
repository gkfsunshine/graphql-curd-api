<?php
namespace App\Graph\Encryption;

/**
 * 已经 测试没有问题  满足多数据请求  特殊符号
 *
 * Class Encrypt_v3
 * @package App\Graph\Encryption
 */
class Encrypt_v3
{
    /**
     * @param $data  要加密的字符串
     * @param $key   密钥
     * @return string
     */
    final public static function encrypt($data, $key = 'encrypt')
    {
        $key = md5($key);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= $key{$x};
            $x++;
        }
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
        }
        return base64_encode($str);
    }


    /**
     * @param $data    要解密的字符串
     * @param $key     密钥
     * @return string
     */
    final public static function decrypt($data, $key = 'encrypt')
    {
        $key = md5($key);
        $x = 0;
        $data = base64_decode($data);
        $len = strlen($data);
        $l = strlen($key);
        $char = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return $str;
    }


}


