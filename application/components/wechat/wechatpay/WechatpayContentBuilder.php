<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/23
 * Time: 10:22
 */

namespace app\components\wechat\wechatpay;

use think\Exception;
class WechatpayContentBuilder
{
    private $deviceInfo;
    private $body;
    private $detail;
    private $attach;
    private $outTradeNo;
    private $feeType;
    private $totalFee;
    private $timeStart;
    private $timeExpire;
    private $goodsTag;
    private $tradeType;
    private $productId;
    private $limitPay;
    private $openid;
    private $sceneInfo;
    private $tradType;
    //APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP
    private $spbillCreateIp;
    //异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数。
    private $notifyUrl;
    //退款金额
    private $refundFee;
    //退款单号
    private $outRefundNo;
    protected $bizContent;

    /**
     * @param mixed $deviceInfo
     */
    public function setDeviceInfo($deviceInfo)
    {
        $this->deviceInfo = $deviceInfo;
        $this->bizContent['device_info'] = $deviceInfo;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
        $this->bizContent['body'] = $body;
    }

    /**
     * @param mixed $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
        $this->bizContent['detail'] = $detail;
    }

    /**
     * @param mixed $attach
     */
    public function setAttach($attach)
    {
        $this->attach = $attach;
        $this->bizContent['attach'] = $attach;
    }

    /**
     * @param mixed $outTradeNo
     */
    public function setOutTradeNo($outTradeNo)
    {
        $this->outTradeNo = $outTradeNo;
        $this->bizContent['out_trade_no'] = $outTradeNo;
    }

    /**
     * @param mixed $feeType
     */
    public function setFeeType($feeType)
    {
        $this->feeType = $feeType;
        $this->bizContent['fee_type'] = $feeType;
    }

    /**
     * @param mixed $totalFee
     */
    public function setTotalFee($totalFee)
    {
        $this->totalFee = $totalFee;
        $this->bizContent['total_fee'] = $totalFee;
    }

    /**
     * @param mixed $spbillCreateIp
     */
    public function setSpbillCreateIp($spbillCreateIp)
    {
        $this->spbillCreateIp = $spbillCreateIp;
        $this->bizContent['spbill_create_ip'] = $spbillCreateIp;
    }

    /**
     * @param mixed $timeStart
     */
    public function setTimeStart($timeStart)
    {
        $this->timeStart = $timeStart;
        $this->bizContent['time_start'] = $timeStart;
    }

    /**
     * @param mixed $timeExpire
     */
    public function setTimeExpire($timeExpire)
    {
        $this->timeExpire = $timeExpire;
        $this->bizContent['time_expire'] = $timeExpire;
    }

    /**
     * @param mixed $goodsTag
     */
    public function setGoodsTag($goodsTag)
    {
        $this->goodsTag = $goodsTag;
        $this->bizContent['goods_tag'] = $goodsTag;
    }

    /**
     * @param mixed $notifyUrl
     */
    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
        $this->bizContent['notify_url'] = $notifyUrl;
    }

    /**
     * @param mixed $tradeType
     */
    public function setTradeType($tradeType)
    {
        $this->tradeType = $tradeType;
        $this->bizContent['trade_type'] = $tradeType;
    }

    /**
     * @param mixed $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        $this->bizContent['product_id'] = $productId;
    }

    /**
     * @param mixed $limitPay
     */
    public function setLimitPay($limitPay)
    {
        $this->limitPay = $limitPay;
        $this->bizContent['limit_pay'] = $limitPay;
    }

    /**
     * @param mixed $openid
     */
    public function setOpenid($openid)
    {
        $this->openid = $openid;
        $this->bizContent['openid'] = $openid;
    }

    /**
     * @param mixed $sceneInfo
     */
    public function setSceneInfo($sceneInfo)
    {
        $this->sceneInfo = $sceneInfo;
        $this->bizContent['scene_info'] = $sceneInfo;
    }

    /**
     * @param mixed $tradType
     */
    public function setTradType($tradType)
    {
        $this->tradType = $tradType;
    }

    /**
     * @param mixed $refundFree
     */
    public function setRefundFee($refundFree)
    {
        $this->refundFee = $refundFree;
        $this->bizContent['refund_fee'] = $refundFree;
    }

    /**
     * @param mixed $outRefundNo
     */
    public function setOutRefundNo($outRefundNo)
    {
        $this->outRefundNo = $outRefundNo;
        $this->bizContent['out_refund_no'] = $outRefundNo;
    }

    /**
     * @return mixed
     */
    public function getBizContent()
    {
        return $this->bizContent;
    }
}