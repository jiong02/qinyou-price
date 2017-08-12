<?php

namespace app\ims\controller;

use app\ims\model\PassengerModel;

class PassengerController extends PrivilegeController
{
   public $itineraryId;
   public $passengerId;
    public function __construct()
    {
        $this->itineraryId = $this->post('itin_id');
    }

    public function addOrUpdatePassengerData()
    {
        $request = $this->post();
        $passengerModel = PassengerModel::get($request['cust_id']);
        unset($request['cust_id']);
        $passengerModel->formatInput($request);
        if($passengerModel->save()){
            return getSucc('数据操作成功');
        }
        return getErr('数据操作失败');
    }

    public function getPassengerDataById($return = [])
    {
        $id = $this->post('id');
        if($id && $passengerModel = PassengerModel::get($id)){
            $return['sex'] = $passengerModel->gender;
            $return['name'] = $passengerModel->passenger_name;
            $return['passport'] = $passengerModel->passport_no;
            $return['birth'] = $passengerModel->birthday;
            return getSucc($return);
        }
        return getErr('信息获取失败');
    }

    public function getAllPassengerBaseDataByOrderId()
    {
        $orderId = $this->post('id');
        $passengerModel = new PassengerModel();
        $passengerData = $passengerModel->where('order_id',$orderId)->select();
        $return = $this->formatReturnPassengerBaseData($passengerData);
        return getSucc($return);
    }

    public function getAllPassengerDataByOrderId()
    {
        $orderId = $this->post('order_id');
        $passengerModel = new PassengerModel();
        $passengerData = $passengerModel->where('order_id',$orderId)
            ->where('itinerary_id','0')
            ->select();
        halt($passengerData);
        $return = $this->formatReturnPassengerData($passengerData);
        return getSucc($return);
    }

    public function getAllPassengerDataByItineraryId()
    {
        $itineraryId = $this->post('itin_id');
        $passengerModel = new PassengerModel();
        $passengerData = $passengerModel->where('itinerary_id',$itineraryId)->select();
        $return = $this->formatReturnPassengerData($passengerData);
        return getSucc($return);
    }

    public function getPassengerBaseInfo()
    {
        $passengerId = $this->post('id');
        if(isset($passengerId)){
            $passengerModel = PassengerModel::get($passengerId);
            $return = $this->formatPassengerBaseData($passengerModel);
            return getSucc($return);
        }
        return getErr('信息获取失败');
    }

    public function deletePassengerData()
    {
        $passengerId =  $this->post('id');
        $passengerModel = PassengerModel::get($passengerId);
        if($passengerModel->delete()){
            $passengerModel->orderModel->passenger_amount--;
            if($passengerModel->orderModel->save()){
                return getSucc('删除成功');
            }
        }
        return getSucc('删除失败');
    }

    public function formatReturnPassengerBaseData($passengerData,$return = [])
    {
        foreach ($passengerData as $index => $passengerDatum) {
            $return[] = $this->formatPassengerBaseData($passengerDatum);
        }
        return $return;
    }

    public function formatPassengerBaseData($passengerModel)
    {
        $return['id'] = $passengerModel->id;
        $return['name'] = $passengerModel->passenger_name;
        $return['age'] = $passengerModel->age;
        $return['state'] = $passengerModel->state;
        return $return;
    }

    public function formatReturnPassengerData($passengerData,$return = [])
    {
        foreach ($passengerData as $index => $passengerDatum) {
            $return[] = $this->formatPassengerData($passengerDatum);
        }
        return $return;
    }

    public function formatPassengerData($passengerModel)
    {
        $return['id'] = $passengerModel->id;
        $return['name'] = $passengerModel->passenger_name;
        $return['age'] = $passengerModel->age;
        $return['sex'] = $passengerModel->gender;
        $return['passport'] = $passengerModel->passport_no;
        return $return;
    }

    public function formatInputItineraryInfo()
    {
        $passengerIdArray = json_decode($this->post('cust'),true);
        foreach ($passengerIdArray as $index => $item) {
            $passengerId[] = $item['cust_id'];
        }
        $this->passengerId = $passengerId;
    }

    /**
     * 添加客户所属线路
     */
    public function modifyPassengerItineraryInfo()
    {
        $this->formatInputItineraryInfo();
        $itineraryId = $this->itineraryId;
        $passengerModel = new PassengerModel();
        $result = $passengerModel->where('id','IN',$this->passengerId)
            ->update(['itinerary_id'=>$itineraryId]);
        if ($result) {
            $succData = $this->getPassengerItineraryInfo($itineraryId);
            return getSucc($succData);
        }
        return getErr('修改失败');
    }

    public function getPassengerItineraryInfo($itineraryId = null)
    {
        $itineraryId = $this->post('itin_id',$itineraryId);
        $passengerModel = new PassengerModel();
        $where['itinerary_id'] = $itineraryId;
        $passengerAmount = $passengerModel->where($where)->count();
        $state = 1;
        if($passengerAmount === 0){
            $state = 0;
        }
        $where['gender'] = '男';
        $maleAmount = $passengerModel->where($where)->count();
        $where['gender'] = '女';
        $femaleAmount = $passengerModel->where($where)->count();
        $return['persons'] = $passengerAmount;
        $return['state'] = $state;
        $return['male'] = $maleAmount;
        $return['female'] = $femaleAmount;
        return $return;
    }

    /**
     * 重置用户线路信息
     * @return \think\response\Json
     */
    public function resetPassengerItineraryInfo()
    {
        $this->formatInputItineraryInfo();
        $itineraryId = $this->itineraryId;
        $passengerModel = new PassengerModel();
        $result = $passengerModel->where('id','IN',$this->passengerId)
            ->where('itinerary_id',$itineraryId)
            ->update(['itinerary_id'=>0]);
        if ($result) {
            $succData = $this->getPassengerItineraryInfo($itineraryId);
            return getSucc($succData);
        }
        return getErr('修改失败1');
    }


    public function batchAddEmptyPassengerData($orderModel,$emptyPassengerAmount = null)
    {
        if (!isset($emptyPassengerAmount)){
            $emptyPassengerAmount = $orderModel->passenger_amount;
        }
        for ($i = 0;$i<$emptyPassengerAmount;$i++) {
            $data[$i]['order_id'] = $orderModel->id;
            $data[$i]['itinerary_id'] = 0;
        }
        $passengerModel = new PassengerModel();
        if(!$passengerModel->saveAll($data)){
            exception('客户资料新增失败!');
        }
    }

    public function getMessageByOrderId($orderId)
    {
        $passengerModel = new PassengerModel();
        $statusAmount = $passengerModel->where('order_id',$orderId)->where('passenger_data_status',0)->count();
        $passportAmount = $passengerModel->where('order_id',$orderId)->where('passport_no','')->count();
        $message = '资料未完善';
        if ($statusAmount === 0){
            $message = '资料完善中';
            if ($passportAmount === 0){
                $message = '资料已完善';
            }
        }
        return $message;
    }
}
