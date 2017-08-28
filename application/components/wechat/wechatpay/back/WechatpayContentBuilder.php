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
    //商品或支付单简要描述。
    private $body;
    //附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据。
    private $attach;
    //商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号。
    private $outTradeNo;
    //订单总金额，只能为整数，详见支付金额。
    private $totalFee;
    //取值如下：JSAPI，NATIVE，APP，详细说明见参数规定。
    private $tradeType;
    //trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
    private $productId;
    //trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识。下单前需要调用【网页授权获取用户信息】接口获取到用户的Openid 。
    private $openid;
    //退款金额
    private $refundFree;
    //退款单号
    private $outRefundNo;
    protected $bizContent;

    const NATIVE = 'NATIVE';
    const JSAPI = 'JSAPI';

    public function setBody($body)
    {
        $this->body = $body;
        $this->bizContent['body'] = $body;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setAttach($attach)
    {
        $this->attach = $attach;
        $this->bizContent['attach'] = $attach;
    }

    public function getAttach()
    {
        return $this->attach;
    }


    public function setOutTradeNo($outTradeNo)
    {
        $this->outTradeNo = $outTradeNo;
        $this->bizContent['out_trade_no'] = $outTradeNo;
    }

    public function getOutTradeNo()
    {
        return $this->bizContent['out_trade_no'];
    }


    public function setTotalFee($totalFee)
    {
        $this->totalFee = $totalFee;
        $this->bizContent['total_fee'] = $totalFee;
    }

    public function getTotalFee()
    {
        return $this->totalFee;
    }

    public function setTradeType($tradeType)
    {
        $this->tradeType = $tradeType;
        $this->bizContent['trade_type'] = $tradeType;
    }

    public function getTradeType()
    {
        return $this->tradeType;
    }


    public function setProductId($productId)
    {
        $this->productId = $productId;
        $this->bizContent['product_id'] = $productId;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function setOpenid($openid)
    {
        $this->openid = $openid;
        $this->bizContent['openid'] = $openid;
    }

    public function getOpenid()
    {
        return $this->openid;
    }
    /**
     * @return mixed
     */
    public function getRefundFree()
    {
        return $this->refundFree;
    }

    /**
     * @param mixed $refundFree
     */
    public function setRefundFree($refundFree)
    {
        $this->refundFree = $refundFree;
    }

    /**
     * @return mixed
     */
    public function getOutRefundNo()
    {
        return $this->outRefundNo;
    }

    /**
     * @param mixed $outRefundNo
     */
    public function setOutRefundNo($outRefundNo)
    {
        $this->outRefundNo = $outRefundNo;
    }

    public function getBizContent()
    {
        return $this->bizContent;
    }
//    public function checkPayContent()
//    {
//        if (!$this->getOutTradeNo()){
//            throw new Exception('缺少订单号');
//        }
//        if (!$this->getTotalFee()){
//            throw new Exception('缺少订单金额');
//        }
//        if (!$this->getBody()){
//            throw new Exception('缺少订单名称');
//        }
//        switch ($this->getTradeType()) {
//            case self::NATIVE:
//                if (!$this->getProductId()){
//                    throw new Exception('缺少产品id');
//                }
//                break;
//            case self::JSAPI:
//                if (!$this->getOpenid()){
//                    throw new Exception('缺少openid');
//                }
//                break;
//        }
//    }
}