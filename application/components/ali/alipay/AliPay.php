<?php

/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/14
 * Time: 17:03
 */

namespace app\components\ali\alipay;

use think\Config;

class AliPay
{
    //应用ID
    private $appId;
    //商户私钥
    private $merchantPrivateKey;
    //支付宝公钥
    private $alipayPublicKey;
    //异步通知地址,只有扫码支付预下单可用
    protected $notifyUrl;
    //支付宝网关
    private $gatewayUrl;
    //签名算法类型
    protected $signType;
    //编码格式
    protected $charset;
    //调用的接口版本
    protected $version;
    //接口返回格式
    protected $format;
    //应用授权TOKEN
    protected $appAuthToken;
    //最大查询重试次数
    protected $maxQueryRetry;
    //查询间隔
    protected $queryDuration;

    protected $params = array();

    public function init($config = [])
    {
        $defaultConfig = Config::get('alipay');
        if (count($config) == 0){
            $config = $defaultConfig ;
        }elseif(is_array($config)){
            $config = array_merge($defaultConfig,$config);
        }
        if (isset($config['app_id']) || checkEmpty($config['app_id'])){
            throw new \think\Exception('缺少app_id');
        }
        if (isset($config['merchant_private_key']) || checkEmpty($config['merchant_private_key'])){
            throw new \think\Exception('缺少merchant_private_key');
        }
        if (isset($config['alipay_public_key']) || checkEmpty($config['alipay_public_key'])){
            throw new \think\Exception('缺少alipay_public_key');
        }
        if (isset($config['notify_url']) || checkEmpty($config['notify_url'])){
            throw new \think\Exception('缺少notify_url');
        }
        if (isset($config['gateway_url']) || checkEmpty($config['gateway_url'])){
            throw new \think\Exception('缺少gateway_url');
        }
        if (isset($config['sign_type']) || checkEmpty($config['sign_type'])){
            throw new \think\Exception('缺少sign_type');
        }
        if (isset($config['charset']) || checkEmpty($config['charset'])){
            throw new \think\Exception('缺少charset');
        }
        if (isset($config['version']) || checkEmpty($config['version'])){
            throw new \think\Exception('缺少version');
        }
        if (isset($config['format']) || checkEmpty($config['format'])){
            throw new \think\Exception('缺少format');
        }
        if (isset($config['app_auth_token']) || checkEmpty($config['app_auth_token'])){
            throw new \think\Exception('缺少app_auth_token');
        }
        if (isset($config['max_query_retry']) || checkEmpty($config['max_query_retry'])){
            throw new \think\Exception('缺少max_query_retry');
        }
        if (isset($config['query_duration']) || checkEmpty($config['query_duration'])){
            throw new \think\Exception('缺少query_duration');
        }
        $this->appId = $config['app_id'];
        $this->merchantPrivateKey = $config['merchant_private_key'];
        $this->alipayPublicKey = $config['alipay_public_key'];
        $this->notifyUrl = $config['notify_url'];
        $this->gatewayUrl = $config['gateway_url'];
        $this->signType = $config['sign_type'];
        $this->charset = $config['charset'];
        $this->version = $config['version'];
        $this->format = $config['format'];
        $this->appAuthToken = $config['app_auth_token'];
        $this->maxQueryRetry = $config['max_query_retry'];
        $this->queryDuration = $config['query_duration'];
    }
}