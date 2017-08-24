<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/14
 * Time: 17:21
 */

namespace app\components\wechat\wechatpay;

use Endroid\QrCode\QrCode;

class WechatpayQrcodePay extends Wechatpay
{
    const TRADE_TYPE = 'NATIVE';

    public function pay($outTradeNo, $body, $fee, $productId)
    {
        //集成支付信息并发送支付请求
        self::buildPayContent($outTradeNo, $body, $fee, $productId);
        $this->setUrl();
        $this->setNotifyUrl();
        $this->execute();
        $result = $this->payResult();
        return $result;
    }

    public function buildPayContent($outTradeNo, $body, $fee, $productId)
    {
        //设置支付信息
        $wechatpayContentBuilder = new WechatpayContentBuilder();
        $wechatpayContentBuilder->setOutTradeNo($outTradeNo);
        $wechatpayContentBuilder->setProductId($productId);
        $wechatpayContentBuilder->SetBody($body);
        $wechatpayContentBuilder->setTotalFee($fee);
        $wechatpayContentBuilder->setTradeType(self::TRADE_TYPE);
//      $wechatpayContentBuilder->checkPayContent();
        $bizContent = $wechatpayContentBuilder->getBizContent();
        $this->setBizContent($bizContent);
    }

    public function payResult()
    {
        //接收并分析返回结果
        $wechatpayResult = new WechatpayResult();
        $wechatpayResult->setResponse($this->result);
        if($wechatpayResult->getStatus() == 'SUCCESS'){
            $qrCode = new QrCode($wechatpayResult->getQrCode());
            header('Content-Type: '.$qrCode->getContentType());
            echo $qrCode->writeString();
            exit;
        }else{
            return getError($wechatpayResult->getErrorMessage());
        }
    }
}