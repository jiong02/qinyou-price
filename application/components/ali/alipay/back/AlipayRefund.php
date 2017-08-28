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
    private $outRequestNo;
    private $method = 'alipay.trade.refund';

    /**
     * @return mixed
     */
    public function getOutRequestNo()
    {
        return $this->outRequestNo;
    }

    /**
     * @param mixed $outRequestNo
     */
    public function setOutRequestNo($outRequestNo = '')
    {
        if (!$outRequestNo){
            $outRequestNo = Data::getUniqueString();
        }
        $this->outRequestNo = $outRequestNo;
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
        $this->setResponseType($this->method);
        $alipayResult->setResponse($this->result,$this->getResponseType());
        if($alipayResult->getStatus() == 'SUCCESS'){

        }else{
            return getError($alipayResult->getErrorMessage());
        }
    }





}