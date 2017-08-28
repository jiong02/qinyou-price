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
    private $method = 'alipay.trade.precreate';

    public function pay($outTradeNo, $body, $fee)
    {
        $this->buildPayContent($outTradeNo, $body, $fee);
        $this->setMethod($this->method);
        $this->execute();
        $result = $this->payResult();
        return $result;
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
        $this->setResponseType($this->method);
        $alipayResult->setResponse($this->result,$this->responseType);
        if($alipayResult->getStatus() == $alipayResult::STATUS_SUCCESS){
            $qrCode = new QrCode($alipayResult->getQrCode());
            header('Content-Type: ' . $qrCode->getContentType());
            echo $qrCode->writeString();
            exit;
        }else{
            return getError($alipayResult->getErrorMessage());
        }
    }
}