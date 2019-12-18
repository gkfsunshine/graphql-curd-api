<?php
namespace Graph\Curd\Encryption;

class Encrypt_v5
{
    /**
     * 简单对称加密算法之加密
     * @param String $string 需要加密的字串
     * @param String $skey 加密EKY
     * @return String
     */
    function encode_v1($string = '', $skey = 'mutephp') {
        $strArr = str_split(base64_encode($string));
        $strCount = count($strArr);
        foreach (str_split($skey) as $key => $value){
            $key < $strCount && $strArr[$key].=$value;
        }
        return str_replace(['=', '+', '/'], ['O0O0O', 'o000o', 'oo00o'], join('', $strArr));
    }

    /**
     * 简单对称加密算法之解密
     * @param String $string 需要解密的字串
     * @param String $skey 解密KEY
     * @return String
     */
    function decode_v1($string = '', $skey = 'mutephp')
    {
        $strArr = str_split(str_replace(['O0O0O', 'o000o', 'oo00o'], ['=', '+', '/'], $string), 2);
        $strCount = count($strArr);
        foreach (str_split($skey) as $key => $value) {
            $key <= $strCount && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
        }
        return base64_decode(join('', $strArr));
    }
}
