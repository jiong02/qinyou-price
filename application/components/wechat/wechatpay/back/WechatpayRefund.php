<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/24
 * Time: 16:55
 */

namespace app\components\wechat\wechatpay;


use app\components\Data;

class WechatpayRefund extends WechatpayService
{
    private $outRequestNo;
    private $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
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


    public function buildQueryContent($outTradeNo, $refundFee, $totalFee)
    {
        //设置支付信息
        $this->setOutRequestNo();
        $wechatpayContentBuilder = new WechatpayContentBuilder();
        $wechatpayContentBuilder->setOutTradeNo($outTradeNo);
        $wechatpayContentBuilder->setRefundFree($refundFee);
        $wechatpayContentBuilder->setTotalFee($totalFee);
        $wechatpayContentBuilder->setOutRefundNo($this->getOutRequestNo());
        $bizContent = $wechatpayContentBuilder->getBizContent();
        $this->bizContent = $bizContent;
    }

    public function refund($outTradeNo, $refundFee, $totalFee)
    {
        $this->buildQueryContent($outTradeNo, $refundFee, $totalFee);
        $this->setUrl($this->url);
        $this->setBizContent($this->bizContent);
        $this->execute();
        return $this->refundResult();
    }

    public function refundResult()
    {
        $wechatpayResult = new WechatpayResult();
        $wechatpayResult->setResponse($this->result);
        if($wechatpayResult->getStatus() == $wechatpayResult::SUCCESS){

        }else{
            return getError($wechatpayResult->getErrorCode());
        }
    }




}