<?php

namespace app\components;

use think\Request;


class EncryptRequest
{
    public $request;
    public $timestamp;
    public $code;
    public $message;
    public $data;
    public $sign;

    public function __construct()
    {
        $request = Request::instance();
        $this->request = $request;
        $data = $request->param();;
        $this->checkParams($data);
    }

    public function param($name = '', $default = null)
    {
        if (isset($this->data[$name])){
            return $this->data[$name];
        }elseif($default){
            return $default;
        }else{
            return NULL;
        }
    }

    public function checkParams($data)
    {
        if (!isset($data['data'])){
            abort('200','未发送数据');
        }
        $this->data = $data['data'];
        $this->checkTime($data);
        $this->checkSign($data);
    }

    public function checkTime($data)
    {
        if (!isset($data['timestamp'])){
            abort('200','未发送时间戳');
        }
        if (time() - $data['timestamp'] > 300){
            abort('200','请求时间大于5分钟');
        }
        $this->timestamp = $data['timestamp'];
    }

    public function checkSign($data)
    {
        if (!isset($data['sign'])){
            abort('200','未发送签名');
        }
        $encrypt = new Encrypt();
        $sign = $encrypt->makeSign($data);
        if ($sign !== $data['sign']){
            abort('200','签名校验失败');
        }
        $this->sign = $data['sign'];
    }
}