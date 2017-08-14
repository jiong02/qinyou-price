<?php
namespace app\test\controller;
use think\Request;
use app\test\model\OrderModel;
use think\Validate;

class OrderController extends BaseController
{
    public function createOrder(Request $request)
    {
        $orderModel = new OrderModel();

        $orderInfo = $request->param('order_info/a',array());

        if(empty($orderInfo) || !is_array($orderInfo)){
            return '请输入订单数据';
        }




    }








}

?>