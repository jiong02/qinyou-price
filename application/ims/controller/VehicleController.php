<?php

namespace app\ims\controller;

use app\ims\model\HotelModel;
use app\ims\model\VehicleModel;
use app\ims\model\VehicleTimeModel;
use app\ims\model\VehicleBaseModel;


class VehicleController extends VehicleBasicController
{
    /**
     * 获取单程交通信息
     */
    public function getSingleData()
    {
        $vehicleId = $this->vehicleId;
        $type = $this->post('type','定期交通');
        $vehicleModel = VehicleModel::get($vehicleId);
        if($type == '接驳交通') {
            $baseData = $vehicleModel->singleVehicleBase->formatOutPut();
            $data = $vehicleModel->formatOutPut();
            $return['info'] = array_merge($data,$baseData);
            $return['nav'] = $vehicleModel->formatData('nav');
        }elseif($type == '定期交通'){
            $baseData = $vehicleModel->singleVehicleBase->formatOutPut();
            $data = $vehicleModel->formatOutPut();
            $data['step_shift'] = $vehicleModel->vehicleTime->step_shift;
            $return['info'] = array_merge($data,$baseData);
            $return['nav'] = $vehicleModel->formatData('nav');
        }
        unset($return['singleVehicleBase']);
        return getSucc($return);
    }

    // +----------------------------------------------------------------------
    // | 单程定期数据操作
    // +----------------------------------------------------------------------
    /**
     * 添加定期交通信息
     */
    public function addFixedData()
    {
        $vehicleModel = new VehicleModel();
        $data = $this->formatInputData();
        if($vehicleModel->save($data)){
            $baseData = $this->formatInputBaseData();
            $vehicleModel->singleVehicleBase()->save($baseData);
            $vehicleModel->isUpdate()->save(['vehicle_base_id'=>$vehicleModel->singleVehicleBase->id]);
            $this->vehicleId = $vehicleModel->id;
            $this->addTimeData();
        }
        return getSucc('定期交通信息添加成功');
    }

    /**
     * 获取定期交通信息
     */
    public function getFixedData()
    {
        $vehicleId = $this->vehicleId;
        $vehicleModel = VehicleModel::get($vehicleId);
        $baseData = $vehicleModel->singleVehicleBase->formatOutPut();
        $vehicleData = $vehicleModel->formatOutPut();
        $vehicleData = array_merge($vehicleData,$baseData);
        $vehicleData['step_shift'] = $vehicleModel->vehicleTime->step_shift;
        return getSucc($vehicleData);
    }

    /**
     * 修改定期交通信息
     */
    public function modifyFixedData()
    {
        $vehicleId = $this->vehicleId;
        $vehicleModel = VehicleModel::get($vehicleId);
        $data = $this->formatInputData();
        $dataResult = $vehicleModel->save($data);
        if($dataResult || $dataResult === 0) {
            $baseData = $this->formatInputBaseData();
            $baseResult = $vehicleModel->singleVehicleBase->save($baseData);
            if(!$baseResult && $baseResult !== 0){
                return getErr('单程定期基本交通信息修改失败');
            }
            $this->addTimeData();
            return getSucc('单程定期交通信息修改成功!');
        }
        return getErr('单程定期交通信息修改失败!');
    }

    /**
     * 删除定期交通信息
     */
    public function deleteFixedData()
    {
        $vehicleModel = VehicleModel::get($this->vehicleId);
        if($vehicleModel->delete()){
            $vehicleModel->singleVehicleBase->delete();
            $vehicleModel->vehicleTime->delete();
            return getSucc('信息删除成功');
        }else{
            return getErr('信息删除失败!');
        }
    }

    // +----------------------------------------------------------------------
    // | 单程接驳数据操作
    // +----------------------------------------------------------------------
    /**
     * 新增单程接驳交通
     */
    public function addTransferData()
    {
        $vehicleModel = new VehicleModel();
        $data = $this->formatInputData();
        if($vehicleModel->save($data)){
            $baseData = $this->formatInputBaseData();
            $vehicleModel->singleVehicleBase()->save($baseData);
            $vehicleModel->isUpdate()->save(['vehicle_base_id'=>$vehicleModel->singleVehicleBase->id]);
            return getSucc('单程接驳交通新增成功!');
        }
        return getErr('单程接驳交通新增失败!');
    }

    public function getTransferData()
    {
        $vehicleModel = VehicleModel::get($this->vehicleId);
        $data = $vehicleModel->data;
        $baseData = $vehicleModel->singleVehicleBase->data;
        $transferData = array_merge($data,$baseData);
        return getSucc($transferData);
    }

    public function modifyTransferData()
    {
        $vehicleModel = VehicleModel::get($this->vehicleId);
        $data = $this->formatInputData();
        $dataResult = $vehicleModel->save($data);
        if($dataResult || $dataResult === 0){
            $baseData = $this->formatInputBaseData();
            $baseResult = $vehicleModel->singleVehicleBase->save($baseData);
            if($dataResult === 0 && $baseResult === 0){
                return getErr('当前数据没有任何更新');
            }elseif(!$baseResult && $baseResult !== 0){
                return getErr('单程接驳基本数据更新失败');
            }
            return getSucc('单程接驳数据更新成功');
        }
        return getErr('单程接驳数据更新失败');
    }

    /**
     * 删除单程接驳交通
     */
    public function deleteTransferData()
    {
        $vehicleModel = VehicleModel::get($this->vehicleId);
        if($vehicleModel->delete()){
            $vehicleModel->singleVehicleBase->delete();
            return getSucc('删除驳交通信息成功!');
        }else{
            return getErr('删除接驳交通信息失败!');
        }
    }
}
