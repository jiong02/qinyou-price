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

    public $standardLogic = '或';
    public $standardQuantityOfAdult = 2;
    public $standardQuantityOfChild = 1;
    public $standardQuantityOfExtraAdult = 1;
    public $standardQuantityOfRoom = 0;
    public $inputQuantityOfAdult;
    public $inputQuantityOfChild;
    public $inputQuantityOfRoom;
    public $maxPassengers = 20;
    public $minPassengers = 2;
    public $error;

    public $haveBalanceForRoomCharge = 0;
    public $haveExtraAdult = 0;
    public $childToAdult = 0;

    public $quantityOfRoom;
    public function __construct(Request $request = null)
    {
        $request->routeId = $request->param('route_id', 1);
        $request->startDate = $request->param('start_date');
        $request->endDate = $request->param('end_date');
        $request->checkInDate = $request->param('check_in_date', '2017-07-01');
        $request->adultFare = $request->param('adult_fare');
        $request->childFare = $request->param('child_fare');
        $request->isEnable = $request->param('allow');
        $this->inputQuantityOfAdult = $request->param('quantity_Of_Adult', 8);
        $this->inputQuantityOfChild = $request->param('quantity_Of_Child', 2);
        $this->inputQuantityOfRoom = $request->param('quantity_of_Room',4);
        parent::__construct($request);
    }

    public function pricingRouteFare()
    {
        $totalFare = 0;
        if (!$roomArrangementList = $this->pricingInputQuantityOfRoom()){
            return getError($this->error);
        }
        foreach ($roomArrangementList as $item) {
            $data = $this->getRouteFareByCheckInDate($item);
            if (isset($data['adult_fare'])){
                $totalFare += $data['adult_fare'];
                $adultFare = $data['adult_fare'];
            }
            if (isset($data['child_fare'])){
                $totalFare += $data['child_fare'];
                $childFare = $data['child_fare'];
            }
            if (isset($data['extra_adult_fare'])){
                $totalFare += $data['extra_adult_fare'];
                $extraFare = $data['extra_adult_fare'];
            }
        }

        $farDetail['total_fare'] = $totalFare;
        $farDetail['quantity_of_Room'] = $this->quantityOfRoom;
        if ($this->haveBalanceForRoomCharge != 0 ){
            $farDetail['room_charge'] = $this->haveBalanceForRoomCharge * $adultFare;
        }elseif($this->haveExtraAdult !=0){
            $farDetail['extra_adult'] = $this->haveExtraAdult;
        }elseif($this->childToAdult){
            $farDetail['child_to_adult'] = $this->childToAdult * $adultFare;
        }
        return getSuccess($farDetail);
    }

    public function pricingInputQuantityOfRoom()
    {
        $standardLogic = $this->standardLogic;
        $standardQuantityOfAdult = $this->standardQuantityOfAdult;
        $standardQuantityOfChild = $this->standardQuantityOfChild;
        $standardQuantityOfExtraAdult = $this->standardQuantityOfExtraAdult;
        $inputQuantityOfAdult = $this->inputQuantityOfAdult;
        $inputQuantityOfChild = $this->inputQuantityOfChild;
        $inputQuantityOfRoom = $this->inputQuantityOfRoom;
        $inputQuantityOfPassengers = $inputQuantityOfAdult + $inputQuantityOfChild;
        $this->standardQuantityOfRoom = ceil($inputQuantityOfPassengers / ($standardQuantityOfAdult + $standardQuantityOfChild));
        $roomArrangementList = [];
        $this->haveBalanceForRoomCharge = 0;
        $this->haveExtraAdult = 0;
        $this->childToAdult = 0;
        if ($inputQuantityOfAdult <= 1) {
            $this->error = '最少要有一个成人出行';
            return '最少要有一个成人出行';
        }
        if ($inputQuantityOfChild > $inputQuantityOfAdult) {
            $this->error = '成人数量必须大于儿童数量';
            return '成人数量必须大于儿童数量';
        }
        if ($inputQuantityOfRoom > $inputQuantityOfAdult){
            $this->error = '请确保一间房最少一名大人';
            return '请确保一间房最少一名大人';
        }
        if ($inputQuantityOfChild > 0 && $standardQuantityOfChild <= 0){
            $this->error = '该房型不适合儿童入住';
            return '该房型不适合儿童入住';
        }
        if ($inputQuantityOfPassengers > $this->maxPassengers){
            $this->error = '出行人数大于最大值';
            return '出行人数大于最大值';
        }
        if ($inputQuantityOfPassengers < $this->minPassengers){
            $this->error = '出行人数小于最小值';
            return '出行人数小于最小值';
        }
        if ($inputQuantityOfRoom <= $this->standardQuantityOfRoom){
            $this->quantityOfRoom = $this->standardQuantityOfRoom;
            while ($inputQuantityOfPassengers > 0) {
                $roomArrangement = [];
                $roomArrangement['standard_quantity_of_adult'] = 0;
                $roomArrangement['standard_quantity_of_child'] = 0;
                $roomArrangement['standard_quantity_of_extra_adult'] = 0;
                if ($inputQuantityOfAdult >= $standardQuantityOfAdult) {
                    $roomArrangement['standard_quantity_of_adult'] = $standardQuantityOfAdult;
                    $inputQuantityOfAdult -= $standardQuantityOfAdult;
                    $inputQuantityOfPassengers -= $standardQuantityOfAdult;
                    if ($inputQuantityOfAdult != 0 && $inputQuantityOfChild == 0) {
                        $roomArrangement['standard_quantity_of_extra_adult'] = $standardQuantityOfExtraAdult;
                        $inputQuantityOfAdult -= $standardQuantityOfExtraAdult;
                        $inputQuantityOfPassengers -= $standardQuantityOfExtraAdult;
                        $this->haveExtraAdult += 1;
                    }
                    if ($inputQuantityOfChild >= $standardQuantityOfChild) {
                        $roomArrangement['standard_quantity_of_child'] = $standardQuantityOfChild;
                        $inputQuantityOfChild -= $standardQuantityOfChild;
                        $inputQuantityOfPassengers -= $standardQuantityOfChild;
                    }else{
                        $roomArrangement['standard_quantity_of_child'] = $inputQuantityOfChild;
                        $inputQuantityOfPassengers -= $inputQuantityOfChild;
                        $inputQuantityOfChild = 0;
                    }

                }else{
                    //标准人数减去剩余人数即为需要减去的儿童数量
                    $cutInputQuantityOfChild = $standardQuantityOfAdult - $inputQuantityOfAdult;
                    if ($inputQuantityOfChild >= $cutInputQuantityOfChild) {
                        $inputQuantityOfAdult += $cutInputQuantityOfChild;
                        $this->childToAdult += $cutInputQuantityOfChild;
                        $inputQuantityOfChild -= $cutInputQuantityOfChild;
                    }else{
                        $inputQuantityOfAdult += $inputQuantityOfChild;
                        $this->childToAdult += $inputQuantityOfChild;
                        $inputQuantityOfChild = 0;
                    }
                    if ($inputQuantityOfAdult >= $standardQuantityOfAdult) {
                        $roomArrangement['standard_quantity_of_adult'] = $standardQuantityOfAdult;//2 -5 -3 -1 0
                        $inputQuantityOfAdult -= $standardQuantityOfAdult;
                        $inputQuantityOfPassengers -= $standardQuantityOfAdult;
                        if ($inputQuantityOfChild >= $standardQuantityOfChild) {
                            $roomArrangement['standard_quantity_of_child'] = $standardQuantityOfChild; //1 -4 -3 -2 0
                            $inputQuantityOfChild -= $standardQuantityOfChild;
                            $inputQuantityOfPassengers -= $standardQuantityOfChild;
                        }else{
                            $roomArrangement['standard_quantity_of_child'] = $inputQuantityOfChild;
                            $inputQuantityOfPassengers -= $inputQuantityOfChild;
                            $inputQuantityOfChild = 0;
                        }
                        if ($inputQuantityOfAdult >= $standardQuantityOfExtraAdult && $inputQuantityOfChild == 0) {
                            $roomArrangement['standard_quantity_of_extra_adult'] = $standardQuantityOfExtraAdult;
                            $inputQuantityOfAdult -= $standardQuantityOfExtraAdult;
                            $inputQuantityOfPassengers -= $standardQuantityOfExtraAdult;
                            $this->haveExtraAdult += 1;
                        }
                    }else{
                        $roomArrangement['standard_quantity_of_adult'] = $inputQuantityOfAdult;
                        if ($inputQuantityOfAdult < $standardQuantityOfAdult) {
                            $this->haveBalanceForRoomCharge += 1;
                        }
                        $inputQuantityOfPassengers -= $inputQuantityOfAdult;
                        $inputQuantityOfAdult = 0;
                    }
                }
                if ($inputQuantityOfAdult == 0 && $inputQuantityOfChild == 0) {
                    $inputQuantityOfPassengers = 0;
                }
                $roomArrangementList[] = $roomArrangement;
            }
        }else{
            $this->quantityOfRoom = $this->inputQuantityOfRoom;
            $standardQuantityOfAdult = $this->standardQuantityOfAdult;
            $standardQuantityOfChild = $this->standardQuantityOfChild;
            $standardQuantityOfExtraAdult = $this->standardQuantityOfExtraAdult;
            $inputQuantityOfAdult = $this->inputQuantityOfAdult;
            $inputQuantityOfChild = $this->inputQuantityOfChild;
            $inputQuantityOfRoom = $this->inputQuantityOfRoom;
            $inputQuantityOfPassengers = $inputQuantityOfAdult + $inputQuantityOfChild;
            $this->standardQuantityOfRoom = ceil($inputQuantityOfPassengers / ($standardQuantityOfAdult + $standardQuantityOfChild));
            $roomArrangementList = [];
            $this->haveBalanceForRoomCharge = 0;
            $this->haveExtraAdult = 0;
            $this->childToAdult = 0;
            $passengerOfEveryRoom = floor($inputQuantityOfPassengers / $inputQuantityOfRoom);
            $quantityOfRemainRoom = $inputQuantityOfPassengers % $inputQuantityOfRoom;
            if ($passengerOfEveryRoom == 1){
                $this->haveBalanceForRoomCharge = $inputQuantityOfRoom - $quantityOfRemainRoom;
                $this->childToAdult = $inputQuantityOfChild;
                for ($i=0; $i < $inputQuantityOfRoom ; $i++) {
                    $this->haveBalanceForRoomCharge = $this->haveBalanceForRoomCharge - 1;
                    $roomArrangement['standard_quantity_of_adult'] = 2;
                    $roomArrangement['standard_quantity_of_child'] = 0;
                    $roomArrangement['standard_quantity_of_extra_adult'] = 0;
                    if ($this->haveBalanceForRoomCharge == 0){
                        $roomArrangement['standard_quantity_of_adult'] = 1;
                    }
                    $roomArrangementList[] = $roomArrangement;
                }
            }elseif($passengerOfEveryRoom == 2){
                if($quantityOfRemainRoom < $inputQuantityOfChild){
                    $this->childToAdult = $inputQuantityOfChild - $quantityOfRemainRoom;
                    $inputQuantityOfChild = $inputQuantityOfChild - $this->childToAdult;
                    for ($i=0; $i < $inputQuantityOfRoom ; $i++) {
                        $inputQuantityOfChild = $inputQuantityOfChild - 1;
                        $roomArrangement['standard_quantity_of_adult'] = 2;
                        $roomArrangement['standard_quantity_of_child'] = 0;
                        $roomArrangement['standard_quantity_of_extra_adult'] = 0;
                        if ($inputQuantityOfChild == 0){
                            $roomArrangement['standard_quantity_of_child'] = 1;
                        }
                        $roomArrangementList[] = $roomArrangement;
                    }
                }elseif($quantityOfRemainRoom > $inputQuantityOfChild){
                    $this->haveExtraAdult = $quantityOfRemainRoom - $inputQuantityOfChild;
                    for ($i=0; $i < $inputQuantityOfRoom ; $i++) {
                        $inputQuantityOfChild = $inputQuantityOfChild - 1;
                        $roomArrangement['standard_quantity_of_adult'] = 2;
                        $roomArrangement['standard_quantity_of_child'] = 1;
                        $roomArrangement['standard_quantity_of_extra_adult'] = 0;
                        if ($inputQuantityOfChild == 0){
                            $roomArrangement['standard_quantity_of_child'] = 1;
                            $this->haveExtraAdult = $this->haveExtraAdult - 1;
                            if ($this->haveExtraAdult > 0){
                                $roomArrangement['standard_quantity_of_extra_adult'] = 1;
                            }
                        }
                        $roomArrangementList[] = $roomArrangement;
                    }
                }
            }
        }
//        if ($this->haveBalanceForRoomCharge > 0){
//            foreach ($roomArrangementList as $roomOrder => $roomArrangement){
//                if ($this->haveBalanceForRoomCharge > 0){
//                    if ($roomArrangement['standard_quantity_of_adult'] < $standardQuantityOfAdult){
//                        $roomArrangementList[$roomOrder]['standard_quantity_of_adult'] += 1;
//                        $this->haveBalanceForRoomCharge -= 1;
//                    }
//                    if ($roomArrangement['standard_quantity_of_extra_adult'] > 0 ){
//                        $roomArrangementList[$roomOrder]['standard_quantity_of_adult'] = 0;
//                    }
//                    if ($roomArrangement['standard_quantity_of_child'] > 0 ){
//                        $roomArrangementList[$roomOrder]['standard_quantity_of_child'] = 0;
//                    }
//                }
//
//            }
//        }
//        if ($inputQuantityOfRoom > $standardQuantityOfRoom) {
//
//            for ($i = 0; $i < $inputQuantityOfRoom ; $i++) {
//                if ($inputQuantityOfAdult == 0 && $inputQuantityOfChild >= $standardQuantityOfAdult) {
//                    $roomArrangement['standard_quantity_of_adult'] = $standardQuantityOfAdult;
//                    $inputQuantityOfPassengers -= $standardQuantityOfChild;
//                    $inputQuantityOfChild -= $standardQuantityOfChild;
//                }
//                if ($inputQuantityOfAdult >= $standardQuantityOfAdult) {
//                    $roomArrangement['standard_quantity_of_adult'] = $standardQuantityOfAdult;
//                    $inputQuantityOfPassengers -= $standardQuantityOfAdult;
//                    $inputQuantityOfAdult -= $standardQuantityOfAdult;
//                }else{
//                    $roomArrangement['standard_quantity_of_adult'] = $inputQuantityOfAdult;
//                    $inputQuantityOfPassengers -= $inputQuantityOfAdult;
//                    $inputQuantityOfAdult = 0;
//                }
//                if ($inputQuantityOfChild >= $standardQuantityOfChild) {
//                    $roomArrangement['standard_quantity_of_child'] = $standardQuantityOfChild;
//                    $inputQuantityOfChild -= $standardQuantityOfChild;
//                    $inputQuantityOfPassengers -= $standardQuantityOfChild;
//                }else{
//                    $roomArrangement['standard_quantity_of_child'] = $inputQuantityOfChild;
//                    $inputQuantityOfChild = 0;
//                }
//                if ($inputQuantityOfAdult == 0 && $inputQuantityOfChild == 0) {
//                    $inputQuantityOfPassengers = 0;
//                }
//                $roomArrangement['standard_quantity_of_extra_adult'] = 0;
//                $roomArrangementList[] = $roomArrangement;
//            }
//            if ($inputQuantityOfPassengers > 0) {
//                foreach ($roomArrangementList as $roomOrder => $roomArrangement) {
//                    if ($standardLogic == '且' && $inputQuantityOfPassengers > $standardQuantityOfExtraAdult) {
//                        $roomArrangementList[$roomOrder]['standard_quantity_of_extra_adult'] = $standardQuantityOfExtraAdult;
//                        $inputQuantityOfPassengers -= $standardQuantityOfExtraAdult;
//                    }else{
//                        $roomArrangementList[$roomOrder]['standard_quantity_of_extra_adult'] = 0;
//                    }
//                    if ($standardLogic == '或') {
//                        $roomArrangementList[$roomOrder]['standard_quantity_of_extra_adult'] = 0;
//                        if ($inputQuantityOfPassengers >= $standardQuantityOfExtraAdult && $roomArrangementList[$roomOrder]['standard_quantity_of_child'] == 0) {
//                            $roomArrangementList[$roomOrder]['standard_quantity_of_extra_adult'] = $standardQuantityOfExtraAdult;
//                            $inputQuantityOfPassengers -= $standardQuantityOfExtraAdult;
//                        }
//                    }
//                }
//            }
//        }else{
//            $roomArrangementList = $standardRoomArrangementList;
//        }
        return $roomArrangementList;
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

    public function getRouteFareByCheckInDate(array $passengerList = array())
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
            if ($passengerList){
                $this->quantityOfChild = $passengerList['standard_quantity_of_child'];
                $this->quantityOfExtraAdult = $passengerList['standard_quantity_of_extra_adult'];
            }
            $adultRoomFare = $this->pricingRoomFare('标准成人');
            $itemFare = $this->pricingItemFare();
            if ($adultRoomFare){
                $this->totalFare['adult_fare_detail']['room_detail'][] = $this->roomFareDetail;
                $adultVehicleFare = $this->pricingRouteVehicleFare('标准成人');
                $this->totalFare['adult_fare_detail']['vehicle_detail'][] = $this->vehicleFareDetail;
                $this->totalFare['adult_fare'] = $this->getAdultTotalFare($adultRoomFare,$adultVehicleFare, $itemFare);
                if ($this->quantityOfExtraAdult >=1){
                    $childRoomFare = $this->pricingRoomFare('额外成人');
                    $itemFare = $this->pricingItemFare();
                    $this->totalFare['extra_adult_fare_detail']['room_detail'][] = $this->roomFareDetail;
                    $childVehicleFare = $this->pricingRouteVehicleFare('额外成人');
                    $this->totalFare['extra_adult_fare_detail']['vehicle_detail'][] = $this->vehicleFareDetail;
                    $this->totalFare['extra_adult_fare'] = $this->getChildTotalFare($childRoomFare,$childVehicleFare, $itemFare);
                }
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