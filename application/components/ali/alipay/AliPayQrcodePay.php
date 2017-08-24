<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/15
 * Time: 14:51
 */

namespace app\components\ali\alipay;

use Endroid\QrCode\QrCode;

class AliPayQrcodePay extends Alipay
{
    public static $method = 'alipay.trade.precreate';
    public static $responseType =  'alipay_trade_precreate_response';
    public static function pay($outTradeNo, $body, $fee)
    {
        self::buildPayContent($outTradeNo, $body, $fee);
        self::setMethod(self::$method);
        self::execute();
        self::payResult();
        return self::$result;
    }

    public function buildPayContent($outTradeNo, $body, $fee)
    {
        //设置支付信息
        $alipayContentBuilder = new AlipayContentBuilder();
        $alipayContentBuilder->setOutTradeNo($outTradeNo);
        $alipayContentBuilder->setTotalAmount($fee);
        $alipayContentBuilder->setSubject($body);
        $bizContent = $alipayContentBuilder->getBizContent();
        $this->setBizContent($bizContent);
    }

    public function payResult()
    {
        //接收并分析返回结果
        $alipayResult = new AlipayResult();
        $alipayResult->setResponse($this->result,self::$responseType);
        if($alipayResult->status == 'SUCCESS'){
            $qrCode = new QrCode($alipayResult->qrCode);
            header('Content-Type: '.$qrCode->getContentType());
            echo $qrCode->writeString();
            exit;
        }else{
            return getError($alipayResult->errorMessage);
        }
    }
}