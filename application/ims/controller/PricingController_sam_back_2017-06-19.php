<?php

namespace app\ims\controller;

use app\ims\model\ContractPackageModel;
use app\ims\model\ContractRoomModel;
use app\ims\model\ContractSeasonModel;
use app\ims\model\HotelDefaultVehicleModel;
use app\ims\model\HotelModel;
use app\ims\model\ExchangeModel;
use app\ims\model\HotelRoomModel;
use app\ims\model\VehicleBaseModel;
use app\ims\model\VehicleModel;
use PHPExcel;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use think\Request;

class PricingController extends PrivilegeController
{
    public $roomId;
    public $hotelId;
    public $roomData;
    public $checkInDate;
    public $stayingNights;
    public $quantityOfAdult;
    public $quantityOfChild;
    public $quantityOfExtraAdult;
    public $quantityOfRoom = 1;
    public $packageRoomData;
    public $exchangeRate;
    public $currencyUnit;
    public $packageData;
    public $vehicleFareDetail = [];
    public $exchangeData = [];
    public $error = null;
    public $totalFare = [];
    public $excelSheet;
    public $excelObj;
    public $excelName;

    public function getPackageFareByCheckInDateTest()
    {
        $hotelId = 15;
        $roomId = 10;
        $this->getPackageFareByCheckInDate($hotelId,$roomId,3,'2017-07-15');
//        halt($this->totalFare);
    }

    public function checkAllParam($params = [], $data = [], $rule = [])
    {
        if (empty($params)){
            abortError('请传入参数!');
        }
        foreach ($params as $index => $param) {
            $data[$index] = $param[0];
            $rule[$index] = $param[1];
        }
        $result = $this->validate($data,$rule);
        if ($result !== true){
            abortError($result);
        }
    }

    public function initRoomData()
    {
        $roomData = HotelRoomModel::get($this->roomId);
        $hotelModel = HotelModel::get($this->hotelId);
        $exchangeModel = ExchangeModel::get($hotelModel->exchange_id);
        $this->roomData = $roomData;
        $this->exchangeRate = $exchangeModel->exchange_rate;
        $this->currencyUnit = $exchangeModel->currency_unit;
        $this->exchangeData[$this->currencyUnit] = $this->exchangeRate;
        $this->quantityOfAdult = $roomData->standard_adult;
        $this->quantityOfChild = $roomData->extra_child;
        $this->quantityOfExtraAdult = $roomData->extra_adult;
    }

    public function getPackageFareByCheckInDate($hotelId, $roomId, $stayingNights, $checkInDate)
    {
        $this->hotelId = $hotelId;
        $this->roomId = $roomId;
        $this->stayingNights = $stayingNights;
        $this->checkInDate = $checkInDate;
        $params['room_id']= [$hotelId,'require|integer|>:0'];
        $params['hotel_id']= [$roomId,'require|integer|>:0'];
        $params['stay_in_nights']= [$stayingNights,'require|integer|>:0'];
        $params['check_in_date']= [$checkInDate,'require|date'];
        $this->checkAllParam($params);
        $this->initRoomData();
        $plainAdultRoomFare = $plainAdultVehicleFare = 0;
        $plainChildRoomFare = $plainChildVehicleFare = 0;
        $adultRoomFare = $this->pricingRoomFare('标准成人');
        $plainAdultRoomFare += $adultRoomFare['room_fare'];
        $this->totalFare['adult_fare_detail']['room_detail'][] = $adultRoomFare['room_fare_detail'];
        $childRoomFare = $this->pricingRoomFare('额外儿童');
        $plainChildRoomFare += $childRoomFare['room_fare'];
        $this->totalFare['child_fare_detail']['room_detail'][] = $childRoomFare['room_fare_detail'];
        $adultVehicleFare = $this->pricingPackageVehicleFare('标准成人');
        $plainAdultVehicleFare = $adultVehicleFare['vehicle_fare'];
        $this->totalFare['adult_fare_detail']['vehicle_detail'] = $adultVehicleFare['vehicle_fare_detail'];
        $childVehicleFare = $this->pricingPackageVehicleFare('额外儿童');
        $plainChildVehicleFare = $childVehicleFare['vehicle_fare'];
        $this->totalFare['child_fare_detail']['vehicle_detail'] = $childVehicleFare['vehicle_fare_detail'];
        $this->totalFare['adult_fare'] = $this->getAdultTotalFare($plainAdultRoomFare,$plainAdultVehicleFare);
        $this->totalFare['child_fare'] = $this->getChildTotalFare($plainChildRoomFare,$plainChildVehicleFare);
        $this->totalFare['exchange_data'] = $this->exchangeData;
        return $this->totalFare;
    }

    public function pricingRoomFare($packageType = '标准成人')
    {
        $totalRoomFare = [];
        $seasonUniqueIdAndNightList = $this->getSeasonUniqueIdAndNightList();
        if ($seasonUniqueIdAndNightList === false){
            return false;
        }
        foreach ($seasonUniqueIdAndNightList as $seasonUniqueIdAndNight) {
            foreach ($seasonUniqueIdAndNight as $seasonOrder => $item){
                $fareDetail = '';
                $nullAmount = 1;
                $roomFare = 0;
                $roomName = $this->roomData->room_name;
                $nights = $item['nights'];
                $seasonUniqueId = $item['season_unqid'];
                $totalRoomFare['room_fare'] = 0;
                for ($i = 1; $i <= $nights; $i++) {
                    $packageName = ($i + 1) .'D' . $i.'N';
                    $extensionNight = $nights - $i;
                    $packageRoomData = $this->getPackageRoomData($packageName,$seasonUniqueId,$packageType);
                    $quantityOfPassenger = $this->quantityOfAdult;
                    if ($packageType == '额外儿童' && !$packageRoomData && isset($isFixedPackage)){
                        $quantityOfPassenger = $this->quantityOfChild;
                        $packageRoomData = $this->packageData;
                    }
                    if ($packageRoomData){
                        static $isFixedPackage = true;
                        if ($seasonOrder == 0){
                            $roomFare = $this->pricingFixedPackageRoomFare($nights, $packageType, $quantityOfPassenger, $fareDetail);
                            $totalRoomFare['room_fare_detail'][$roomName] = $fareDetail;
                        }elseif($seasonOrder == 1){
                            $roomFare = $this->pricingPackageExtensionNightRoomFare($extensionNight, $packageType, $quantityOfPassenger, $fareDetail);
                            $totalRoomFare['room_fare_detail'][$roomName] = '(' .$totalRoomFare['room_fare_detail'][$roomName] .') + (' . $fareDetail .')';
                        }
                        $totalRoomFare['room_fare'] += $roomFare;
                    }else{
                        $nullAmount++;
                    }
                    //当固定套餐不适合第一个价格季时进入基础套餐
                    if ($nullAmount == $nights && !isset($isFixedPackage)){
                        $packageName = '基础套餐';
                        $packageRoomData = $this->getPackageRoomData($packageName, $seasonUniqueId, $packageType);
                        if ($packageType == '额外儿童' && !$packageRoomData){
                            $packageRoomData = $this->packageData;
                        }
                        if ($packageRoomData){
                            $roomFare = $this->pricingBasePackageRoomFare($nights, $packageType, $quantityOfPassenger, $fareDetail);
                            if (isset($totalRoomFare['room_fare_detail'][$roomName])){
                                $totalRoomFare['room_fare_detail'][$roomName] = '(' . $totalRoomFare['room_fare_detail'][$roomName].') + (' . $fareDetail .')';
                            }
                            $totalRoomFare['room_fare_detail'][$roomName] = $fareDetail;
                            $totalRoomFare['room_fare'] += $roomFare;
                        }
                    }
                }
            }
        }
        if ($totalRoomFare['room_fare']  === 0){
            $this->error = '价格季查询失败!';
            return false;
        }
        if(empty($totalRoomFare)){
            $this->error = '价格为空';
            return false;
        }else{
            return $totalRoomFare;
        }
    }

    public function pricingFixedPackageRoomFare($extensionNight,$packageType,$packagePassengerAmount,&$fareDetail = '')
    {
        $fareDetail = '';
        $type = $this->formatPackageType($packageType);
        if ($this->packageRoomData->total_price == '房型总价') {
            $type = $type == 'adult_fare' ? 'room_price' : $type;
            $roomTotalFare = $this->packageRoomData->$type;
            $roomTotalFare = json_decode($roomTotalFare, true);
            $packageRoomFare = $roomTotalFare[0]['standard_price'];
            $roomExtensionFare =  $roomTotalFare[0]['extension_price'];
            if ($type == 'adult_fare' || $type == 'room_price'){
                $roomFare = ($packageRoomFare + $extensionNight * $roomExtensionFare) / $packagePassengerAmount;
                $fareDetail = "($packageRoomFare$this->currencyUnit + $extensionNight * $roomExtensionFare) / $packagePassengerAmount";
            }else{
                $roomFare = $packageRoomFare + $extensionNight * $roomExtensionFare;
                $fareDetail = "$packageRoomFare$this->currencyUnit + $extensionNight * $roomExtensionFare";
            }
        }else{
            $roomTotalFare = $this->packageRoomData->$type;
            $roomTotalFare = json_decode($roomTotalFare, true);
            if (isset($roomTotalFare[$packagePassengerAmount-1])){
                $roomFare = $roomTotalFare[$packagePassengerAmount-1];
            }else{
                $roomFare = $roomTotalFare[0];
            }
            $packageRoomFare = $roomFare['standard_price'];
            $roomExtensionFare = $roomFare['extension_price'];
            $roomFare = $packageRoomFare + $extensionNight * $roomExtensionFare;
            $fareDetail = "$packageRoomFare$this->currencyUnit + $extensionNight * $roomExtensionFare";
        }
        if ($type == 'extra_child_fare' && $this->packageRoomData->child_is_bed > 0){
            $roomFare += $this->packageRoomData->child_is_bed;
            $fareDetail = '('. $packageRoomFare . $this->currencyUnit . ' ) + ' . $this->packageRoomData->child_is_bed;
        }
        $roomFare = $roomFare / $this->exchangeRate;
        return $roomFare;

    }

    public function pricingPackageExtensionNightRoomFare($extensionNight,$packageType,$packagePassengerAmount, &$fareDetail = '')
    {
        $fareDetail = '';
        $type = $this->formatPackageType($packageType);
        if ($this->packageRoomData->total_price == '房型总价') {
            $type = $type == 'adult_fare' ? 'room_price' : $type;
            $roomTotalFare = $this->packageRoomData->$type;
            $roomTotalFare = json_decode($roomTotalFare, true);
            $roomExtensionFare =  $roomTotalFare[0]['extension_price'];
            $roomFare = ($extensionNight * $roomExtensionFare) / $packagePassengerAmount;
            $fareDetail = "($extensionNight * $roomExtensionFare) / $packagePassengerAmount";
        }else{
            $roomTotalFare = $this->packageRoomData->$type;
            $roomTotalFare = json_decode($roomTotalFare, true);
            $roomExtensionFare = $roomTotalFare[$packagePassengerAmount-1]['extension_price'];
            $roomFare = "$extensionNight * $roomExtensionFare";
        }
        if ($type == 'extra_child_fare' && $this->packageRoomData->child_is_bed > 0){
            $roomFare += $this->packageRoomData->child_is_bed;
            $fareDetail = '(' . $fareDetail . ')' . $extensionNight . '*' . $roomExtensionFare;
        }
        return $roomFare;

    }

    public function pricingBasePackageRoomFare($night,$packageType,$packagePassengerAmount, &$fareDetail = '')
    {
        $fareDetail = '';
        $type = $this->formatPackageType($packageType);
        if ($this->packageRoomData->total_price == '房型总价'){
            $type = $type == 'adult_fare' ? 'room_price' : $type;
            $roomFare = $this->packageRoomData->$type;
            $roomFare = json_decode($roomFare,true);
            $roomFare = $roomFare[0]['standard_price'];
            if ($type == 'adult_fare' || $type == 'room_price'){
                $totalFare = ($roomFare * $night) / $packagePassengerAmount;
                $fareDetail = "($roomFare$this->currencyUnit * $night) / $packagePassengerAmount";
            }else{
                $totalFare = $roomFare * $night;
                $fareDetail = " $roomFare$this->currencyUnit * $night ";
            }
        }else{
            $roomFare = $this->packageRoomData->$type;
            $roomFare = json_decode($roomFare,true);
            if (isset($roomFare[$packagePassengerAmount-1])){
                $roomFare = $roomFare[$packagePassengerAmount-1]['standard_price'];
            }else{
                $roomFare = $roomFare[0]['standard_price'];
            }
            $totalFare = $roomFare * $night;
            $fareDetail = "$fareDetail$this->currencyUnit * $night";
        }

        if ($type == 'extra_child_fare' && $this->packageRoomData->child_is_bed > 0){
            $totalFare += $this->packageRoomData->child_is_bed;
            $fareDetail = '(' . $fareDetail . $this->currencyUnit . ') +' . $this->packageRoomData->child_is_bed;
        }
        $totalFare = $totalFare / $this->exchangeRate;
        return $totalFare;
    }

    public function pricingPackageVehicleFare($packageType)
    {
        $vehicleFare = 0;
        if ($this->packageData->package_type == $packageType){
            $includeGoVehicle = json_decode($this->packageData->include_go_vehicle,true);
            $includeBackVehicle = json_decode($this->packageData->include_back_vehicle, true);
        }else{
            $includeGoVehicle = [];
            $includeBackVehicle = [];
        }
        if (!empty($includeGoVehicle) && !empty($includeBackVehicle)){
            $this->vehicleFareDetail = '包含';
        }else{
            $defaultVehicleModel = new HotelDefaultVehicleModel();
            $defaultVehicleModel = $defaultVehicleModel->where('hotel_id',$this->hotelId)->find();
            if ($defaultVehicleModel){
                $defaultGoVehicle = json_decode($defaultVehicleModel->default_go_vehicle,true);
                if ($defaultGoVehicle){
                    foreach ($defaultGoVehicle as $item) {
                        $vehicleFare += $this->pricingVehicleFare($item['id'], $item['category'], $packageType);
                    }
                }
                $defaultBackVehicle = json_decode($defaultVehicleModel->default_back_vehicle,true);
                if ($defaultBackVehicle){
                    foreach ($defaultBackVehicle as $item) {
                        $vehicleFare += $this->pricingVehicleFare($item['id'], $item['category'], $packageType);
                    }
                }
            }else{
                $vehicleFare = 0;
            }
        }
        return $vehicleFare;
    }

    public function pricingVehicleFare($vehicleId, $vehicleCategory, $packageType)
    {
        $vehicleFare = 0;
        if($vehicleCategory == '单程交通'){
            $vehicleModel = VehicleModel::get($vehicleId);
            $vehicleName = $vehicleModel->vehicle_name;
            $fareData = $vehicleModel->singleBase;
        }else{
            $vehicleName = $vehicleCategory;
            $fareData = VehicleBaseModel::get($vehicleId);
        }
        if (is_null($fareData)){
            $vehicleFare = 0;
            $this->vehicleFareDetail[] = [$vehicleName=>'当前联程交通不存在!'];
            return $vehicleFare;
        }
        $currencyUnit = $fareData->currency_unit;
        if (empty($currencyUnit)){
            $exchangeRate = $this->exchangeRate;
        }else{
            $exchangeRate = $this->getExchangeData($currencyUnit);
            $this->exchangeData[$currencyUnit] = $exchangeRate;
        }
        if ($fareData->pricing_method == '单载体'){
            $standardAmount = $this->quantityOfAdult;
            $extraAdultAmount = $this->quantityOfExtraAdult;
            $extraChildAmount = $this->quantityOfChild;
            if ($packageType == '标准成人'){
                $vehicleFare = ($fareData->rental_fare) / $standardAmount;
                $this->vehicleFareDetail[] = [$vehicleName => $fareData->rental_fare . $currencyUnit . '/' . $standardAmount];
            }elseif($packageType == '额外成人'){
                $vehicleFare = $fareData->rental_fare / ($extraAdultAmount + $standardAmount);
                $this->vehicleFareDetail[] = [$vehicleName => $fareData->rental_fare . $currencyUnit . '/' . ($extraAdultAmount + $standardAmount)];
            }elseif($packageType == '额外儿童'){
                $vehicleFare = $fareData->rental_fare / ($extraChildAmount + $standardAmount);
                $this->vehicleFareDetail[] = [$vehicleName=> $fareData->rental_fare . $currencyUnit . '/' . ($extraChildAmount + $standardAmount)];
            }
        }else{
            if ($packageType == '标准成人' || $packageType == '额外成人'){
                $vehicleFare = $fareData->adult_fare;
            }else{
                $vehicleFare = $fareData->child_fare;
            }

            $this->vehicleFareDetail[] = [$vehicleName=>$vehicleFare.$currencyUnit];
        }
        $vehicleFare = $vehicleFare / $exchangeRate;
        return $vehicleFare;
    }

    //四获取价格季唯一id和入住晚数的列表
    public function getSeasonUniqueIdAndNightList()
    {
        $seasonUniqueIdList = $this->getSeasonUniqueIdListByHotelId();
        if ($seasonUniqueIdList === false){
            return false;
        }
        $seasonUniqueIdAndNightList = [];
        foreach ($seasonUniqueIdList as $index => $seasonUniqueId) {
            if (count(array_unique($seasonUniqueId)) == 1){
                $seasonUniqueIdAndNightList[$index][] = ['season_unqid'=>$seasonUniqueId[0], 'nights'=>count($seasonUniqueId)];
            }else{
                $night = 1;
                foreach ($seasonUniqueId as $key => $value) {
                    if (isset($seasonUniqueId[$key +1])){
                        if ($value != $seasonUniqueId[$key +1]){
                            $seasonUniqueIdAndNightList[$index][] = ['season_unqid'=>$value, 'nights'=>$night];
                            $night = 1;
                        }else{
                            $night++;
                        }
                    }
                }
                if (isset($value)){
                    $seasonUniqueIdAndNightList[$index][] = ['season_unqid'=>$value, 'nights'=>$night];
                }
                if(count($seasonUniqueIdAndNightList[$index]) > 2){
                    $this->error = '跨了3个价格季,无法处理';
                    return false;
                }
            }
        }
        return $seasonUniqueIdAndNightList;

    }

    //一根据酒店id获取价格季唯一id列表
    public function getSeasonUniqueIdListByHotelId()
    {
        $seasonUniqueIdList = $seasonUniqueId = [];
        $contractModel = new ContractPackageModel();
        $contractIdArray = $contractModel->getGroupColumn('contract_id',['hotel_id'=>$this->hotelId]);
        $everyItineraryDate = $this->getEveryItineraryDateByCheckInDate();
        foreach ($contractIdArray as $index => $item) {
            foreach ($everyItineraryDate as $date) {
                $result = $this->checkEveryItineraryDateByContractId($item,$date);
                if ($result){
                    $seasonUniqueId[] = $result;
                }
            }
            if ($seasonUniqueId){
                $seasonUniqueIdList[] = $seasonUniqueId;
                $seasonUniqueId = [];
            }
        }
        if (!$seasonUniqueIdList){
            $this->error = '当前酒店不存在价格季!';
            return false;
        }
        return $seasonUniqueIdList;
    }

    //二根据入住日期获取每一个线路日期
    public function getEveryItineraryDateByCheckInDate()
    {
        $everyItineraryDate = [];
        $checkInDate =  strtotime($this->checkInDate);
        for ($i = 0; $i < $this->stayingNights;$i++){
            $everyItineraryDate[] = $checkInDate + $i * 3600*24;
        }
        return $everyItineraryDate;
    }

    //三通过合同id检查每一个线路日期,合格则获取其价格季id
    public function checkEveryItineraryDateByContractId($contractId,$itineraryDate)
    {
        $contractSeasonModel = new ContractSeasonModel();
        $seasonData = $contractSeasonModel->where('contract_id',$contractId)->select();
        if($seasonData){
            foreach ($seasonData as $item) {
                $startDate = strtotime($item->season_start_date);
                $endDate = strtotime($item->season_end_date);
                if ($item->date_type == '所有日期'){
                    if ($itineraryDate >= $startDate && $itineraryDate <= $endDate){
                        return $item->season_unqid;
                    }
                }elseif($item->date_type == '某几天'){
                    $startDate = strtotime($item->someday_start);
                    $endDate = strtotime($item->someday_end);
                    if ($itineraryDate >= $startDate && $itineraryDate <= $endDate){
                        return $item->season_unqid;
                    }
                }elseif($item->date_type == '周末'){
                    $result = $this->checkDateByWeekendType($startDate,$endDate,$itineraryDate);
                    if ($result){
                        return $item->season_unqid;
                    }
                }elseif($item->date_type == '工作日'){
                    $result = $this->checkDateByWeekDayType($startDate,$endDate,$itineraryDate);
                    if ($result){
                        return $item->season_unqid;
                    }
                }
            }
        }
        return false;
    }

    public function checkDateByWeekendType($startDate, $endDate,$date)
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

    public function checkDateByWeekDayType($startDate, $endDate,$date)
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

    public function getPackageRoomData($packageName,$seasonUniqid,$packageType)
    {
        $contractPackageModel = new ContractPackageModel();
        $contractRoomModel = new ContractRoomModel();
        $where['package_name'] = $packageName;
        $where['season_unqid'] = $seasonUniqid;
        $where['package_type'] = $packageType;
        $packageData = $contractPackageModel->where($where)->find();
        if ($packageData){
            $this->packageData = $packageData;
            $packageRoomData = $contractRoomModel
                ->where('room_id',$this->roomId)
                ->where('package_unqid',$packageData->package_unqid)
                ->find();
            $this->packageRoomData = $packageRoomData;

        }
        return isset($packageRoomData) ? $packageRoomData : false;
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

    public function getExchangeData($currencyUnit)
    {
        $exchangeModel = new ExchangeModel();
        $exchangeModel = $exchangeModel->where('currency_unit',$currencyUnit)->find();
        $exchangeRate = $exchangeModel->exchange_rate;
        return $exchangeRate;
    }
}
