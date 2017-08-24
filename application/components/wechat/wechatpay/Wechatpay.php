<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/14
 * Time: 17:02
 */

namespace app\components\wechat\wechatpay;


use app\components\Curl;
use app\components\Data;
use think\Config;
use think\Exception;

class Wechatpay
{
    private $key;
    private $appSecret;
    private $sslCertPath;
    private $sslKeyPath;
    //微信分配的公众账号ID。
    private $appId;
    //微信支付分配的商户号。
    private $merchantId;
    //随机字符串，不长于32位。推荐随机数生成算法。
    private $nonceString;
    //APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP。
    private $spbillCreateIP;
    //接收微信支付异步通知回调地址。
    private $url;
    protected $notifyUrl;
    protected $sign;
    protected $unifiedOrderUrl = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    protected $systemParams = array();
    protected $bizContent = array();
    protected $result;
    public function __construct()
    {
        $this->init();
    }

    public function init($config = [])
    {
        $defaultConfig = Config::get('wechatpay');
        if (count($config) == 0){
            $config = $defaultConfig;
        }elseif(is_array($config)){
            $config = array_merge($defaultConfig,$config);
        }
        if (!array_key_exists('app_id', $config) || checkEmpty($config['app_id'])){
            throw new Exception('缺少app_id');
        }
        if (!array_key_exists('app_secret', $config) || checkEmpty($config['app_secret'])){
            throw new Exception('缺少app_secret');
        }
        if (!array_key_exists('merchant_id', $config) || checkEmpty($config['merchant_id'])){
            throw new Exception('缺少merchant_id');
        }
        if (!array_key_exists('key', $config) || checkEmpty($config['key'])){
            throw new Exception('缺少key');
        }
        if (!array_key_exists('notify_url', $config) || checkEmpty($config['notify_url'])){
            throw new Exception('缺少notify_url');
        }
        $this->appId = $config['app_id'];
        $this->appSecret = $config['app_secret'];
        $this->merchantId = $config['merchant_id'];
        $this->key = $config['key'];
        $this->notifyUrl = $config['notify_url'];
    }

    public function setAppId($appId)
    {
        $this->appId = $appId;
        $this->systemParams['appid'] = $appId;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
        $this->systemParams['mch_id'] = $merchantId;
    }

    public function getMerchantId()
    {
        return $this->merchantId;
    }

    public function setNonceString($nonceString)
    {
        $this->nonceString = $nonceString;
        $this->systemParams['nonce_str'] = $nonceString;
    }

    public function getNonceString()
    {
        return $this->nonceString;
    }

    public function SetNotifyUrl($notifyUrl = '')
    {
        if (!$notifyUrl){
            $notifyUrl = $this->notifyUrl;
        }
        $this->systemParams['notify_url'] = $notifyUrl;
    }

    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    public function setUrl($url = '')
    {
        if (!$url){
            $url = $this->unifiedOrderUrl;
        }
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setSpbillCreateIP($spbillCreateIP)
    {
        $this->spbillCreateIP = $spbillCreateIP;
        $this->systemParams['spbill_create_ip'] = $spbillCreateIP;
    }

    public function getSpbillCreateIP()
    {
        return $this->spbillCreateIP;
    }

    public function MakeSign($signData)
    {
        //签名步骤一：按字典序排序参数
        ksort($signData);
        $string = Data::ToUrlParams($signData);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $this->key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    public function setSign($signData)
    {
        $sign = $this->MakeSign($signData);
        $this->sign = $sign;
    }

    public function verifySign($signData, $sign)
    {
        $this->setSign($signData);
        if ($sign == $this->getSign()){
            return true;
        }else{
            return false;
        }
    }

    public function getSign()
    {
        return $this->sign;
    }

    public function setBizContent($bizContent)
    {
        $this->bizContent = $bizContent;
    }

    public function getBizContent()
    {
        return $this->bizContent;
    }

    public function checkSystemParams()
    {
        if (!$this->getBizContent()){
            throw new Exception('缺少biz_content');
        }
    }

    public function getSystemParams()
    {
        $this->setAppId($this->appId);
        $this->setMerchantId($this->merchantId);
        if (!$this->getNonceString()){
            $nonceString = Data::generateNonceString();
            $this->setNonceString($nonceString);
        }
        if (!$this->getSpbillCreateIP()){
            $this->setSpbillCreateIP($_SERVER['REMOTE_ADDR']);
        }
        return $this->systemParams;
    }

    public function execute()
    {
        $systemParams = $this->getSystemParams();
        $bizContent = $this->getBizContent();
        $this->checkSystemParams();
        $apiParams = array_merge($systemParams, $bizContent);
        $this->setSign($apiParams);
        $apiParams['sign'] = $this->getSign();
        $xmlApiParams = Data::formatArraytoXml($apiParams);
        $curl = new Curl();
        $result = $curl->post($this->url,$xmlApiParams);
        $result = Data::formatXmlToArray($result);
        $this->result = $result;
    }
}