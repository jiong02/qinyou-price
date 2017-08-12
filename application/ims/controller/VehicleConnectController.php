<?php

namespace app\ims\controller;

use app\ims\model\VehicleBaseModel;
use app\ims\model\VehicleModel;

class VehicleConnectController extends VehicleBasicController
{
    /**
     * 获取联程线路信息
     */
    public function getConnectRouteInfo()
    {
        $vehicleBaseId = $this->vehicleId;
        $baseModel = VehicleBaseModel::get($vehicleBaseId);
        $baseData['info'] = $baseModel->formatOutPut();
        $baseData['journey_type'] = $baseModel->connect_journey_type;
        $baseData['info']['week'] = explode(',',$baseModel->connect_departure_week);
        $banner = [];
        foreach ($baseModel->connectVehicle as $index => $item) {
            $banner[] = $item->formatData('nav');
        }
        $baseData['banner'] = $banner;
        return getSucc($baseData);
    }

    /**
     * 查询联程联程线路
     */
    public function queryConnectRoutList()
    {
        $where['hotel_id'] = $this->hotelId;
//        $where['hotel_id'] = 22;
        $where['vehicle_category'] = '联程交通';
        if(isset($this->journeyType) && !empty($this->journeyType)){
            $where['connect_journey_type'] = $this->journeyType;
        }
        $week = $this->post('week');
        if (isset($week)){
            $where['connect_departure_week'] = $week;
        }
        $baseModel = new VehicleBaseModel();
        $basePageData = $this->getPageData($baseModel,$where);
//        halt($basePageData->toArray());
//        halt($basePageData->toArray());
        $list = [];
        foreach ($basePageData as $index => $basePageDatum) {
//            halt($basePageDatum->toArray());
            $ret['type'] = '联程交通';
            $ret['trf_id'] = $basePageDatum->id;
            $ret['days'] = $basePageDatum->connect_departure_week;
            $place = [];
//            halt($basePageDatum->connect_vehicle->toArray());
            foreach ($basePageDatum->connect_vehicle as $key => $value) {
                if($key === 0){
                    $place[] = ['name'=>$value->departure_place_name];
                }
                $place[] = ['name'=>$value->destination_name];
            }
            $ret['place'] = $place;
            $list[] = $ret;
/*            var_dump($place);
            halt($ret);*/
        }
        $return['total'] = $this->getPageCount($baseModel,$where);
        $return['list'] = $list;
        return getSucc($return);
    }

    /**
     * 添加联程线路信息
     */
    public function addConnectRouteData()
    {
        $data = $this->formatInputBaseData();
        unset($data['vehicle_id']);
        $data['vehicle_category'] = '联程交通';
        $data['connect_journey_type'] = $this->post('journey_type');
        $week = json_decode($this->post('week'),true);
        $data['connect_departure_week'] = implode(',',$week);
        $vehicleId = json_decode($this->vehicleId,true);
        $vehicleBaseModel =  new VehicleBaseModel();
        if($vehicleBaseModel->save($data)){
            $result = VehicleModel::where('id','IN',$vehicleId)
                ->where('vehicle_base_id',0)
                ->update(['vehicle_base_id'=>$vehicleBaseModel->id]);
            if($result){
                return getSucc('联程线路新增成功');
            }
            return getErr('交通信息更新失败');
        }
        return getErr('联程线路新增失败!');
    }

    /**
     * 删除联程线路信息
     */
    public function deleteConnectRouteInfo()
    {
        $vehicleBaseId = $this->vehicleId;
        $baseModel = VehicleBaseModel::get($vehicleBaseId);
        $result = VehicleModel::where('vehicle_base_id',$baseModel->id)->update(['vehicle_base_id'=>0]);
        if($result) {
            if($baseModel->delete()){
                return getSucc('联程线路删除成功');
            }
            return getErr('联程线路删除失败');
        }
        return getErr('联程节点信息更新是失败');
    }

    /**
     * 删除联程信息
     */
    public function deleteConnectData()
    {
        $vehicleModel = VehicleModel::get($this->vehicleId);
        if($vehicleModel->delete()){
            return getSucc('联程交通删除成功!');
        }
        return getErr('联程交通删除失败!');
    }

    // +----------------------------------------------------------------------
    // | 联程接驳数据操作
    // +----------------------------------------------------------------------
    /**
     * 添加联程接驳信息
     */
    public function addConnectTransferData()
    {
        $vehicleModel = new VehicleModel();
        $data = $this->formatInputData();
        if($vehicleModel->save($data)){
            return getSucc('联程接驳交通新增成功!');
        }
        return getErr('联程接驳交通新增失败!');
    }
    /*
     * 修改联程接驳信息
     */
    public function modifyConnectTransferData()
    {
        $vehicleModel = VehicleModel::get($this->vehicleId);
        $data = $this->formatInputData();
        if($vehicleModel->isUpdate()->save($data)){
            return getSucc('联程接驳交通修改成功!');
        }
        return getErr('联程接驳交通修改失败!');
    }

    /*
    * 获取联程接驳信息
    */
    public function getConnectTransferData()
    {
        if( $vehicleModel = VehicleModel::get($this->vehicleId)){
            $data = $vehicleModel->formatOutPut();
            return getSucc($data);
        }
        return getErr('联程接驳交通删除失败!');
    }

    // +----------------------------------------------------------------------
    // | 联程定期数据操作
    // +----------------------------------------------------------------------
    /*
     * 新增联程定期信息
     */
    public function addConnectFixedData()
    {
        $vehicleModel = new VehicleModel();
        $data = $this->formatInputData();
        if($vehicleModel->save($data)){
            $this->vehicleId = $vehicleModel->id;
            $this->addTimeData();
        }
        return getSucc('定期交通信息添加成功');
    }

    public function getConnectFixedData()
    {
        $vehicleId = $this->vehicleId;
        $vehicleModel = VehicleModel::get($vehicleId);
        $vehicleData = $vehicleModel->formatOutPut();
        $vehicleData['step_shift'] = $vehicleModel->vehicleTime->step_shift;
        return getSucc($vehicleData);
    }

    /**
     * 修改联程定期交通信息
     */
    public function modifyConnectFixedData()
    {
        $vehicleId = $this->vehicleId;
        $vehicleModel = VehicleModel::get($vehicleId);
        $data = $this->formatInputData();
        $dataResult = $vehicleModel->save($data);
        if($dataResult || $dataResult === 0) {
            $this->vehicleId = $vehicleModel->id;
            $this->addTimeData();
            return getSucc('单程定期交通信息修改成功!');
        }
        return getErr('单程定期交通信息修改失败!');
    }

    /**
     * 删除联程定期交通信息
     */
    public function deleteConnectFixedData()
    {
        $vehicleModel = VehicleModel::get($this->vehicleId);
        if($vehicleModel->delete()){
            $vehicleModel->vehicleTime->delete();
            return getSucc('信息删除成功');
        }else{
            return getErr('信息删除失败!');
        }
    }

    /**
     * 获取联程节点信息
     */
    public function getConnectNodeInfo()
    {
        $where['id'] = $this->vehicleId;
        $vehicleModel = VehicleModel::where($where)->find();
        $return['info'] = $vehicleModel
            ->hidden(['single_journey_type','vehicle_category'])
            ->formatOutPut();
        $return['nav'] = $vehicleModel->formatData('nav');
        $return['banner'] = $vehicleModel->formatData('banner');
        if($vehicleModel->vehicle_type == '定期交通'){
            $return['info']['step_shift'] = $vehicleModel->vehicleTime->step_shift;
        }
        return getSucc($return);
    }
}
