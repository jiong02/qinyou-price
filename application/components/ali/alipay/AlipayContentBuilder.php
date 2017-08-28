<?php

/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/27
 * Time: 14:33
 */

namespace app\components\ali\alipay;

class AlipayContentBuilder
{
    private $outTradeNo;
    private $sellerId;
    private $totalAmount;
    private $discountableAmount;
    private $subject;
    private $body;
    private $goodsDetail = array();
    private $operatorId;
    private $storeId;
    private $terminalId;
    private $extendParams = array();
    private $timeoutExpress;

    //退款接口参数
    private $refundAmount;
    private $outRequestNo;

    private $bizParams = array();

    private $bizContent = NULL;
    /**
     * @return mixed
     */
    public function getOutTradeNo()
    {
        return $this->outTradeNo;
    }

    /**
     * @param mixed $outTradeNo
     */
    public function setOutTradeNo($outTradeNo)
    {
        $this->outTradeNo = $outTradeNo;
        $this->bizParams['out_trade_no'] = $outTradeNo;
    }

    /**
     * @return mixed
     */
    public function getSellerId()
    {
        return $this->sellerId;
    }

    /**
     * @param mixed $sellerId
     */
    public function setSellerId($sellerId)
    {
        $this->sellerId = $sellerId;
        $this->bizParams['seller_id'] = $sellerId;
    }

    /**
     * @return mixed
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param mixed $totalAmount
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
        $this->bizParams['total_amount'] = $totalAmount;
    }

    /**
     * @return mixed
     */
    public function getDiscountableAmount()
    {
        return $this->discountableAmount;
    }

    /**
     * @param mixed $discountableAmount
     */
    public function setDiscountableAmount($discountableAmount)
    {
        $this->discountableAmount = $discountableAmount;
        $this->bizParams['discountable_amount'] = $discountableAmount;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        $this->bizParams['subject'] = $subject;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
        $this->bizParams['body'] = $body;
    }

    /**
     * @return array
     */
    public function getGoodsDetail()
    {
        return $this->goodsDetail;
    }

    /**
     * @param array $bizParams
     */
    public function setGoodsDetail($goodsDetail)
    {
        $this->goodsDetail = $goodsDetail;
        $this->bizParams['goods_detail'] = $goodsDetail;
    }

    /**
     * @return mixed
     */
    public function getOperatorId()
    {
        return $this->operatorId;
    }

    /**
     * @param mixed $operatorId
     */
    public function setOperatorId($operatorId)
    {
        $this->operatorId = $operatorId;
        $this->bizParams['operator_id'] = $operatorId;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param mixed $storeId
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        $this->bizParams['store_id'] = $storeId;
    }

    /**
     * @return mixed
     */
    public function getTerminalId()
    {
        return $this->terminalId;
    }

    /**
     * @param mixed $terminalId
     */
    public function setTerminalId($terminalId)
    {
        $this->terminalId = $terminalId;
        $this->bizParams['terminal_id'] = $terminalId;
    }

    /**
     * @return array
     */
    public function getExtendParams()
    {
        return $this->extendParams;
    }

    /**
     * @param array $bizParams
     */
    public function setExtendParams($extendParams)
    {
        $this->extendParams = $extendParams;
        $this->bizParams['extend_params'] = $extendParams;
    }

    /**
     * @return mixed
     */
    public function getTimeoutExpress()
    {
        return $this->timeoutExpress;
    }

    /**
     * @param mixed $timeoutExpress
     */
    public function setTimeoutExpress($timeoutExpress)
    {
        $this->timeoutExpress = $timeoutExpress;
        $this->bizParams['timeout_express'] = $timeoutExpress;
    }

    /**
     * @return mixed
     */
    public function getRefundAmount()
    {
        return $this->refundAmount;
    }

    /**
     * @param mixed $refundAmount
     */
    public function setRefundAmount($refundAmount)
    {
        $this->refundAmount = $refundAmount;
        $this->bizParams['refund_amount'] = $refundAmount;
    }

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
    public function setOutRequestNo($outRequestNo)
    {
        $this->outRequestNo = $outRequestNo;
        $this->bizParams['out_request_no'] = $outRequestNo;
    }

    /**
     * @return null or json
     */
    public function getBizContent()
    {
        if(!empty($this->bizParams)){
            $this->bizContent = json_encode($this->bizParams,JSON_UNESCAPED_UNICODE);
        }
        return $this->bizContent;
    }


}