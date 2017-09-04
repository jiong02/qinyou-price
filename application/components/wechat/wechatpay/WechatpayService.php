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

class WechatpayService
{
    //微信分配的公众账号ID。
    private $appId;
    private $appSecret;
    //微信支付分配的商户号。
    private $merchantId;
    private $key;
    private $sslCertPath;
    private $sslKeyPath;
    //接收微信支付异步通知回调地址。
    private $notifyUrl;
    private $timeoutExpress;
    private $maxQueryRetry;
    private $queryDuration;

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
        if (!array_key_exists('timeout_express', $config) || checkEmpty($config['timeout_express'])){
            throw new Exception('缺少timeout_express');
        }
        if (!array_key_exists('max_query_retry', $config) || checkEmpty($config['max_query_retry'])){
            throw new Exception('缺少max_query_retry');
        }
        if (!array_key_exists('query_duration', $config) || checkEmpty($config['query_duration'])){
            throw new Exception('缺少query_duration');
        }
        if (!array_key_exists('ssl_key_path', $config) || checkEmpty($config['ssl_key_path'])){
            throw new Exception('缺少ssl_key_path');
        }
        if (!array_key_exists('ssl_cert_path', $config) || checkEmpty($config['ssl_cert_path'])){
            throw new Exception('缺少ssl_cert_path');
        }
        $this->appId = $config['app_id'];
        $this->appSecret = $config['app_secret'];
        $this->merchantId = $config['merchant_id'];
        $this->key = $config['key'];
        $this->notifyUrl = $config['notify_url'];
        $this->timeoutExpress = $config['timeout_express'];
        $this->maxQueryRetry = $config['max_query_retry'];
        $this->queryDuration = $config['query_duration'];
        $this->sslCertPath = $config['ssl_cert_path'];
        $this->sslKeyPath = $config['ssl_key_path'];
    }

    public function qrcodePay($contentBuilder)
    {
        $wechatpayRequest = new WechatpayRequest();
        $startTime = date('YmdHis');
        $contentBuilder->setTimeStart($startTime);
        $contentBuilder->setTimeExpire($startTime + $this->timeoutExpress);
        $contentBuilder->setSpbillCreateIp($_SERVER['REMOTE_ADDR']);
        $contentBuilder->setNotifyUrl($this->notifyUrl);
        $contentBuilder->setTradeType($wechatpayRequest::TRADE_TYPE_NATIVE);
        $bizContent = $contentBuilder->getBizContent();
        $wechatpayRequest->setBizContent($bizContent);
        $wechatpayRequest->setUrl($wechatpayRequest::URL_UNIFIEDORDER);
        $response = $this->clientExecute($wechatpayRequest);
        $wechatpayResult = new WechatResult($response);
        $wechatpayResult->setTradeStatus('FAILD');
        if ($response['return_code'] == 'SUCCESS' && $response['return_msg'] == 'OK'){
            if ($response['result_code'] == 'SUCCESS'){
                $wechatpayResult->setTradeStatus('SUCCESS');
            }
        }
        return $wechatpayResult;
    }

    public function refund($contentBuilder)
    {
        $bizContent = $contentBuilder->getBizContent();
        $wechatpayRequest = new WechatpayRequest();
        $wechatpayRequest->setBizContent($bizContent);
        $wechatpayRequest->setUrl($wechatpayRequest::URL_REFUND);
        $wechatpayRequest->setSslCertPath($this->sslCertPath);
        $wechatpayRequest->setSslKeyPath($this->sslKeyPath);
        $response = $this->clientExecute($wechatpayRequest,true);
        return $response;
    }

    public function refundQuery($contentBuilder)
    {
        $bizContent = $contentBuilder->getBizContent();
        $wechatpayRequest = new WechatpayRequest();
        $wechatpayRequest->setBizContent($bizContent);
        $wechatpayRequest->setUrl($wechatpayRequest::URL_REFUND_QUERY);
        $response = $this->clientExecute($wechatpayRequest);
        return $response;
    }

    public function query($contentBuilder)
    {
        $bizContent = $contentBuilder->getBizContent();
        $wechatpayRequest = new WechatpayRequest();
        $wechatpayRequest->setBizContent($bizContent);
        $wechatpayRequest->setUrl($wechatpayRequest::URL_QUERY);
        $response = $this->clientExecute($wechatpayRequest);
        return $response;
    }

    public function queryResult($contentBuilder)
    {
        $response = $this->query($contentBuilder);
        return $this->setQueryResult($response);
    }

    // 轮询查询订单支付结果
    public function loopQueryResult($queryContentBuilder){
        $queryResult = NULL;
        for ($i = 1;$i < $this->maxQueryRetry;$i++){

            sleep($this->queryDuration);

            $queryResponse = $this->query($queryContentBuilder);
            if(!empty($queryResponse)){
                if($this->stopQuery($queryResponse)){
                    return $this->setQueryResult($queryResponse);
                }
                $queryResult = $queryResponse;
            }
        }
        return $this->setQueryResult($queryResult);
    }

    //判断是否返回成功
    public function querySuccess($response)
    {
        $result = false;
        if ($response['return_code'] == 'SUCCESS' && $response['return_msg'] == 'OK'){
            if ($response['result_code'] == 'SUCCESS' && $response['trade_state'] == 'SUCCESS'){
                $result = true;
            }
        }
        return $result;
    }

    public function setQueryResult($response)
    {
        $wechatpayResult = new WechatResult($response);
        if ($this->querySuccess($response)){
            $wechatpayResult->setTradeStatus('SUCCESS');
        }else{
            $wechatpayResult->setTradeStatus('FAILD');
        }
        return $wechatpayResult;
    }

    public function stopQuery($response)
    {
        $result = true;
        if ($response['return_code'] == 'SUCCESS' && $response['return_msg'] == 'OK'){
            if ($response['result_code'] == 'SUCCESS'){
                if( $response['trade_state'] == 'NOTPAY' || $response['trade_state'] == 'USERPAYING'){
                    $result = false;
                }
            }
        }
        return $result;
    }

    public function clientExecute($request, $useCert = false)
    {
        $wecatPayClient = new WechatpayClient();
        $wecatPayClient->setAppId($this->appId);
        $wecatPayClient->setKey($this->key);
        $wecatPayClient->setAppSecret($this->appSecret);
        $wecatPayClient->setMerchantId($this->merchantId);
        $result = $wecatPayClient->execute($request, $useCert);
        return $result;
    }
}