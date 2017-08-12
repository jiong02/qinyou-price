<?php

namespace app\ims\controller;

use app\ims\model\HotelDefaultVehicleModel;
use think\Request;

class HotelDefaultVehicleController extends PrivilegeController
{
    public function formatInputVehicleData(Request $request)
    {
        $inputVehicleData = $request->param();
        $inputVehicleData['default_go_vehicle'] = json_encode($request->param('default_go_vehicle'));
        $inputVehicleData['default_back_vehicle'] = json_encode($request->param('default_back_vehicle'));
        return $inputVehicleData;
    }

    public function addVehicleData(Request $request)
    {
        $inputVehicleData = $this->formatInputVehicleData($request);
        if(HotelDefaultVehicleModel::create($inputVehicleData)){
            return getSucc('数据新增成功!');
        }
        return getErr('数据新增失败!');
    }

    public function getVehicleData(Request $request)
    {
        $hotelId = $request->param('hotel_id');
       $vehicleData = HotelDefaultVehicleModel::where('hotel_id',$hotelId)->find();
       return getsucc($vehicleData);
    }

    public function modifyVehicleData(Request $request)
    {
        $defaultVehicleId = $request->param('default_vehicle_id');
        $defaultVehicleModel = HotelDefaultVehicleModel::get($defaultVehicleId);
        $inputVehicleData = $this->formatInputVehicleData($request);

        unset($inputVehicleData['default_vehicle_id']);
        if($defaultVehicleModel->save($inputVehicleData)){
            return getSucc('数据修改成功!');
        }
        return getErr('数据修改失败!');
    }


}
