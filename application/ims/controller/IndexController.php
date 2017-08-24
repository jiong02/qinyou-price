<?php
namespace app\ims\controller;


use app\components\ali\alipay\AliPayQrcodePay;
use app\components\wechat\wechatpay\Wechatpay;
use app\components\wechat\wechatpay\WechatpayQrcodePay;
use app\components\wechat\wechatpay\WechatpayQuery;
use app\components\ali\alipay\AlipayQuery;
use app\ims\model\EmployeeModel;
use Endroid\QrCode\QrCode;

class IndexController extends PrivilegeController
{
    public function index()
    {
//        return AliPayQrcodePay::pay(123123,'hehda',20000);
//        $alipayQuery = new AlipayQuery();
//        return $alipayQuery->query(123123);
        $wechatpayQuery = new WechatpayQuery();
        return $wechatpayQuery->query(10247681);
//        return WechatpayQuery::query(10247681);
//        return WechatpayQrcodePay::pay('10247681','验孕棒',99.00,'000011');
    }
}