<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/23
 * Time: 23:38
 */

namespace app\components\wechat\wechatpay;


use think\Exception;

class WechatpayQuery extends Wechatpay
{
    private $url = 'https://api.mch.weixin.qq.com/pay/orderquery';

    public function query($outTradeNo)
    {
        $this->buildQueryContent($outTradeNo);
        //集成支付信息并发送支付请求
        $this->setUrl($this->url);
        $this->setBizContent($this->bizContent);
        $this->execute();
        return $this->queryResult();
    }

    public function buildQueryContent($outTradeNo)
    {
        //设置支付信息
        $wechatpayContentBuilder = new WechatpayContentBuilder();
        $wechatpayContentBuilder->setOutTradeNo($outTradeNo);
        $bizContent = $wechatpayContentBuilder->getBizContent();
        $this->bizContent = $bizContent;
    }

    public function queryResult()
    {
        $wechatpayResult = new WechatpayResult();
        $wechatpayResult->setResponse($this->result);
        if($wechatpayResult->getStatus() == $wechatpayResult::SUCCESS){

        }else{
            return getError($wechatpayResult->getErrorCode());
        }
    }

    public function loopQuery($outTradeNo)
    {
        $wechatpayResult = new WechatpayResult();
        for ($i = 0; $i < $this->maxQueryRetry; $i++){
            try{
                sleep($this->queryDuration);
            }catch (Exception $e){
                $wechatpayResult->setStatus($wechatpayResult::FAIL);
                $wechatpayResult->setErrorCode($e->getCode());
                $wechatpayResult->setErrorMessage($e->getMessage());
            }
            $this->buildQueryContent($outTradeNo);
            $this->setUrl($this->url);
            $this->setBizContent($this->bizContent);
            $this->execute();
            $wechatpayResult->setResponse($this->result);
            if($wechatpayResult->stopQuery()){
                return $this->loopQueryResult($wechatpayResult);
            }
        }
        return $this->loopQueryResult($wechatpayResult);
    }

    public function loopQueryResult($wechatpayResult)
    {
        if($wechatpayResult->getStatus() == $wechatpayResult::SUCCESS){

        }else{
            return getError($wechatpayResult->getErrorCode());
        }
    }
}