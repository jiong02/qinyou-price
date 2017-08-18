<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/17
 * Time: 14:43
 */

namespace app\components;


class Math
{
    public static function generateRandomNumber($length = 4)
    {
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return mt_rand($min, $max);
    }
}