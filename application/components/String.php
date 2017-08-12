<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-07-11
 * Time: 14:23
 */

namespace app\components;


class String
{
    /**
     * 获取随机字符串
     * @param int 字符串长度
     */
    static public function generateNonceString($length = 8, $string = "")
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=`~";
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $string;
    }

    static public function generateNonceStringWithNoSymbol($length = 8, $string = "")
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $string;
    }

    static public function generateNonceStringWithNoCapital($length = 8, $string = "")
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $string;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public static function toUrlParams($data, $buff = "")
    {
        foreach ($data as $k => $v)
        {
            if($v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
}