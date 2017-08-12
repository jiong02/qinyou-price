<?php

namespace app\ims\controller;

use app\ims\model\ContractModel;
use app\ims\model\ContractPackageModel;
use app\ims\model\ContractRoomFormatModel;
use app\ims\model\ContractRoomModel;
use app\ims\model\ContractSeasonModel;
use app\ims\model\HotelDefaultVehicleModel;
use app\ims\model\HotelRoomModel;
use app\ims\model\VehicleBaseModel;
use app\ims\model\VehicleModel;
use think\Validate;

class PricingController extends PrivilegeController
{
    public $roomId;
    public $hotelId;
    public $roomData;
    public $packageData;
    public $itineraryDays;
    public $departureDate;

    public function testPricing($roomId,$date,$day)
    {
/*        $roomId = 32;
        $date = '2017-05-18';
        $day = '4';*/
/*        $roomId = 32;
        $departureDate = '2017-05-12';
        $itineraryDays = '4';*/
        $data = $this->pricing($roomId,$date,$day);
        return $data;
    }

    public function test()
    {
        $roomId = 12;
        $departureDate = '2017-05-20';
        $itineraryDays = '4';
        $data = $this->pricing($roomId,$departureDate,$itineraryDays);
        halt($data);
    }

    public function pricing($roomId,$departureDate,$itineraryDays)
    {
        $this->getInputPricingData($roomId,$departureDate,$itineraryDays);
        $seasonIdArray = $this->checkDepartureDate();
        $seasonIdAndDayArray = $this->getSeasonIdAndDay($seasonIdArray);
        $totalFare = [];
        foreach ($seasonIdAndDayArray as $k => $v) {
            foreach ($v as $index => $item) {
                $nullAmount = 1;
                for ($i=2; $i <= $item['day']; $i++) {
                    $packageName = $i.'D'.($i-1).'N';
                    $seasonModel = ContractSeasonModel::get($item['season_id']);
                    $seasonUniqid = $seasonModel->season_unqid;
                    $dayOrder = $item['day'];
                    if (!isset($v[$index+1])){
                        $dayOrder = ($item['day'] - 1);
                    }
                    //固定套餐
                    $roomData = $this->roomData;
                    $standardAmount = $roomData->standard_adult;
                    $extraAdultAmount = $roomData->extra_adult;
                    $extraChildAmount = $roomData->extra_child;
                    $packageType = '标准成人';
                    $fixedPackageRoomData = $this->getPackageRoomData($packageName,$seasonUniqid,$packageType);
                    if ($fixedPackageRoomData){
                        $conRoomInfo = $this->formateData($this->packageData);
                        $totalFare['fixed']['package_unqid'] = $conRoomInfo['package_unqid'];
                        $fixedPackageAdultRoomFare = $this->getFixedPackageRoomFare($fixedPackageRoomData,$item['day'] - $i,$packageType,$standardAmount);
                        $fixedPackageAdultVehicleFare = $this->getPackageVehicleFare($packageType);
                        $totalFare['fixed']['adult_fare']['room_fare'] = $fixedPackageAdultRoomFare;
                        if ($fixedPackageAdultVehicleFare != 0){
                            $totalFare['fixed']['adult_fare']['vehicle_fare'] = $fixedPackageAdultVehicleFare;
                        }
                        if ($extraAdultAmount >= 1){
                            $packageType = '额外成人';
                            $fixedPackageRoomData = $this->getPackageRoomData($packageName,$seasonUniqid,$packageType);
                            if ($fixedPackageRoomData){
                                $conRoomInfo = $this->formateData($this->packageData);
                                $totalFare['fixed']['package_unqid'] = $conRoomInfo['package_unqid'];
                                $fixedPackageExtraAdultRoomFare = $this->getFixedPackageRoomFare($fixedPackageRoomData,$item['day'] - $i,$packageType,$extraAdultAmount);
                                $fixedPackageExtraAdultVehicleFare = $this->getPackageVehicleFare($packageType);
                                $totalFare['fixed']['extra_adult_fare']['room_fare'] = $fixedPackageExtraAdultRoomFare;
                                if ($fixedPackageExtraAdultVehicleFare != 0){
                                    $totalFare['fixed']['extra_adult_fare']['vehicle_fare'] = $fixedPackageExtraAdultVehicleFare;
                                }

                            }
                        }
                        if ($extraChildAmount >= 1){
                            $packageType = '额外儿童';
                            $fixedPackageRoomData = $this->getPackageRoomData($packageName,$seasonUniqid,$packageType);
                            if ($fixedPackageRoomData){
                                $conRoomInfo = $this->formateData($this->packageData);
                                $totalFare['fixed']['package_unqid'] = $conRoomInfo['package_unqid'];
                                $fixedPackageExtraAdultRoomFare = $this->getFixedPackageRoomFare($fixedPackageRoomData,$item['day'] - $i,$packageType,$extraChildAmount);
                                $fixedPackageExtraChildVehicleFare = $this->getPackageVehicleFare($packageType);
                                $totalFare['fixed']['extra_child_fare']['room_fare'] = $fixedPackageExtraAdultRoomFare;
                                if ($fixedPackageExtraChildVehicleFare != 0) {
                                    $totalFare['fixed']['extra_child_fare']['vehicle_fare'] = $fixedPackageExtraChildVehicleFare;
                                }
                            }
                        }
                    }else{
                        $nullAmount++;
                    }
                    if ($nullAmount == $item['day']){
                        $packageType = '标准成人';
                        $packageName = '基础套餐';
                        $basePackageRoomData = $this->getPackageRoomData($packageName,$seasonUniqid,$packageType);
                        if ($basePackageRoomData){
                            $conRoomInfo = $this->formateData($this->packageData);
                            $totalFare['base']['package_unqid'] = $conRoomInfo['package_unqid'];
                            $basePackageAdultRoomFare = $this->getBasePackageRoomData($basePackageRoomData,$item['day'] - 1,$packageType,$standardAmount);
                            $fixedPackageExtraChildVehicleFare = $this->getPackageVehicleFare($packageType);
                            $totalFare['base']['adult_fare']['room_fare'] = $basePackageAdultRoomFare;
                            if ($fixedPackageExtraChildVehicleFare != 0){
                                $totalFare['base']['adult_fare']['vehicle_fare'] = $fixedPackageExtraChildVehicleFare;
                            }
                            if ($extraAdultAmount >= 1){
                                $packageType = '额外成人';
                                $basePackageRoomData = $this->getPackageRoomData($packageName,$seasonUniqid,$packageType);
                                if ($basePackageRoomData){
                                    $conRoomInfo = $this->formateData($this->packageData);
                                    $totalFare['base']['package_unqid'] = $conRoomInfo['package_unqid'];
                                    $basePackageExtraAdultRoomFare = $this->getBasePackageRoomData($basePackageRoomData,$item['day'] - 1,$packageType,$extraAdultAmount);
                                    $fixedPackageExtraAdultVehicleFare = $this->getPackageVehicleFare($packageType);
                                    $totalFare['base']['extra_adult_fare']['room_fare'] = $basePackageExtraAdultRoomFare;
                                    if ($fixedPackageExtraAdultVehicleFare != 0) {
                                        $totalFare['base']['extra_adult_fare']['vehicle_fare'] = $fixedPackageExtraAdultVehicleFare;
                                    }
                                }
                            }
                            if ($extraChildAmount >= 1){
                                $packageType = '额外儿童';
                                $basePackageRoomData = $this->getPackageRoomData($packageName,$seasonUniqid,$packageType);
                                if ($basePackageRoomData){
                                    $conRoomInfo = $this->formateData($this->packageData);
                                    $totalFare['base']['package_unqid'] = $conRoomInfo['package_unqid'];
                                    $basePackageExtraChildRoomFare = $this->getBasePackageRoomData($basePackageRoomData,$item['day'] - 1,$packageType,$extraChildAmount);
                                    $fixedPackageExtraChildVehicleFare = $this->getPackageVehicleFare($packageType);
                                    $totalFare['base']['extra_child_fare']['room_fare'] = $basePackageExtraChildRoomFare;
                                    if ($fixedPackageExtraChildVehicleFare != 0) {
                                        $totalFare['base']['extra_child_fare']['vehicle_fare'] = $fixedPackageExtraChildVehicleFare;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $totalFare;
    }
    public function getPackageRoomData($packageName,$seasonUniqid,$packageType)
    {
        $contractPackageModel = new ContractPackageModel();
        $contractRoomModel = new ContractRoomModel();
        $where['package_name'] = $packageName;
        $where['season_unqid'] = $seasonUniqid;
        $where['package_type'] = $packageType;
        $packageData = $contractPackageModel->where($where)->find();
        $this->packageData = $packageData;
        if ($packageData){
            $roomData = $contractRoomModel
                ->where('room_id',$this->roomId)
                ->where('package_unqid',$packageData->package_unqid)
                ->find();
        }
        return isset($roomData) ? $roomData : false;
    }

    public function getFixedPackageRoomFare($fixedPackageRoomData,$extensionNight,$packageType,$packagePassengerAmount)
    {
        $type = $this->formatPackageType($packageType);
        if ($fixedPackageRoomData->total_price == '房型总价') {
            $type = $type == 'adult_fare' ? 'room_price' : $type;
            $roomTotalFare = $fixedPackageRoomData->$type;
            if (!is_array($roomTotalFare)) {
                $roomTotalFare = json_decode($roomTotalFare, true);
            }
            $packageRoomFare = $roomTotalFare[0]['standard_price'];
            $roomExtensionFare =  $roomTotalFare[0]['extension_price'];
            $roomFare = ($packageRoomFare + $extensionNight * $roomExtensionFare) / $packagePassengerAmount;
        }else{
            $roomTotalFare = $fixedPackageRoomData->$type;
            if (!is_array($roomTotalFare)) {
                $roomTotalFare = json_decode($roomTotalFare, true);
            }
            $packageRoomFare = $roomTotalFare[$packagePassengerAmount-1]['standard_price'];
            $roomExtensionFare = $roomTotalFare[$packagePassengerAmount-1]['extension_price'];
            $roomFare = $packageRoomFare + $extensionNight * $roomExtensionFare;
        }
        if ($type == 'extra_child_fare' && $fixedPackageRoomData->child_is_bed != -1){
            $roomFare += $fixedPackageRoomData->child_is_bed;
        }
        return $roomFare;

    }

    public function getBasePackageRoomData($basePackageRoomData,$night,$packageType,$packagePassengerAmount)
    {
        $type = $this->formatPackageType($packageType);
        if ($basePackageRoomData->total_price == '房型总价'){
            $type = $type == 'adult_fare' ? 'room_price' : $type;
            $roomFare = $basePackageRoomData->$type;
            if (!is_array($roomFare)){
                $roomFare = json_decode($roomFare,true);
            }
            $roomFare = $roomFare[0]['standard_price'];
            $roomFare = ($roomFare * $night)/$packagePassengerAmount;
        }else{
            $roomFare = $basePackageRoomData->$type;
            if (!is_array($roomFare)){
                $roomFare = json_decode($roomFare,true);
            }
            $roomFare = $roomFare[$packagePassengerAmount-1]['standard_price'];
            $roomFare *= $night;
        }
        if ($type == 'extra_child_fare' && $basePackageRoomData->child_is_bed != -1){
            $roomFare += $basePackageRoomData->child_is_bed;
        }

        return $roomFare;
    }

    public function getPackageVehicleFare($packageType)
    {
        $vehicleFare = 0;
        $includeGoVehicle = json_decode($this->packageData->include_go_vehicle,true);
        $includeBackVehicle = json_decode($this->packageData->include_back_vehicle, true);
        if (!empty($includeGoVehicle) && !empty($includeBackVehicle)){
            $vehicleFare = 0;
        }else{
            $defaultVehicleModel = new HotelDefaultVehicleModel();
            $defaultVehicleModel = $defaultVehicleModel->where('hotel_id',$this->hotelId)->find();
            $defaultGoVehicle = json_decode($defaultVehicleModel->default_go_vehicle,true);
            if ($defaultGoVehicle){
                $vehicleFare += $this->getVehicleFare($defaultGoVehicle,$packageType);
            }
            $defaultBackVehicle = json_decode($defaultVehicleModel->default_back_vehicle,true);
            if ($defaultBackVehicle){
                $vehicleFare += $this->getVehicleFare($defaultBackVehicle,$packageType);
            }
        }
        return $vehicleFare;
    }

    public function getVehicleFare($vehicleData,$packageType)
    {
        $vehicleFare = 0;
//        dump($vehicleData);
        foreach ($vehicleData as $index => $vehicleDatum) {
            if($vehicleDatum['category'] == '单程交通'){
                $fareData = VehicleModel::get($vehicleDatum['id'])->singleBase;
            }else{
                $fareData = VehicleBaseModel::get($vehicleDatum['id']);
            }
            if ($fareData->pricing_method == '单载体'){
                $standardAmount = $this->roomData->standard_adult;
                $extraAdultAmount = $this->roomData->extra_adult;
                $extraChildAmount = $this->roomData->extra_child;
                if ($packageType == '标准成人'){
                    $vehicleFare += $fareData->rental_fare / $standardAmount;
                }elseif($packageType == '额外成人'){
                    $vehicleFare += $fareData->rental_fare / ($extraAdultAmount + $standardAmount);
                }elseif($packageType == '额外儿童'){
                    $vehicleFare += $fareData->rental_fare / ($extraAdultAmount + $extraChildAmount);
                }
            }else{
                if ($packageType == '标准成人' || $packageType == '额外成人'){
                    $vehicleFare += $fareData->adult_fare;
                }else{
                    $vehicleFare += $fareData->child_fare;
                }
            }
        }
        return $vehicleFare;
    }

    public function formatPackageType($packageType)
    {
        if ($packageType == '标准成人'){
            $type = 'adult_fare';
        }elseif($packageType == '额外成人'){
            $type = 'extra_adult_fare';
        }elseif($packageType == '额外儿童'){
            $type = 'extra_child_fare';
        }
        return $type;
    }

    public function getSeasonIdAndDay($seasonIdArray)
    {
        foreach ($seasonIdArray as $index => $item) {
            if (count(array_unique($item)) == 1){
                $dayArray[$index][] = ['season_id'=>$item[0], 'day'=>count($item)];
            }else{
                $day = 1;
                foreach ($item as $key => $value) {
                    if (isset($item[$key +1])){
                        if ($value != $item[$key +1]){
                            $dayArray[$index][] = ['season_id'=>$value, 'day'=>$day];
                            $day = 1;
                        }else{
                            $day++;
                        }
                    }
                }
                $dayArray[$index][] = ['season_id'=>$value, 'day'=>$day];
            }
        }
        return $dayArray;

    }

    public function getInputPricingData($roomId,$departureDate,$itineraryDays)
    {
        $data = [
            'room_id'=>$roomId,
            'departure_date'=>$departureDate,
            'itinerary_days'=>$itineraryDays,
        ];
        $rule = [
            'room_id'=>'require|integer|>:0',
            'departure_date'=>'require|date',
            'itinerary_days'=>'require|integer|>:2',
        ];
        $result = $this->validate($data,$rule);
        if ($result !== true){
            abortError($result);
        }
        if(!$roomData = HotelRoomModel::get($roomId)){
            abortError('room_id错误');
        }
        $this->roomId = $roomId;
        $this->roomData = $roomData;
        $this->hotelId = $roomData->hotel_id;
        $this->departureDate = $departureDate;
        $this->itineraryDays = $itineraryDays;
    }

    public function getAllItineraryDate()
    {
        $allDate = [];
        $date =  strtotime($this->departureDate);
        for ($i = 0; $i < $this->itineraryDays;$i++){
            $allDate[] = $date + $i * 3600*24;
        }
        return $allDate;
    }

    public function checkDepartureDate()
    {
        $seasonIdArray = [];
        $contractModel = new ContractPackageModel();
        $contractIdArray = $contractModel->getGroupColumn('contract_id',['hotel_id'=>$this->hotelId]);
        $allItineraryDate = $this->getAllItineraryDate();
        foreach ($contractIdArray as $index => $item) {
            foreach ($allItineraryDate as $date) {
                $seasonIdArray[$index][] = $this->checkContractDepartureDate($item,$date);
            }
        }
        return $seasonIdArray;
    }

    public function checkContractDepartureDate($contractId,$date)
    {

        $contractSeasonModel = new ContractSeasonModel();
        $seasonData = $contractSeasonModel->where('contract_id',$contractId)->select();
        if($seasonData){
                foreach ($seasonData as $item) {
                    $startDate = strtotime($item->season_start_date);
                    $endDate = strtotime($item->season_end_date);
                    if ($item->date_type == '所有日期'){
                        if ($date >= $startDate && $date <= $endDate){
                            return $item->id;
                        }
                    }elseif($item->date_type == '某几天'){
                        $startDate = strtotime($item->someday_start);
                        $endDate = strtotime($item->someday_end);
                        if ($date >= $startDate && $date <= $endDate){
                            return $item->id;
                        }
                    }elseif($item->date_type == '周末'){
                        $result = $this->checkInWeekend($startDate,$endDate,$date);
                        if ($result){
                            return $item->id;
                        }
                    }elseif($item->date_type == '工作日'){
                        $result = $this->checkInWeekDay($startDate,$endDate,$date);
                        if ($result){
                            return $item->id;
                        }
                    }
                }
        }
        abortError('当前酒店不存在价格季!');
    }

    public function checkSeasonData()
    {

    }

    public function checkInWeekend($startDate, $endDate,$date)
    {
        while ($startDate <= $endDate) {
            $week = date('w',$startDate);
            if ($week == 0 || $week == 6) {
                if ($date == $startDate){
                    return true;
                }
            }
            $startDate = $startDate + 3600*24;
        }
        return false;
    }

    public function checkInWeekDay($startDate, $endDate,$date)
    {
        while ($startDate <= $endDate) {
            $week = date('w',$startDate);
            if ($week != 0 || $week != 6) {
                if ($date == $startDate){
                    return true;
                }
            }
            $startDate = $startDate + 3600*24;
        }
        return false;
    }



}
