<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/23
 * Time: 23:38
 */

namespace app\components\wechat\wechatpay;


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
        $this->queryResult();
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
        halt($wechatpayResult->result);
        dump($wechatpayResult->status);
        halt($wechatpayResult->errorMessage);
    }
}