<?php

/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/14
 * Time: 17:03
 */

namespace app\components\ali\alipay;

use app\components\ali\alipay\AlipayRequest;
use app\components\Curl;
use app\components\Data;
use think\Exception;
use think\Config;

class AlipayService
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
    private $notifyUrl;
    //订单失效时长
    private $timeoutExpress;
    //签名算法类型
    private $signType;
    //编码格式
    private $charset;
    //调用的接口版本
    private $version;
    //接口返回格式
    private $format;
    //最大查询重试次数
    private $maxQueryRetry;
    //查询间隔
    private $queryDuration;

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
            throw new Exception('缺少app_id');
        }
        if (!array_key_exists('merchant_private_key', $config) || checkEmpty($config['merchant_private_key'])){
            throw new Exception('缺少merchant_private_key');
        }
        if (!array_key_exists('alipay_public_key', $config) || checkEmpty($config['alipay_public_key'])){
            throw new Exception('缺少alipay_public_key');
        }
        if (!array_key_exists('notify_url', $config) || checkEmpty($config['notify_url'])){
            throw new Exception('缺少notify_url');
        }
        if (!array_key_exists('timeout_express', $config) || checkEmpty($config['timeout_express'])){
            throw new Exception('缺少timeout_express');
        }
        if (!array_key_exists('gateway_url', $config) || checkEmpty($config['gateway_url'])){
            throw new Exception('缺少gateway_url');
        }
        if (!array_key_exists('sign_type', $config) || checkEmpty($config['sign_type'])){
            throw new Exception('缺少sign_type');
        }
        if (!array_key_exists('charset', $config) || checkEmpty($config['charset'])){
            throw new Exception('缺少charset');
        }
        if (!array_key_exists('version', $config) || checkEmpty($config['version'])){
            throw new Exception('缺少version');
        }
        if (!array_key_exists('format', $config) || checkEmpty($config['format'])){
            throw new Exception('缺少format');
        }
        if (!array_key_exists('max_query_retry', $config) || checkEmpty($config['max_query_retry'])){
            throw new Exception('缺少max_query_retry');
        }
        if (!array_key_exists('query_duration', $config) || checkEmpty($config['query_duration'])){
            throw new Exception('缺少query_duration');
        }
        $this->appId = $config['app_id'];
        $this->merchantPrivateKey = $config['merchant_private_key'];
        $this->alipayPublicKey = $config['alipay_public_key'];
        $this->notifyUrl = $config['notify_url'];
        $this->timeoutExpress = $config['timeout_express'];
        $this->gatewayUrl = $config['gateway_url'];
        $this->signType = $config['sign_type'];
        $this->charset = $config['charset'];
        $this->version = $config['version'];
        $this->format = $config['format'];
        $this->maxQueryRetry = $config['max_query_retry'];
        $this->queryDuration = $config['query_duration'];
    }

    public function qrcodePay($contentBuilder)
    {
        $contentBuilder->setTimeoutExpress($this->timeoutExpress);
        $bizContent = $contentBuilder->getBizContent();
        $qrcodePayRequest = new AlipayRequest();
        $qrcodePayRequest->setMethod($qrcodePayRequest::METHOD_PRECREATE);
        $qrcodePayRequest->setBizContent($bizContent);
        $qrcodePayRequest->setNotifyUrl($this->notifyUrl);
        $response = $this->clientExecute($qrcodePayRequest);
        $result = new AlipayResult($response);
        if(!empty($response)&&("10000"==$response->code)){
            $result->setTradeStatus("SUCCESS");
        } elseif($this->tradeError($response)){
            $result->setTradeStatus("UNKNOWN");
        } else {
            $result->setTradeStatus("FAILED");
        }
        return $result;
    }

    public function query($contentBuilder)
    {
        $bizContent = $contentBuilder->getBizContent();
        $queryRequest = new AlipayRequest();
        $queryRequest->setMethod($queryRequest::METHOD_QUERY);
        $queryRequest->setBizContent($bizContent);
        $response = $this->clientExecute($queryRequest);
        return $response;
    }

    public function queryResult($contentBuilder)
    {
        $response = $this->query($contentBuilder);

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

    protected function setQueryResult($response)
    {
        $result = new AlipayResult($response);
        if($this->querySuccess($response)){
            // 查询返回该订单交易支付成功
            $result->setTradeStatus("SUCCESS");

        } elseif ($this->tradeError($response)){
            //查询发生异常或无返回，交易状态未知
            $result->setTradeStatus("UNKNOWN");
        } else {
            //其他情况均表明该订单号交易失败
            $result->setTradeStatus("FAILED");
        }
        return $result;
    }

    // 判断是否停止查询
    protected function stopQuery($response){
        if("10000"==$response->code){
            if("TRADE_FINISHED"==$response->trade_status||
                "TRADE_SUCCESS"==$response->trade_status||
                "TRADE_CLOSED"==$response->trade_status){
                return true;
            }
        }
        return false;
    }

    public function refund($contentBuilder)
    {
        $bizContent = $contentBuilder->getBizContent();
        $refundRequest = new AlipayRequest();
        $refundRequest->setMethod($refundRequest::METHOD_REFUND);
        $refundRequest->setBizContent($bizContent);
        $response = $this->clientExecute($refundRequest);
        $result = new AlipayResult($response);
        if(!empty($response)&&("10000"==$response->code)){
            $result->setTradeStatus("SUCCESS");
        } elseif ($this->tradeError($response)){
            $result->setTradeStatus("UNKNOWN");
        } else {
            $result->setTradeStatus("FAILED");
        }
        return $result;
    }

    public function refundQuery($contentBuilder)
    {
        $bizContent = $contentBuilder->getBizContent();
        $refundQueryRequest = new AlipayRequest();
        $refundQueryRequest->setMethod($refundQueryRequest::METHOD_REFUND_QUERY);
        $refundQueryRequest->setBizContent($bizContent);
        $response = $this->clientExecute($refundQueryRequest);
        $result = new AlipayResult($response);
    }

    public function clientExecute($request)
    {
        $alipayClient = new AlipayClient();
        $alipayClient->setAppId($this->appId);
        $alipayClient->setAlipayPublicKey($this->alipayPublicKey);
        $alipayClient->setMerchantPrivateKey($this->merchantPrivateKey);
        $alipayClient->setFormat($this->format);
        $alipayClient->setCharset($this->charset);
        $alipayClient->setSignType($this->signType);
        $result = $alipayClient->execute($request);
        return $result;
    }

    // 交易异常，或发生系统错误
    protected function tradeError($response){
        return empty($response) || $response->code == "20000";
    }

    // 查询返回“支付成功”
    protected function querySuccess($queryResponse){
        return !empty($queryResponse) &&
            $queryResponse->code == "10000" &&
            ($queryResponse->trade_status == "TRADE_SUCCESS" ||
                $queryResponse->trade_status == "TRADE_FINISHED");
    }
}