<?php
namespace app\ims\controller;


use app\components\ali\alipay\AlipayRefund;
use app\components\wechat\wechatpay\WechatpayQrcodePay;
use app\components\wechat\wechatpay\WechatpayQuery;
use app\components\ali\alipay\AliPayQrcodePay;
use app\components\ali\alipay\AlipayQuery;
use app\ims\model\EmployeeModel;
use Endroid\QrCode\QrCode;

class IndexController extends PrivilegeController
{
    public function index()
    {

    }

    public function alipayQrcodePay()
    {
        $alipayQrcodePay = new AliPayQrcodePay();
        return $alipayQrcodePay->pay(1503545268, 'hehda', 0.01);
    }

    public function alipayQuery()
    {
        $alipayQuery = new AlipayQuery();
        return $alipayQuery->loopQuery(1503545268);
    }

    public function alipayRefund()
    {
        $alipayQuery = new AlipayRefund();
        return $alipayQuery->refund(1503545268,0.01);
    }

    public function wechatpayQuery()
    {
        $wechatpayQuery = new WechatpayQuery();
        return $wechatpayQuery->loopQuery(10247681);
    }

    public function wechatpayQrcodePay()
    {
        $wechatpayQuery = new WechatpayQrcodePay();
        return $wechatpayQuery->pay('10247681','验孕棒',99.00,'000011');

    }
}