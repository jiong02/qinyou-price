<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/9/5
 * Time: 14:10
 */

namespace app\test\controller;


use app\components\Data;
use app\components\wechat\wechatEnterprise\WechatEnterpriseSendMessage;
use app\ims\model\EmployeeModel;
use app\test\model\OrderModel;
use app\test\model\OrderRefundModel;
use think\Request;

class OrderRefundController extends BaseController
{
    public $chargeOfEmployee = '80036';

    public function modifyRefundOrderStatus(Request $request)
    {
        $orderId = $request->param('order_id');
        $orderCustomerId = $request->param('customer_id');
        $orderModel = new OrderModel();
        $orderModel = $orderModel->where('id',$orderId)->where('create_order_people_id',$orderCustomerId)->find();
        if ($orderModel){
            if($orderModel->order_status != 3 && $orderModel->order_status != 4){
                return getError('当前订单并未支付');
            }
            if($orderModel->order_status == 5){
                return getError('当前订单已进入退款流程');
            }
            $orderModel->order_status = 5;
            if($orderModel->save()){
                $sendMessage = new WechatEnterpriseSendMessage();
                $textContent = '联系人' . $orderModel->linkman_name.'已提交退款,请登录后台处理';
                $accountName = $orderModel->take_charge_people_name;
                if ($orderModel->take_charge_people_name == ''){
                    $accountName = $this->chargeOfEmployee;
                }
                $sendMessage->sendTextMessage($accountName,$textContent);
                return getSuccess('发起退款成功');
            }
            return getError('发起退款失败');
        }
        return getError('当前订单不存在');

    }

    public function getOrderRefundByOrderId(Request $request)
    {
        $orderId = $request->param('order_id');
        $orderRefundModel = new OrderRefundModel();
        $result = $orderRefundModel->where('order_id',$orderId)->find();
        if (!$result){
            return getError('当前订单号没有退款信息');
        }
        return getSuccess($result);
    }

    public function addOrderRefund(Request $request)
    {
        $orderId = $request->param('order_id');
        $refundPrice = $request->param('refund_price');
        $refundComment = $request->param('refund_comment');
        $refundModel = new OrderRefundModel();
        do{
            $refundOrderId = Data::getUniqueString();
        }while($refundModel->getRefundOrderId($refundOrderId));
        if (!$orderModel = OrderModel::get($orderId)){
            return getError('当前订单不存在');
        }
        if( $orderModel->order_status != 5){
            return getError('当前订单并未发起退款');
        }
        if ($orderModel->update_total_price < $refundPrice){
            return getError('当前退款价格大于订单总价');
        }
        if ($refundComment > 500){
            return getError('退款描述过长');
        }
        $refundModel->order_id = $orderId;
        $refundModel->refund_price = $refundPrice;
        $refundModel->refund_comment = $refundComment;
        $refundModel->refund_order_id = $refundOrderId;
        $refundModel->refund_status = 1;
        if($refundModel->save()){
            $sendMessage = new WechatEnterpriseSendMessage();
            $accountName = $this->chargeOfEmployee;
            $textContent = $orderModel->take_charge_people_name .'已提提交退款申请,请登录后台处理';
            $sendMessage->sendTextMessage($accountName,$textContent);
            return getSuccess($refundModel->refund_status);
        }
        return getError('退款提交失败');
    }

    public function modifyRefundStatus(Request $request)
    {
        $refundOrderId = $request->param('refund_order_id');
        $refundOrderStatus = $request->param('refund_order_status', 2);
        $params['refund_order_id'] = [$refundOrderId , 'require'];
        $params['refund_order_status'] = [$refundOrderStatus , 'number|between:1,4'];
        $this->checkAllParam($params);
        $orderRefundModel = new OrderRefundModel();
        $result = $orderRefundModel->where('refund_order_id',$refundOrderId)->setField('refund_status',$refundOrderStatus);
        if ($result == 0){
            return getError('没有任何数据被修改!');
        }
        if (!$result){
            return getError('数据修改失败!');
        }
        return getSuccess('数据修改成功');

    }

    public function refund(Request $request)
    {
        $refundOrderId = $request->param('refund_order_id');
        $params['refund_order_id'] = [$refundOrderId , 'require'];
        $this->checkAllParam($params);
        $orderRefundModel = new OrderRefundModel();
        $orderRefundModel = $orderRefundModel->where('refund_order_id',$refundOrderId)->find();
        if ($orderRefundModel){
            $orderRefundModel->refund_status = 3;
            $orderRefundModel->save();
        }else{
            return getError('当前退款订单不存在');
        }

        $outRefundNo = $orderRefundModel->refund_order_id;
        $refundFee = $orderRefundModel->refund_price;
        $orderModel = $orderRefundModel->orderModel;

        $totalFee = $orderModel->update_total_price;
        $payStatus = $orderModel->pay_status;
        $outTradeNo = $orderModel->order_pay_id;
        $pay = new PayController();
        if ($payStatus == 1){
            $result = $pay->alipayRefund($outTradeNo, $outRefundNo, $refundFee);
        }elseif($payStatus == 2){
            $result = $pay->wechatpayRefund($outTradeNo, $outRefundNo, $totalFee, $refundFee);
        }else{
            $result = getError('支付类型错误');
        }
        if ($result['status'] === 1){
            $orderRefundModel->refund_status = 4;
            $orderRefundModel->save();
        }
        return $result;
    }
}