<?php

/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/14
 * Time: 17:03
 */

namespace app\components\ali\alipay;

use app\components\Curl;
use app\components\StringComponents;
use think\Config;

class Alipay
{
    //应用ID
    private $appId;
    //支付宝公钥
    private $alipayPublicKey;
    //商户私钥
    private $merchantPrivateKey;
    //支付宝网关
    private $gatewayUrl;
    //异步通知地址,只有扫码支付预下单可用
    protected $notifyUrl;
    //接口名称
    protected $method;
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

    protected $systemParams = array();

    protected $bizContent = array();

    public function __construct()
    {
        $this->init();
    }

    public function init($config = [])
    {
        $defaultConfig = Config::get('alipay');
        if (count($config) == 0){
            $config = $defaultConfig;
        }elseif(is_array($config)){
            $config = array_merge($defaultConfig,$config);
        }
        if (!array_key_exists('app_id', $config) || checkEmpty($config['app_id'])){
            throw new \think\Exception('缺少app_id');
        }
        if (!array_key_exists('merchant_private_key', $config) || checkEmpty($config['merchant_private_key'])){
            throw new \think\Exception('缺少merchant_private_key');
        }
        if (!array_key_exists('alipay_public_key', $config) || checkEmpty($config['alipay_public_key'])){
            throw new \think\Exception('缺少alipay_public_key');
        }
        if (!array_key_exists('notify_url', $config) || checkEmpty($config['notify_url'])){
            throw new \think\Exception('缺少notify_url');
        }
        if (!array_key_exists('gateway_url', $config) || checkEmpty($config['gateway_url'])){
            throw new \think\Exception('缺少gateway_url');
        }
        if (!array_key_exists('sign_type', $config) || checkEmpty($config['sign_type'])){
            throw new \think\Exception('缺少sign_type');
        }
        if (!array_key_exists('charset', $config) || checkEmpty($config['charset'])){
            throw new \think\Exception('缺少charset');
        }
        if (!array_key_exists('version', $config) || checkEmpty($config['version'])){
            throw new \think\Exception('缺少version');
        }
        if (!array_key_exists('format', $config) || checkEmpty($config['format'])){
            throw new \think\Exception('缺少format');
        }
        if (!array_key_exists('app_auth_token', $config)){
            throw new \think\Exception('缺少app_auth_token');
        }
        if (!array_key_exists('max_query_retry', $config) || checkEmpty($config['max_query_retry'])){
            throw new \think\Exception('缺少max_query_retry');
        }
        if (!array_key_exists('query_duration', $config) || checkEmpty($config['query_duration'])){
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

    public function getGatewayUrl()
    {
       return $this->gatewayUrl;
    }

    public function setAppAuthToken($appAuthToken)
    {
        $this->systemParams['app_auth_token'] = $appAuthToken;
        $this->appAuthToken = $appAuthToken;
    }

    public function getAppAuthToken()
    {
        return $this->appAuthToken;
    }

    public function setNotifyUrl($notifyUrl)
    {
        $this->systemParams['notify_url'] = $notifyUrl;
        $this->notifyUrl = $notifyUrl;
    }

    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    public function setMethod($method = '')
    {

        $this->method = $method;
        $this->systemParams['method'] = $method;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getAlipayPublicKey()
    {
        return $this->alipayPublicKey;
    }

    public function getSystemParams()
    {
        $this->systemParams["app_id"] = $this->appId;
        $this->systemParams["format"] = $this->format;
        $this->systemParams["charset"] = $this->charset;
        $this->systemParams["sign_type"] = $this->signType;
        $this->systemParams["timestamp"] = date ("Y-m-d H:i:s");
        $this->systemParams["version"] = $this->version;
        $this->systemParams["notify_url"] = $this->getNotifyUrl();
        $this->systemParams["app_auth_token"] = $this->getAppAuthToken();
        return $this->systemParams;
    }

    public function checkSystemParams()
    {
        if (!$this->getMethod()){
            throw new \think\Exception('缺少method');
        }
        if (!$this->getBizContent()){
            throw new \think\Exception('缺少biz_content');
        }
    }

    public function setBizContent($bizContent)
    {
        $this->bizContent = json_encode($bizContent, JSON_UNESCAPED_UNICODE);
    }

    public function getBizContent()
    {
        return $this->bizContent;
    }

    protected function generateSign($data)
    {
        ksort($data);
        $data = StringComponents::toUrlParams($data);
        $sign = $this->sign($data);
        return $sign;
    }

    protected function sign($data) {
        $priKey = $this->merchantPrivateKey;
        $result = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($priKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        if (!$result){
            throw new \think\Exception('您使用的私钥格式错误，请检查RSA私钥配置');
        }
        openssl_sign($data, $sign, $result, OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);
        return $sign;
    }

    public function verifySign($data, $sign) {
        $pubKey = $this->getAlipayPublicKey();
        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        if(!$res){
            throw new \think\Exception('支付宝RSA公钥错误。请检查公钥文件格式是否正确');
        }
        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);

        return $result;
    }

    public function formatSignData($signData)
    {
        $signData = json_encode($signData, JSON_UNESCAPED_UNICODE);
        return $signData;
    }

    public function execute()
    {
        $this->checkSystemParams();
        $systemParams = $this->getSystemParams();
        $bizContent['biz_content'] = $this->getBizContent();
        $signParams = array_merge($systemParams,$bizContent);
        $systemParams['sign'] = $this->generateSign($signParams);
        $gateWayUrl = $this->getGatewayUrl();
        $url = $gateWayUrl . '?' .http_build_query($systemParams);
        $curl = new Curl();
        $result = $curl->post($url,$bizContent);
        return $result;
    }
}