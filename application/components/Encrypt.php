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

    /**
     * 格式化参数格式化成url参数
     */
    public function toUrlParams($data)
    {
        $buff = "";
        foreach ($data as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    public function makeSign($data)
    {
        ksort($data['data']);
        $headerString = $this->toUrlParams($data);
        $dataString = $this->toUrlParams($data['data']);
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