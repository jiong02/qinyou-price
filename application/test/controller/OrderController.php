<?php
namespace app\test\controller;
use think\Request;
use app\test\model\OrderModel;
use think\Validate;
use app\test\model\OrderCustomerModel;

class OrderController extends BaseController
{
    /**
     * @name 修改订单（联系人）
     * @auth Sam
     * @param Request $request
     * @return array|string
     */
    public function updateOrder(Request $request)
    {
        $orderModel = new OrderModel();

        $orderInfo = $request->param('order_info/a',array());

        if(empty($orderInfo) || !is_array($orderInfo)){
            return '请输入订单数据';
        }

//        halt($orderModel->scene['create_order']);

        $validateClass = new Validate($orderModel->createOrder);
        $validateRes = $validateClass->check($orderInfo);

        if(empty($validateRes)){
            return $validateClass->getError();
        }

        $orderInfo['order_status'] = 1;
        $orderInfo['create_date'] = date('Y-m-d',time());

        $result = $orderModel->save($orderInfo);

        if(!empty($result)){
            return (int)$orderModel->id;
        }else{
            return '修改失败';
        }
    }

    /**
     * @name 修改联系人信息
     * @auth Sam
     * @param Request $request
     * @return array|string
     */
    public function updateLinkmanInfo(Request $request)
    {
        $orderModel = new OrderModel();

        $linkmanInfo = $request->param('linkman_info/a',array());

        if(empty($linkmanInfo) || !is_array($linkmanInfo)){
            return '请输入联系人数据';
        }

        $validateClass = new Validate($orderModel->rules);
        $validateRes = $validateClass->scene($orderModel->scene['linkman'])->check($linkmanInfo);

        if(empty($validateRes)){
            return $validateClass->getError();
        }

        $result = $orderModel->save($linkmanInfo);

        if(!empty($result)){
            return '修改成功';
        }else{
            return '修改失败';
        }

    }


    /**
     * @name 获取订单（联系人）
     * @auth Sam
     * @param Request $request
     * @return array|string
     */
    public function getOrderLinkmanInfo(Request $request)
    {
        $orderId = $request->param('order_id',0);

        if(empty($orderId) || !is_numeric($orderId)){
            return '请输入订单ID';
        }

        $orderModel = new OrderModel();

        $orderInfo = $orderModel->field("cheeru_order.id,cheeru_order.order_name,cheeru_order.route_id,cheeru_order.trip_date,cheeru_order.room_number,cheeru_order.adult_number,cheeru_order.child_number,cheeru_order.total_price,cheeru_order.linkman_name,cheeru_order.linkman_phone,cheeru_order.linkman_wechat,ims_route.ims_route.image_uniqid,ims_new.ims_image.image_category,ims_new.ims_image.image_path,ims_route.route_type")->where("cheeru_order.id",$orderId)->join('ims_route.ims_route','cheeru_order.route_id = ims_route.ims_route.id','LEFT')->join('ims_new.ims_image','ims_route.ims_route.image_uniqid = ims_new.ims_image.image_uniqid','LEFT')->find();

        if(!empty($orderInfo)){
            return $orderInfo->toArray();
        }

        return '没有订单信息';
    }

    /**
     * @name 获取客户资料
     * @auth Sam
     * @param Request $request
     * @return array|string
     */
    public function getTripPersonList(Request $request)
    {
        $orderId = $request->param('order_id',0);

        if(empty($orderId) || !is_numeric($orderId)){
            return '订单不存在';
        }

        $orderModel = new OrderModel();

        $orderInfo = $orderModel->where('id',$orderId)->find();

        if(empty($orderInfo)){
            return '订单不存在';
        }

        if($orderInfo['order_status'] > 3){
            return '流程不正确';
        }

        $customerModel = new OrderCustomerModel();

        $customerList = $customerModel->where('order_id',$orderId)->select();

        if(!empty($customerList)){
            return $customerList->toArray();
        }

        return '没有客户信息';
    }

    /**
     * @name 修改客户信息
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function updateCustomerInfo(Request $request)
    {
        $customerInfo = $request->param('customer_info/a',array());

        if(empty($customerInfo) || !is_array($customerInfo)){
            return '请输入客户信息';
        }

        $customerModel = new OrderCustomerModel();

        $validateClass = new Validate($customerModel->linkman);

        $validateResult = $validateClass->check($customerInfo);

        if(empty($validateResult)){
            return $validateClass->getError();
        }

        //判断是否填写完整客户资料
        $orderModel = new OrderModel();

        $orderInfo = $orderModel->where('id',$customerInfo['order_id'])->find();

        if(empty($orderInfo)){
            return '订单不存在';
        }

        $tripNumber = $orderInfo->adult_number + $orderInfo->child_number;

        if($tripNumber <= 0){
            return '客户数量错误';
        }

        $custNumber = $customerModel->where('order_id',$customerInfo['order_id'])->count();

        if($tripNumber == $custNumber){
            $orderInfo->order_status = 4;
            $orderInfo->save();
        }

        //保存客户信息
        $customerResult = $customerModel->save($customerInfo);

        if(!empty($customerResult)){
            return '修改成功';
        }

        return '修改失败';
    }




}

?>