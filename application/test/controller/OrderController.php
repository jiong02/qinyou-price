<?php
namespace app\test\controller;
use think\Request;
use app\test\model\OrderModel;
use think\Validate;

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

        $validateClass = new Validate();
        $validateRes = $validateClass->check($orderInfo);

        if(empty($validateRes)){
            return $validateClass->getError();
        }

        $orderInfo['order_status'] = 1;

        $result = $orderModel->save($orderInfo);

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

        $orderInfo = $orderModel->field("cheeru_order.id,cheeru_order.order_name,cheeru_order.route_id,cheeru_order.trip_date,cheeru_order.room_number,cheeru_order.adult_number,cheeru_order.child_number,cheeru_order.total_price,cheeru_order.linkman_name,cheeru_order.linkman_phone,cheeru_order.linkman_wechat,ims_route.ims_route.image_uniqid,ims_new.ims_image.image_category,ims_new.ims_image.image_path,ims_route.route_type ")->where("cheeru_order.id",$orderId)->join('ims_route','cheeru_order.route_id = ims_route.ims_route.id','LEFT')->join('ims_image','ims_route.ims_route.image_uniqid = ims_new.ims_image.image_uniqid','LEFT')->buildSql();
echo $orderInfo;exit;
//        $orderInfo = $orderModel->select();

        if(!empty($orderInfo)){
            return $orderInfo->toArray();
        }

        return '没有订单信息';
    }







}

?>