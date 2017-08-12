<?php

namespace app\ims\controller;

use app\ims\model\EmployeeModel;
use app\ims\model\HotelModel;
use app\ims\model\ItineraryDateModel;
use app\ims\model\OrderModel;
use app\ims\model\PassengerModel;
use app\index\model\Hotel;
use app\index\model\Order;
use think\Request;

class OrderController extends PrivilegeController
{
    public $orderId;
    public function __construct(Request $request = null)
    {
        $this->orderId = $this->post('id');
    }

    public function index($return = [])
    {
        $employeeId = $this->post('id');
        $orderModel = new OrderModel();
        $result = $orderModel->where('employee_sn',$employeeId)->select();
        $name = EmployeeModel::get($employeeId)->employee_name;
        foreach ($result as $index => $item) {
            $return[$index]['id'] = $item->id;
            $return[$index]['amount'] = $item->passenger_amount;
            $return[$index]['date'] = $item->departure_date.'/'.$item->back_departure_date;
            $return[$index]['dest'] = $item->country.'/'.$item->dest;
            $return[$index]['number'] = $item->order_sn;
            $return[$index]['name'] = $name;
        }
        return getSucc($return);
    }

    public function addOrderData()
    {
        $request = $this->post();
        $orderModel = new OrderModel();
        $orderModel = $orderModel->formatInput($request);
        if($orderModel->save()){
            $this->batchAddItineraryDateData($orderModel);
            $passengerController = new PassengerController();
            $passengerController->batchAddEmptyPassengerData($orderModel);
            return getSucc($orderModel->id);
        }
        return getErr('新增失败!');
    }

    public function getOrderData()
    {
        $orderId = $this->post('id');
        if($orderId && $orderModel = OrderModel::get($orderId)){
            $return['contact'] = $orderModel->passenger_representative_name;
            $return['phone'] = $orderModel->passenger_representative_phone;
            $return['country'] = $orderModel->country;
            $return['hotel_id'] = $orderModel->hotel_id;
            $return['date'] = $orderModel->date;
            $return['dest'] = $orderModel->dest;
            $return['message'] = (new PassengerController())->getMessageByOrderId($orderId);
            $return['days'] = $orderModel->itinerary_day_amount;
            $return['currency'] = HotelModel::get($orderModel->hotel_id)->exchange->currency_unit;
            $return['persons'] = $orderModel->passenger_amount;
            $return['route'] = $orderModel->route;
            return getSucc($return);
        };
        return getErr('数据获取失败');
    }

    /**
     * 修改联系人信息
     */
    public function modifyRepData()
    {
        $orderId = $this->post('id');
        $data['passenger_representative_name'] = $this->post('name');
        $data['passenger_representative_phone'] = $this->post('phone');
        $orderModel = OrderModel::get($orderId);
        if($orderModel->save($data)) {
            $this->batchAddItineraryDateData($orderModel);
            return getSucc('数据更新成功!');
        }
        return getErr('数据更新失败');
    }

    /**
     * 修改订单日期信息
     */
    public function ModifyOrderDate()
    {
        $param = $this->post();
        $orderId = $this->orderId;
        $orderModel = OrderModel::get($orderId);
        $orderModel->formatInput($param);
        if($result = $orderModel->save()) {
            $this->batchAddItineraryDateData($orderModel);
            return getSucc('数据更新成功!');
        }
        if($result === 0){
            return getErr('当前数据没有更新');
        }
        return getErr('当前数据更新失败');
    }

    public function batchAddItineraryDateData($orderModel,$data = array())
    {
        $orderId = $orderModel->id;
        $dateRange =  get_date_from_range($orderModel->departure_date,$orderModel->back_departure_date);
        if(empty($dateRange)){
            exception('请提交线路日期');
        }
        $itineraryAmount = $orderModel->itinerary_amount;
        if ($itineraryAmount <=0) {
            exception('请提交线数量');
        }
        $dateModel = new ItineraryDateModel();
        $dateModel->where('order_id',$orderId)->delete();
        for ($i = 0; $i < $itineraryAmount;$i++){
            $itineraryId = uniqid();
            foreach ($dateRange as $index => $item) {
                $ret['order_id'] = $orderId;
                $ret['itinerary_id'] = $itineraryId;
                $ret['itinerary_date'] = $item;
                $data[] = $ret;
            }
        }
        if(!$dateModel->saveAll($data)) {
            exception($dateModel->getError());
        }
    }

    public function modifyPassengerAmount($type = null)
    {
        $orderId = $this->post('id');
        $orderModel = OrderModel::get($orderId);
        $type = $this->post('type',$type);
        if(isset($type) && $type == 'reduce'){
            $orderModel->passenger_amount--;
        }else{
            $orderModel->passenger_amount++;
            $passengerModel = new PassengerController();
            $emptyPassengerAmount = 1;
            $passengerModel->batchAddEmptyPassengerData($orderModel,$emptyPassengerAmount);
        }
        if($orderModel->save()){
            return getSucc('订单人数修改成功');
        }
        return getErr('订单人数修改失败');
    }
}
