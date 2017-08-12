<?php

namespace app\route\controller;

use app\ims\controller\BasePricingController;
use app\ims\model\VehicleBaseModel;
use app\ims\model\VehicleCityModel;
use app\ims\model\VehicleModel;
use app\route\model\RouteFareModel;
use app\route\model\RouteHotelRoomModel;
use app\route\model\RouteModel;
use app\route\model\RouteVehicleModel;
use think\Request;

class RouteFareController extends BasePricingController
{
    public $routeId;
    public $routeData;

    public function __construct(Request $request = null)
    {
        $request->routeId = $request->param('route_id');
        $request->startDate = $request->param('start_date');
        $request->endDate = $request->param('end_date');
        $request->checkInDate = $request->param('check_in_date');
        $request->adultFare = $request->param('adult_fare');
        $request->childFare = $request->param('child_fare');
        $request->isEnable = $request->param('allow');
        parent::__construct($request);
    }

    public function initRouteRoomData($routeRoomData)
    {
        $this->roomId = $routeRoomData->room_id;
        $this->stayingNights = $routeRoomData->check_in_night_amount;
    }

    public function checkAllRouteParam($params = [])
    {
        $data['route_id'] = $this->request->routeId;
        $rule['route_id'] = 'require|integer|>:0';
        foreach ($params as $index => $param) {
            $data[$index] = $param[0];
            $rule[$index] = $param[1];
        }
        $result = $this->validate($data,$rule);
        if ($result !== true){
            abortError($result);
        }
        $routeModel = new RouteModel;
        if(!$routeData = $routeModel->get($this->request->routeId)){
            abortError('route_id错误');
        }
        $this->routeData = $routeData;
    }

    public function addRouteDate(Request $request)
    {
        $param['start_date'] = [$request->startDate,'require|date'];
        $param['end_date'] = [$request->endDate,'require|date'];
        $this->checkAllRouteParam($param);
        $this->routeData->start_time = $request->startDate;
        $this->routeData->end_time = $request->endDate;
        if ($this->routeData->save() === false){
            abortError('日期保存失败');
        }
        $result = $this->getRoutePlainFare();
        return $result;
    }

    public function modifyRouteFare(Request $request)
    {
        $routeId = $request->routeId;
        $adultFare = $request->adultFare;
        $childFare = $request->childFare;
        $isEnable = $request->isEnable;
        $checkInDate = $request->checkInDate;
        $param['adult_fare'] = [$adultFare,'require|integer|>:0'];
        $param['child_fare'] = [$childFare,'require|integer|>:0'];
        $param['is_enable'] = [$isEnable,'require|integer|>=:0'];
        $param['check_in_date'] = [$checkInDate,'require|date'];
        $this->checkAllRouteParam($param);
        $where['route_id'] = $routeId;
        $where['expired_date'] = $checkInDate;
        $routeFareModel = new RouteFareModel();
        if ($routeFareData = $routeFareModel->where($where)->find()){
            $routeFareData->adult_fare = $adultFare;
            $routeFareData->child_fare = $childFare;
            $routeFareData->is_enable = $isEnable;
            if ($routeFareData->save()){
                return getSuccess('修改成功');
            }
            return getError('修改失败');
        }else{
            $routeFareModel->expired_date = $checkInDate;
            $routeFareModel->route_id = $routeId;
            $routeFareModel->adult_fare = $adultFare;
            $routeFareModel->child_fare = $childFare;
            $routeFareModel->is_enable = $isEnable;
            if ($routeFareModel->save()){
                return getSuccess('新增成功');
            }
        }
    }

    public function getRoutePlainFare($result = [])
    {
        $this->checkAllRouteParam();
        $this->routeId = $this->request->routeId;
        $routeData = $this->routeData;
        if ($routeData->start_time == '0000-00-00' || $routeData->end_time == '0000-00-00'){
            return getSuccess($result);
        }
        $dateSet = get_date_from_range($routeData->start_time,$routeData->end_time);
        $routeFareModel = new RouteFareModel();
        $forceFareData  = $routeFareModel->where('route_id',$this->routeId)->column('*','expired_date');
        $routeHotelRoomModel = new RouteHotelRoomModel();
        $routeRoomData = $routeHotelRoomModel->where('route_id',$this->routeId)->find();
        if (!$routeRoomData){
            return getError('没有选择房型或线路不存在');
        }
        $totalFare = [];
        foreach ($dateSet as $date) {
            $allow = true;
            $this->checkInDate = $date;
            if (isset($forceFareData[$date])){
                $totalFare = $forceFareData[$date]['adult_fare'];
                $allow = (boolean)$forceFareData[$date]['is_enable'];
            }else{
                $this->initRouteRoomData($routeRoomData);
                $this->initRoomData();
                $adultRoomFare = $this->pricingRoomFare('标准成人');
                if (!$adultRoomFare){
                    $allow = false;
                }else{
                    $vehicleFare = $this->pricingRouteVehicleFare('标准成人');
                    $itemFare = $this->pricingItemFare();
                    $totalFare = $this->getAdultTotalFare($adultRoomFare,$vehicleFare, $itemFare);
                }
            }
            $result[$date]['fare'] = $totalFare;
            $result[$date]['allow'] = $allow;
        }
        $result = [$result];
        $result['start_date'] = $routeData->start_time;
        $result['end_date'] = $routeData->end_time;
        return getSuccess($result);
    }

    public function getRouteFareByCheckInDate()
    {
        $this->routeId = $this->request->routeId;
        $date = $this->request->checkInDate;
        $this->checkInDate = $date;
        $this->totalFare = [];
        $this->exchangeData = [];
        $this->packageData = [];
        $param['check_in_date'] = [$date,'require|date'];
        $this->checkAllRouteParam($param);
        $routeFareModel = new RouteFareModel();
        $routeHotelRoomModel = new RouteHotelRoomModel();
        $routeRoomData = $routeHotelRoomModel->where('route_id',$this->routeId)->find();
        $forceFareData  = $routeFareModel->where('route_id',$this->routeId)->column('*','expired_date');
        if (isset($forceFareData[$date])){
            $this->totalFare['adult_fare'] = $forceFareData[$date]['adult_fare'];
            $this->totalFare['child_fare'] = $forceFareData[$date]['child_fare'];
            $this->totalFare['adult_fare_detail'] = '强制价格';
        }else{
            $this->initRouteRoomData($routeRoomData);
            $this->initRoomData();
            $adultRoomFare = $this->pricingRoomFare('标准成人');
            $itemFare = $this->pricingItemFare();
            if ($adultRoomFare){
                $this->totalFare['adult_fare_detail']['room_detail'][] = $this->roomFareDetail;
                $adultVehicleFare = $this->pricingRouteVehicleFare('标准成人');
                $this->totalFare['adult_fare_detail']['vehicle_detail'][] = $this->vehicleFareDetail;
                $this->totalFare['adult_fare'] = $this->getAdultTotalFare($adultRoomFare,$adultVehicleFare, $itemFare);
                if ($this->quantityOfChild >=1){
                    $childRoomFare = $this->pricingRoomFare('额外儿童');
                    $itemFare = $this->pricingItemFare();
                    $this->totalFare['child_fare_detail']['room_detail'][] = $this->roomFareDetail;
                    $childVehicleFare = $this->pricingRouteVehicleFare('额外儿童');
                    $this->totalFare['child_fare_detail']['vehicle_detail'][] = $this->vehicleFareDetail;
                    $this->totalFare['child_fare'] = $this->getChildTotalFare($childRoomFare,$childVehicleFare, $itemFare);
                }
                $this->totalFare['exchange_data'] = $this->exchangeData;
            }
        }
        return (boolean)$this->totalFare ? $this->totalFare : false;
    }

    public function pricingRouteVehicleFare($packageType = '标准成人')
    {
        $this->vehicleFareDetail = [];
        $routeVehicleModel = new RouteVehicleModel();
        $routeVehicleData = $routeVehicleModel->where('route_id',$this->routeId)->select();
        $vehicleFare = 0;
        foreach ($routeVehicleData as $index => $routeVehicleDatum) {
            $vehicleCategory = $routeVehicleDatum->vehicle_category;
            $vehicleId = $routeVehicleDatum->vehicle_id;
            $vehicleType = $routeVehicleDatum->vehicle_type;
            if ($vehicleCategory == '往返交通'){
                $vehicleBaseModel = new VehicleBaseModel();
                $vehicleCityModel = VehicleCityModel::get($vehicleId);
                $vehicleBaseData = $vehicleBaseModel->where('id','IN',$vehicleCityModel->vehicle_base_id)->select();
                foreach ($vehicleBaseData as $key => $item) {
                    $vehicleId = $item['id'];
                    if ($item->vehicle_category == '单程交通'){
                        $vehicleModel = new VehicleModel();
                        $vehicleModel = $vehicleModel->where('vehicle_base_id',$vehicleId)->find();
                        $vehicleId = $vehicleModel->id;
                    }
                    $vehicleFare += $this->pricingVehicleNode($vehicleId, $item->vehicle_category, $vehicleType, $packageType);
                }
            }else{
                $vehicleFare += $this->pricingVehicleNode($vehicleId, $vehicleCategory, $vehicleType, $packageType);
            }
        }
        return $vehicleFare;

    }

    public function pricingVehicleNode($vehicleId, $vehicleCategory, $itineraryType, $packageType)
    {
        if ($this->packageData->package_type == $packageType){
            $includeGoVehicle = json_decode($this->packageData->include_go_vehicle,true);
            $includeBackVehicle = json_decode($this->packageData->include_back_vehicle, true);
        }else{
            $includeGoVehicle = [];
            $includeBackVehicle = [];
        }

        if (!empty($includeGoVehicle) && $itineraryType == '去程'){
            foreach ($includeGoVehicle as $item) {
                if ($vehicleCategory == $item['category'] && $vehicleId == $item['id']){
                    $vehicleFare = 0;
                    $this->vehicleFareDetail[$itineraryType][] = $fareDetail = '包含';
                }
            }
        }

        if(!empty($includeBackVehicle) && $itineraryType == '返程'){
            foreach ($includeGoVehicle as $item) {
                if ($vehicleCategory == $item['category'] && $vehicleId == $item['id']){
                    $vehicleFare = 0;
                    $this->vehicleFareDetail[$itineraryType][] = $fareDetail = '包含';
                }
            }
        }

        if (!isset($vehicleFare)){
            $vehicleFare = $this->pricingVehicleFare($vehicleId, $vehicleCategory, $itineraryType, $packageType);
        }
        return $vehicleFare;
    }

}