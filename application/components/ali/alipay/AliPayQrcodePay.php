<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/15
 * Time: 14:51
 */

namespace app\components\ali\alipay;

use Endroid\QrCode\QrCode;

class AliPayQrcodePay
{
    public static $method = 'alipay.trade.precreate';
    public static $resultType =  'alipay_trade_precreate_response';
    public static $bizContent; //支付参数
    public static $result; //支付结果
    public static function pay($outTradeNo, $body, $fee)
    {
        self::setPayContent($outTradeNo, $body, $fee);
        self::execute();
        $result = self::payResult();
        return $result;
    }

    public static function setPayContent($outTradeNo, $body, $fee)
    {
        //设置支付信息
        $alipayContentBuilder = new AlipayContentBuilder();
        $alipayContentBuilder->setOutTradeNo($outTradeNo);
        $alipayContentBuilder->setTotalAmount($fee);
        $alipayContentBuilder->setSubject($body);
        $alipayContentBuilder->checkPayContent();
        $bizContent = $alipayContentBuilder->getBizContent();
        self::$bizContent = $bizContent;
    }

    public static function execute()
    {
        //集成支付信息并发送支付请求
        $alipay = new Alipay();
        $alipay->setMethod(self::$method);
        $alipay->setBizContent(self::$bizContent);
        $result = $alipay->execute();
        self::$result = $result;
    }

    public static function payResult()
    {
        //接收并分析返回结果
        $alipayResult = new AlipayResult();
        $alipayResult->setResponse(self::$result,self::$resultType);
        if($alipayResult->status = 'SUCCESS'){
            $qrCode = new QrCode($alipayResult->qrCode);
            header('Content-Type: '.$qrCode->getContentType());
            echo $qrCode->writeString();
            exit;
        }else{
            return getError('二维码生成失败');
        }
    }
}