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
use app\test\model\OrderModel;
use app\test\model\TestAccount;
use Endroid\QrCode\QrCode;
use think\Cache;
use think\Request;

class PayController extends BaseController
{
    public function qrcodePay(Request $request)
    {
        $outTradeNo = $request->param('out_trade_no');
        $subject = $request->param('subject');
        $totalAmount = $request->param('total_amount');
        $payType = $request->param('pay_type');
        $params = [
            'out_trade_no'=>[$outTradeNo , 'require'],
            'subject'=>[$subject , 'require'],
            'total_amount'=>[$totalAmount , 'require'],
            'pay_type'=>[$payType , 'require'],
        ];
        $this->checkAllParam($params);
        if ($payType == '支付宝'){
            $result = $this->alipayQrcodePay($outTradeNo, $subject, $totalAmount);
        }elseif($payType == '微信'){
            $result = $this->wechatpayQrcodePay($outTradeNo, $subject, $totalAmount);
        }else{
            $result = getError('支付类型错误');
        }
        return $result;
    }

    public function query(Request $request)
    {
        $customerToken = $request->param('customer_token','5a0435');
        $customerId = $request->param('customer_id',4);
        $outTradeNo = $request->param('out_trade_no',18);
        $payType = $request->param('pay_type','支付宝');
        $params = [
            'out_trade_no'=>[$outTradeNo , 'require'],
            'pay_type'=>[$payType , 'require'],
        ];
        $testAccount = new TestAccount();
        $testAccount->checkAccountId($customerId,$customerToken);
        $this->checkAllParam($params);
        if ($payType == '支付宝'){
            $result = $this->alipayQuery($outTradeNo);
        }elseif($payType == '微信'){
            $result = $this->wechatpayQuery($outTradeNo);
        }else{
            $result = getError('支付类型错误');
        }
        if ($result['status'] === 1){
            $orderModel =  new OrderModel();
            $orderModel->updateOrderStatus($outTradeNo,$customerId,4);
        }
        return $result;
    }

    public function alipayQrcodePay($outTradeNo, $subject, $totalAmount)
    {
        $cachePrefix = 'alipay_code_url_';
        if ($codeUrl = Cache::get($cachePrefix. $outTradeNo)){
            return getSuccess($codeUrl);
        }
        $alipayContentBuilder = new AlipayContentBuilder();
        $alipayContentBuilder->setOutTradeNo($outTradeNo);
        $alipayContentBuilder->setSubject($subject);
        $alipayContentBuilder->setTotalAmount($totalAmount);
        $qrcodePay = new AlipayService();
        $result = $qrcodePay->qrcodePay($alipayContentBuilder);
        $response = $result->getResponse();
        if ($result->getTradeStatus() == 'SUCCESS'){
            Cache::set($cachePrefix . $outTradeNo, $response->qr_code);
            return getSuccess($response->qr_code);
        }else{
            return getError($response->msg);
        }
    }

    public function wechatpayQrcodePay($outTradeNo, $subject, $totalAmount, $productId = '')
    {
        if (!$productId){
            $productId = Data::getUniqueString();
        }
        $cachePrefix = 'wechatpay_code_url_';
        if ($codeUrl = Cache::get($cachePrefix . $outTradeNo)){
            return getSuccess($codeUrl);
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
            Cache::set($cachePrefix . $outTradeNo, $response['code_url']);
            return getSuccess($response['code_url']);
        }else{
            return getError($response['err_code']);
        }
    }

    public function alipayQuery($outTradeNo)
    {
        $alipayQueryBuilder = new AlipayContentBuilder();
        $alipayQueryBuilder->setOutTradeNo($outTradeNo);
        $query = new AlipayService();
        $result = $query->loopQueryResult($alipayQueryBuilder);
        if ($result->getTradeStatus() == 'SUCCESS'){
            return getSuccess('订单支付成功');
        }else{
            $response = $result->getResponse();
            return getError($response->msg);
        }
    }

    public function alipayRefund()
    {
        $outRequestNo = Data::getUniqueString();
        $alipayRefundBuilder = new AlipayContentBuilder();
        $alipayRefundBuilder->setOutTradeNo(1503545268);
        $alipayRefundBuilder->setOutRequestNo($outRequestNo);
        $refund = new AlipayService();
        $result = $refund->refundQuery($alipayRefundBuilder);
        $refund->refundQuery($alipayRefundBuilder);
    }

    public function wechatpayQuery($outTradeNo)
    {
        $wechatpayContentBuilder = new WechatpayContentBuilder();
        $wechatpayContentBuilder->setOutTradeNo($outTradeNo);
        $wechatpayQuery = new WechatpayService();
        $result = $wechatpayQuery->loopQueryResult($wechatpayContentBuilder);
        if ($result->getTradeStatus() == 'SUCCESS'){
            return getSuccess('订单支付成功');
        }else{
            $response = $result->getResponse();
            return getError($response['trade_state_desc']);
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

    public function updateOrderStatus()
    {

    }
//    public function wechatpayQrcodePay()
//    {
//        $wechatpayContentBuilder = new WechatpayContentBuilder();
//        $wechatpayContentBuilder->setOutTradeNo(date('YmdHis'));
//        $wechatpayContentBuilder->setBody('验孕棒');
//        $wechatpayContentBuilder->setTotalFee(99.00);
//        $wechatpayContentBuilder->setProductId(000011);
//        $qrcodePay = new WechatpayService();
//        $result = $qrcodePay->qrcodePay($wechatpayContentBuilder);
//        if ($result->getTradeStatus() == 'SUCCESS'){
//            $response = $result->getResponse();
//            $qrCode = new QrCode($response['code_url']);
//            header('Content-Type: ' . $qrCode->getContentType());
//            echo $qrCode->writeString();
//            exit;
//        }
//    }
}