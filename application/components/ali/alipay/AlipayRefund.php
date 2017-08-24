<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/24
 * Time: 17:15
 */

namespace app\components\ali\alipay;


use app\components\Data;

class AlipayRefund extends Alipay
{
    private $outTradeNo;
    private $method = 'alipay.trade.refund';
    private $responseType = 'alipay_trade_refund_response';
    public function setOutRequestNo($outTradeNo = '')
    {
        if (!$outTradeNo){
            $outTradeNo = Data::getUniqueString();
        }
        $this->outTradeNo = $outTradeNo;
    }

    public function getOutRequestNo()
    {
        return $this->outTradeNo;
    }

    public function refund($outTradeNo, $refundAmount)
    {
        $this->buildQueryContent($outTradeNo, $refundAmount);
        $this->setMethod($this->method);
        $this->execute();
        return $this->refundResult();
    }

    public function buildQueryContent($outTradeNo, $refundAmount)
    {
        $this->setOutRequestNo();
        $alipayContentBuilder = new AlipayContentBuilder();
        $alipayContentBuilder->setOutTradeNo($outTradeNo);
        $alipayContentBuilder->setRefundAmount($refundAmount);
        $alipayContentBuilder->setOutRequestNo($this->getOutRequestNo());
        $bizContent = $alipayContentBuilder->getBizContent();
        $this->setBizContent($bizContent);
    }

    public function refundResult()
    {
        $alipayResult = new AlipayResult();
        $alipayResult->setResponse($this->result,$this->responseType);
        if($alipayResult->getStatus() == 'SUCCESS'){

        }else{
            return getError($alipayResult->getErrorMessage());
        }
    }
}