<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/17
 * Time: 11:44
 */

namespace app\components;


class Curl
{
    public $timeout = 30;
    public $url = '';
    public $params;
    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function toUrlParams($params, $buff = "")
    {
        foreach ($params as $k => $v)
        {
            if($v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        $this->url = $this->url . '?' . $buff;
    }

    public function get($url, $params)
    {
        $this->setUrl($url);
        $this->toUrlParams($params);
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以json形式返回
        $result = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($result,true);
        return $data;
    }

    public function post($url, $params)
    {
        $this->setUrl($url);
        $this->setParams($params);
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch,CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
        //运行curl，结果以json形式返回
        $result = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($result,true);
        return $data;
    }
}