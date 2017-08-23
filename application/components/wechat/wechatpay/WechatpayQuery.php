<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/23
 * Time: 23:38
 */

namespace app\components\wechat\wechatpay;


class WechatpayQuery
{
    private static $url = 'https://api.mch.weixin.qq.com/pay/orderquery';
    private static $bizContent;
    private static $result;

    public static function query($outTradeNo)
    {
        self::buildQueryContent($outTradeNo);
        self::execute();
    }

    public static function buildQueryContent($outTradeNo)
    {
        //设置支付信息
        $wechatpayContentBuilder = new WechatpayContentBuilder();
        $wechatpayContentBuilder->setOutTradeNo($outTradeNo);
//        $wechatpayContentBuilder->checkPayContent();
        $bizContent = $wechatpayContentBuilder->getBizContent();
        self::$bizContent = $bizContent;
    }

    public static function execute()
    {
        //集成支付信息并发送支付请求
        $wechatpay = new Wechatpay();
        $wechatpay->setUrl(self::$url);
        $wechatpay->setBizContent(self::$bizContent);
        $result = $wechatpay->execute();
        halt($result);
        self::$result = $result;
    }

    public static function queryResult()
    {

    }
}