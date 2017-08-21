<?php
namespace app\test\controller;
use think\Request;
use app\test\model\OrderModel;
use think\Validate;
use app\test\model\OrderCustomerModel;
use think\config;
use app\components\Excel;
use app\test\model\TestAccount;

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
        $orderInfo['total_price'] = $orderInfo['update_total_price'];

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

        $validateClass = new Validate($orderModel->linkman);
        $validateRes = $validateClass->check($linkmanInfo);

        if(empty($validateRes)){
            return $validateClass->getError();
        }

        $linkmanInfo['order_status'] = 2;

        if(!empty($linkmanInfo['id'])){
            $result = $orderModel->update($linkmanInfo);
        }else{
            $result = $orderModel->save($linkmanInfo);
        }

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

        $orderInfo = $orderModel->field("cheeru_order.id,cheeru_order.order_name,cheeru_order.route_id,cheeru_order.trip_date,cheeru_order.room_number,cheeru_order.adult_number,cheeru_order.child_number,cheeru_order.update_total_price,cheeru_order.linkman_name,cheeru_order.linkman_phone,cheeru_order.linkman_wechat,ims_route.ims_route.image_uniqid,ims_new.ims_image.image_category,ims_new.ims_image.image_path,ims_route.route_type,order_status")->where("cheeru_order.id",$orderId)->join('ims_route.ims_route','cheeru_order.route_id = ims_route.ims_route.id','LEFT')->join('ims_new.ims_image','ims_route.ims_route.image_uniqid = ims_new.ims_image.image_uniqid','LEFT')->find();

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

        $validateClass = new Validate($customerModel->rules);

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

            $dateNow = date('Y-m-d',time());

            if($orderInfo->create_date == $dateNow){
                $orderInfo->is_same_date = 1;
            }

            $orderInfo->save();
        }

        //保存客户信息
        if(!empty($customerInfo['id'])){
            $customerResult = $customerModel->update($customerInfo);
        }else{
            $customerResult = $customerModel->save($customerInfo);
        }


        if(!empty($customerResult)){
            return '修改成功';
        }

        return '修改失败';
    }

    /**
     * @name 前端修改订单状态
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function updateOrderStatus(Request $request)
    {
        $orderId = $request->param('order_id',0);
        $status = $request->param('status',0);
        $type = $request->param('type',1);

        if(empty($orderId) || empty($status)){
            return '数据不完整';
        }

        $orderModel = new OrderModel();

        $orderInfo = $orderModel->where('id',$orderId)->find();

        if(empty($orderInfo)){
            return '订单不存在';
        }

        $orderInfo->order_status = $status;
        $result = '';

        if($type == 1){
            $result = $orderInfo->save();
        }else if($type == 2){
            $result = $orderInfo->update();
        }

        if($result){
            return '修改成功';
        }

        return '修改失败';
    }

    /**
     * @name 获取后台订单列表
     * @auth Sam
     * @return array|string
     */
    public function getBackOrderList(Request $request)
    {
        $page = $request->param('page',0);
        $limit = $request->param('limit',10);

        $orderModel = new OrderModel();

        $orderList = $orderModel->field('cheeru_order.id,cheeru_order.order_name,cheeru_order.create_time,cheeru_order.adult_number,cheeru_order.child_number,cheeru_order.update_total_price,cheeru_order.take_charge_people_id,cheeru_order.order_status,cheeru_order.route_id,ims_route.ims_route.route_code')->join('ims_route.ims_route','route_id = ims_route.ims_route.id ','LEFT')->limit($page,$limit)->order('id desc')->select();

        $orderCount = $orderModel->field('cheeru_order.id,cheeru_order.order_name,cheeru_order.create_time,cheeru_order.adult_number,cheeru_order.child_number,cheeru_order.update_total_price,cheeru_order.take_charge_people_id,cheeru_order.order_status,cheeru_order.route_id,ims_route.ims_route.route_code')->join('ims_route.ims_route','route_id = ims_route.ims_route.id ','LEFT')->count();

        $orderCount = ceil($orderCount / 10);

        if(!empty($orderList)){
            $return['total_page'] = $orderCount;
            $return['order_list'] = $orderList->toArray();

            return $return;
        }

        return '没有订单列表';

    }

    /**
     * @name 获取订单信息
     * @auth Sam
     * @param Request $request
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getBackOrderInfo(Request $request)
    {
        $orderId = $request->param('order_id',0);

        if(empty($orderId) || !is_numeric($orderId)){
            return '请输入订单ID';
        }

        $orderModel = new OrderModel();

        $orderInfo = $orderModel->field('cheeru_order.*,ims_route.ims_route.route_code')->join('ims_route.ims_route','route_id = ims_route.ims_route.id ','LEFT')->where('cheeru_order.id',$orderId)->find();

        if(empty($orderInfo)){
            return '订单不存在';
        }

        $orderInfo = $orderInfo->toArray();

        $customerModel = new OrderCustomerModel();

        $customerList = $customerModel->field("cheeru_order_customer.*,'false' as ispull")->where('order_id',$orderId)->select();

        if(!empty($customerList)){
            $customerList = $customerList->toArray();
        }else{
            $customerList = '';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->field('id,user_name,call_phone')->where('id',$orderInfo['create_order_people_id'])->find();

        if(!empty($accountInfo['user_name'])){
            $orderInfo['user_name'] = $accountInfo['user_name'];
        }else{
            $orderInfo['user_name'] = '';
        }

        if(!empty($accountInfo['call_phone'])){
            $orderInfo['call_phone'] = $accountInfo['call_phone'];
        }else{
            $orderInfo['call_phone'] = '';
        }

        $orderInfo['customer_list'] = $customerList;

        return $orderInfo;

    }

    /**
     * @name 修改后台订单
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function updateOrderPrice(Request $request)
    {
        $orderId = $request->param('order_id',0);

        $price = $request->param('price',0);

        if(empty($orderId) || !is_numeric($orderId)){
            return '订单不存在';
        }

        if(empty($price) || !is_numeric($price)){
            return '价格不正确';
        }

        $orderModel = new OrderModel();

        $orderInfo = $orderModel->where('id',$orderId)->find();

        if(empty($orderInfo)){
            return '订单不存在';
        }

        $orderInfo->update_total_price = $price;

        if($orderInfo->save()){
            return '修改订单成功';
        }

        return '修改订单失败';
    }

    /**
     * @name Excel输出订单信息
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function outputOrderInfo(Request $request)
    {
        $orderId = $request->param('order_id',0);

        if(empty($orderId)){
            return '订单ID不存在';
        }

        $orderModel = new OrderModel();

        $orderField = "
            order_name as '订单名称',
            trip_date as '出行日期',
            room_number as '房间数量',
            adult_number as '成人数量',
            child_number as '儿童数量',
            update_total_price as '总价格',
            linkman_name as '联系人',
            linkman_phone as '联系人电话',
            linkman_wechat as '联系人微信'
        ";

        $orderInfo = $orderModel->field($orderField)->where('cheeru_order.id',$orderId)->find();

        if(empty($orderInfo)){
            return '订单不存在';
        }

        $orderInfo = $orderInfo->toArray();

        $excKey = [];
        $excValue = [];

        foreach($orderInfo as $k=>$v){
            $excKey[] = $k;
            $excValue[] = $v;
        }

        $excClass = new Excel();

        $excClass->init2('行程确认单','订单信息');

        $excClass->setHeader($excKey);
        $excClass->setContent($excValue);

        $excClass->export();

    }




}

?>