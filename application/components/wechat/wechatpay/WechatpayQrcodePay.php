<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/14
 * Time: 17:21
 */

namespace app\components\wechat\wechatpay;

use Endroid\QrCode\QrCode;

class WechatpayQrcodePay
{
    const TRADE_TYPE = 'NATIVE';
    public static $bizContent; //支付参数
    public static $result; //支付结果
    public static function pay($outTradeNo, $body, $fee, $productId)
    {
        self::buildPayContent($outTradeNo, $body, $fee, $productId);
        self::execute();
        $result = self::payResult();
        return $result;
    }

    public static function buildPayContent($outTradeNo, $body, $fee, $productId)
    {
        //设置支付信息
        $wechatpayContentBuilder = new WechatpayContentBuilder();
        $wechatpayContentBuilder->setOutTradeNo($outTradeNo);
        $wechatpayContentBuilder->setProductId($productId);
        $wechatpayContentBuilder->SetBody($body);
        $wechatpayContentBuilder->setTotalFee($fee);
        $wechatpayContentBuilder->setTradeType(self::TRADE_TYPE);
        $wechatpayContentBuilder->checkPayContent();
        $bizContent = $wechatpayContentBuilder->getBizContent();
        self::$bizContent = $bizContent;
    }

    public static function execute()
    {
        //集成支付信息并发送支付请求
        $wechatpay = new Wechatpay();
        $wechatpay->setUrl();
        $wechatpay->setBizContent(self::$bizContent);
        $result = $wechatpay->execute();
        self::$result = $result;
    }

    public static function payResult()
    {
        //接收并分析返回结果
        $wechatpayResult = new WechatpayResult();
        $wechatpayResult->setResponse(self::$result);
        if($wechatpayResult->status == 'SUCCESS'){
            $qrCode = new QrCode($wechatpayResult->qrCode);
            header('Content-Type: '.$qrCode->getContentType());
            echo $qrCode->writeString();
            exit;
        }else{
            return getError($wechatpayResult->errorMessage);
        }
    }
}