<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/29
 * Time: 10:14
 */

namespace app\test\controller;

use app\components\Data;
use app\components\ali\alipay\AlipayClient;
use app\components\ali\alipay\AlipayRefund;
use app\components\ali\alipay\AlipayService;
use app\components\ali\alipay\AlipayContentBuilder;
use app\components\wechat\wechatpay\WechatpayContentBuilder;
use app\components\wechat\wechatpay\WechatpayQrcodePay;
use app\components\wechat\wechatpay\WechatpayQuery;
use app\components\ali\alipay\AliPayQrcodePay;
use app\components\ali\alipay\AlipayQuery;
use app\components\wechat\wechatpay\WechatpayRefund;
use app\components\wechat\wechatpay\WechatpayService;
use Endroid\QrCode\QrCode;
use think\Request;

class PayController extends BaseController
{
    public function qrcodePay(Request $request)
    {
        $outTradeNo = $request->param('out_trade_no');
        $subject = $request->param('subject');
        $totalAmount = $request->param('total_amount');
        $params = [
            'out_trade_no'=>[$outTradeNo , 'require'],
            'subject'=>[$subject , 'require'],
            'total_amount'=>[$totalAmount , 'require'],
        ];
        $this->checkAllParam($params);
        $payType = $request->param('pay_type');
        if ($payType == '支付宝'){
            $result = $this->alipayQrcodePay($outTradeNo, $subject, $totalAmount);
        }elseif($payType == '微信'){
            $result = $this->wechatpayQrcodePay($outTradeNo, $subject, $totalAmount);
        }else{
            $result = getError('支付类型错误')
        }
        return $result;
    }

    public function alipayQrcodePay($outTradeNo, $subject, $totalAmount)
    {
        $alipayContentBuilder = new AlipayContentBuilder();
        $alipayContentBuilder->setOutTradeNo($outTradeNo);
        $alipayContentBuilder->setSubject($subject);
        $alipayContentBuilder->setTotalAmount($totalAmount);
        $qrcodePay = new AlipayService();
        $result = $qrcodePay->qrcodePay($alipayContentBuilder);
        $response = $result->getResponse();
        if ($result->getTradeStatus() == 'SUCCESS'){
            Data::createQrCode($response->qr_code);
        }else{
            return getError($response->msg);
        }
    }

    public function wechatpayQrcodePay($outTradeNo, $subject, $totalAmount, $productId = '')
    {
        if (!$productId){
            $productId = Data::getUniqueString();
        }
        $wechatpayContentBuilder = new WechatpayContentBuilder();
        $wechatpayContentBuilder->setOutTradeNo($outTradeNo);
        $wechatpayContentBuilder->setBody($subject);
        $wechatpayContentBuilder->setTotalFee($totalAmount);
        $wechatpayContentBuilder->setProductId($productId);
        $qrcodePay = new WechatpayService();
        $result = $qrcodePay->qrcodePay($wechatpayContentBuilder);
        $response = $result->getResponse();
        if ($result->getTradeStatus() == 'SUCCESS'){
            Data::createQrCode($response->qr_code);
        }else{
            return getError($response['return_msg']);
        }
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
        $result = $refund->refundQuery($alipayRefundBuilder);
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
//        $wechatpayContentBuilder->setTotalFee(99.00);
//        $wechatpayContentBuilder->setRefundFee(50.00);
//        $wechatpayContentBuilder->setOutRefundNo($outRefundNo);
        $refund = new WechatpayService();
        $result = $refund->refundQuery($wechatpayContentBuilder);
        halt($result);
    }
}