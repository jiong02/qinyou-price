<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/17
 * Time: 10:45
 */

namespace app\components\wechat\wechatEnterprise;


use app\components\Curl;
use app\components\Data;
use think\Cache;
use think\Config;

class WechatEnterprise
{
    const FAIL = 'FAIL';
    const SUCCESS = 'SUCCESS';

    private $corpId;
    private $secret;
    protected $agentId;
    public $errorCode;
    public $errorMessage;
    public $status;

    public function init($config = [])
    {
        $defaultConfig = Config::get('wechatEnterprise');
        if (count($config) == 0){
            $config = $defaultConfig ;
        }elseif(is_array($config)){
            $config = array_merge($defaultConfig,$config);
        }
        if (!isset($config['corp_id']) || checkEmpty($config['corp_id'])){
            throw new \think\Exception('缺少corp_id');
        }
        if (!isset($config['secret']) || checkEmpty($config['secret'])){
            throw new \think\Exception('缺少secret');
        }
        if (!isset($config['agent_id']) || checkEmpty($config['agent_id'])){
            throw new \think\Exception('缺少agent_id');
        }
        $this->corpId = $config['corp_id'];
        $this->secret = $config['secret'];
        $this->agentId = $config['agent_id'];
    }



    public function getAccessToken()
    {
        $accessToken = Cache::get($this->corpId . '_access_token');
        if (!$accessToken) {
            $accessToken = $this->generateAccessToken();
        }
        return $accessToken;
    }

    public function generateAccessToken()
    {
        $params['corpid'] = $this->corpId;
        $params['corpsecret'] = $this->secret;
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken';
        $curl = new Curl();
        $params = Data::toUrlParams($params);
        $result = $curl->get($url, $params);
        $result = $this->formatResult($result);
        if ($this->status == self::SUCCESS){
            Cache::set($this->corpId . '_access_token', $result['access_token'], $result['expires_in']);
            return $result['access_token'];
        }else{
            throw new \think\Exception('access_token获取失败');
        }
    }


    public function formatResult($result)
    {
        $this->errorCode = $result['errcode'];
        $this->errorMessage = $result['errmsg'];
        if($this->errorCode == 0 && $this->errorMessage == 'ok'){
            $this->status = self::SUCCESS;
        }else{
            $this->status = self::FAIL;
        }
        return $result;
    }
}