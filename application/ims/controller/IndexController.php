<?php
namespace app\ims\controller;


use app\components\ali\alipay\AlipayClient;
use app\components\ali\alipay\AlipayRefund;
use app\components\ali\alipay\AlipayService;
use app\components\ali\alipay\AlipayContentBuilder;
use app\components\Data;
use app\components\wechat\wechatpay\WechatpayContentBuilder;
use app\components\wechat\wechatpay\WechatpayQrcodePay;
use app\components\wechat\wechatpay\WechatpayQuery;
use app\components\ali\alipay\AliPayQrcodePay;
use app\components\ali\alipay\AlipayQuery;
use app\components\wechat\wechatpay\WechatpayRefund;
use app\components\wechat\wechatpay\WechatpayService;
use app\ims\model\EmployeeModel;
use Endroid\QrCode\QrCode;

class IndexController extends PrivilegeController
{
    public function index()
    {

    }

    public function alipayQrcodePay()
    {
        $alipayQrcodeBuilder = new AlipayContentBuilder();
        $alipayQrcodeBuilder->setOutTradeNo(1503545268);
        $alipayQrcodeBuilder->setSubject('hehda');
        $alipayQrcodeBuilder->setTotalAmount('0.01');
        $qrcodePay = new AlipayService();
        $result = $qrcodePay->qrcodePay($alipayQrcodeBuilder);
        if ($result->getTradeStatus() == 'SUCCESS'){
            $response = $result->getResponse();
            $qrCode = new QrCode($response->qr_code);
            header('Content-Type: ' . $qrCode->getContentType());
            echo $qrCode->writeString();
            exit;
        }else{

        }
//        $alipayQrcodePay = new AliPayQrcodePay();
//        return $alipayQrcodePay->pay(1503545268, 'hehda', 0.01);
    }

    public function alipayQuery()
    {
//        $alipayQuery = new AlipayQuery();
        $alipayQueryBuilder = new AlipayContentBuilder();
        $alipayQueryBuilder->setOutTradeNo(1503545268);
        $query = new AlipayService();
        $result = $query->queryResult($alipayQueryBuilder);
        halt($result);
        //        return $alipayQuery->loopQuery(1503545268);
    }

    public function alipayRefund()
    {
//        $alipayQuery = new AlipayRefund();
//        $alipayRefundBuilder->setRefundAmount(0.01);
        $outRequestNo = Data::getUniqueString();
        $alipayRefundBuilder = new AlipayContentBuilder();
        $alipayRefundBuilder->setOutTradeNo(1503545268);
        $alipayRefundBuilder->setOutRequestNo($outRequestNo);
        $refund = new AlipayService();
//        $result = $refund->refund($alipayRefundBuilder);
        $refund->refundQuery($alipayRefundBuilder);
    }

    public function wechatpayQuery()
    {
        $wechatpayContentBuilder = new WechatpayContentBuilder();
        $wechatpayContentBuilder->setOutTradeNo(10247681);
        $wechatpayQuery = new WechatpayService();
        $result = $wechatpayQuery->loopQueryResult($wechatpayContentBuilder);
        halt($result);
    }

    public function wechatpayQrcodePay()
    {
        $wechatpayContentBuilder = new WechatpayContentBuilder();
        $wechatpayContentBuilder->setOutTradeNo(date('YmdHis'));
        $wechatpayContentBuilder->setBody('验孕棒');
        $wechatpayContentBuilder->setTotalFee(99.00);
        $wechatpayContentBuilder->setProductId(000011);
        $qrcodePay = new WechatpayService();
        $result = $qrcodePay->qrcodePay($wechatpayContentBuilder);
        if ($result->getTradeStatus() == 'SUCCESS'){
            $response = $result->getResponse();
            $qrCode = new QrCode($response['code_url']);
            header('Content-Type: ' . $qrCode->getContentType());
            echo $qrCode->writeString();
            exit;
        }
    }

    public function wechatpayRefund()
    {
        $outRefundNo = Data::getUniqueString();
        $wechatpayContentBuilder = new WechatpayContentBuilder();
        $wechatpayContentBuilder->setOutTradeNo(date('YmdHis'));
        $wechatpayContentBuilder->setTotalFee(99.00);
        $wechatpayContentBuilder->setRefundFee(50.00);
        $wechatpayContentBuilder->setOutRefundNo($outRefundNo);
        $refund = new WechatpayService();
        $result = $refund->refund($wechatpayContentBuilder);
        halt($result);
    }
}