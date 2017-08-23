<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-07-11
 * Time: 14:20
 */

namespace app\components;


use think\Config;

class Encrypt
{
    public $assignToken;

    public function makeSign($data)
    {
        ksort($data['data']);
        $headerString = Data::toUrlParams($data);
        $dataString = Data::toUrlParams($data['data']);
        $token = Config::get('site_token');
        if(isset($this->assignToken)){
            $token .= $this->assignToken;
        }
        $string = $headerString . "&" . $dataString . "&token=" . $token;
        $string = md5($string);
        $result = strtoupper($string);
        return $result;
    }
}