<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-06-21
 * Time: 16:57
 */

namespace app\ims\controller;

use app\ims\model\ContractItemModel;
use app\ims\model\ContractPackageModel;
use app\ims\model\ContractRoomModel;
use app\ims\model\ContractSeason;
use app\ims\model\ContractSeasonModel;
use app\ims\model\VehicleBaseModel;
use app\ims\model\VehicleModel;
use app\ims\model\ExchangeModel;
use app\ims\model\HotelRoomModel;
use app\ims\model\HotelModel;


class BasePricingController extends BaseController
{
    public $roomId;
    public $checkInDate;
    public $stayingNights;
    public $extensionNight;
    public $roomData;
    public $roomName;
    public $hotelId;

    public $packageType = '标准成人';
    public $packageName;
    public $packageData;
    public $packageRoomData;
    public $adultPackageData;
    public $extensionFare;

    public $exchangeRate;
    public $currencyUnit;
    public $exchangeData;
    public $quantityOfAdult;
    public $quantityOfChild;
    public $quantityOfExtraAdult;
    public $quantityOfPassenger;

    public $vehicleFareDetail;
    public $roomFareDetail;
    public $itemFareDetail;

    public $contractId;
    public $seasonUniqueId;
    public $seasonUniqueIdSet = [];

    public $error = null;
    public $isFixedPackage = null;
    public $totalFare;

    public function initRoomData()
    {
        $roomData = HotelRoomModel::get($this->roomId);
        $this->roomData = $roomData;
        $this->roomName = $roomData->room_name;
        $this->hotelId = $roomData->hotel_id;
        $hotelModel = HotelModel::get($this->hotelId);
        $exchangeModel = ExchangeModel::get($hotelModel->exchange_id);
        $this->exchangeRate = $exchangeModel->exchange_rate;
        $this->currencyUnit = $exchangeModel->currency_unit;
        $this->exchangeData[$this->currencyUnit] = $this->exchangeRate;
        $this->quantityOfAdult = $roomData->standard_adult;
        $this->quantityOfChild = $roomData->extra_child;
        $this->quantityOfExtraAdult = $roomData->extra_adult;
    }

    public function getAdultTotalFare($roomFare,$vehicleFare, $itemFare)
    {
        $insurance = 155;
        $bigGift = 60;
        $cost = $roomFare + $vehicleFare + $itemFare;
        $profit = $this->getProfitByCost($cost);
        $totalFare = $cost / $profit + $insurance + $bigGift;
        $totalFare *= 1.04;
//        $totalFare = intval($totalFare);
        $totalFare = round($totalFare, -1);
        return $totalFare;
    }

    public function getChildTotalFare($roomFare,$vehicleFare, $itemFare)
    {
        $insurance = 150;
        $cost = $roomFare + $vehicleFare + $itemFare;
        $profit = 0.7;
//        $profit = $this->getProfitByCost($cost);
        if ($roomFare == 0 && $vehicleFare == 0){
            $totalFare = 150;
        }else{
            $totalFare = $cost / $profit + $insurance;
            $totalFare *= 1.04;
            $totalFare = round($totalFare, -1);
        }
        return $totalFare;
    }

    public function getExtraAdultTotalFare($roomFare,$vehicleFare, $itemFare)
    {
        $insurance = 155;
        $bigGift = 60;
        $cost = $roomFare + $vehicleFare + $itemFare;
        $profit = 0.7;
//        $profit = $this->getProfitByCost($cost);
        $totalFare = $cost / $profit + $insurance + $bigGift;
        $totalFare *= 1.04;
//        $totalFare = intval($totalFare);
        $totalFare = round($totalFare, -1);
        return $totalFare;
    }

    public function getProfitByCost($cost)
    {
        if ($cost > 0 && $cost <= 3999){
            $profit = 0.65;
        }elseif($cost > 3999 && $cost <= 6999){
            $profit = 0.68;
        }elseif($cost > 6999 && $cost <= 9999){
            $profit = 0.72;
        }elseif($cost > 9999 && $cost <= 14999){
            $profit = 0.75;
        }elseif($cost > 14999 && $cost <= 22999){
            $profit = 0.78;
        }else{
            $profit = 0.80;//$cost > 22999 && $cost <= 30000
        }
        return $profit;
    }

    public function pricingItemFare()
    {
        $contractModel = new ContractItemModel();
        $contractItemData = $contractModel->where('contract_id',$this->contractId)
           ->where('item_type','强制收费')
           ->select();
        $itemFare = 0;
        $type = 'adult_fare';
        if ($this->packageType == '额外儿童'){
            $type = 'kids_fare';
        }
        foreach ($contractItemData as $index => $contractItemDatum) {
            if ($contractItemData['fare_type'] == '按次收费'){
                $itemFare = $itemFare + $contractItemDatum[$type];
                $fareDetail = $itemFare . $this->currencyUnit;
            }else{
                $itemFare = $contractItemDatum[$type] * $this->stayingNights;
                $fareDetail = $itemFare . $this->currencyUnit . "*" . $this->stayingNights;
            }
            $this->itemFareDetail[$contractItemDatum['item_name']] = $fareDetail;
        }
        $itemFare = $itemFare / $this->exchangeRate;
        return $itemFare;
    }

    public function pricingRoomFare($packageType = '标准成人')
    {
        $seasonUniqueIdSet = $this->getSeasonUniqueIdSet();
        if (!$seasonUniqueIdSet){
            $this->error = '当前酒店不存在价格季';
            return false;
        }
        $totalRoomFare = 0;
        $fixedRoomFareList = $fixedPackageDataList = $fixedPackageRoomFareDetail =[];
        foreach ($seasonUniqueIdSet as $seasonUniqueIdList) {
            $uniqueSeasonUniqueIdList = array_unique($seasonUniqueIdList);
            foreach ($uniqueSeasonUniqueIdList as $uniqueSeasonUniqueId) {
                $this->roomFareDetail = [];
                if ($packageType == '标准成人'){
                    $this->isFixedPackage = null;
                    $this->packageRoomData = [];
                    $this->packageData = [];
                }
                $this->seasonUniqueId = $uniqueSeasonUniqueId;
                $this->packageType = $packageType;
                $fixedRoomFare = $this->pricingFixedPackageRoomFare();
                if ($fixedRoomFare !== false){
                    $fixedRoomFareList[] = $fixedRoomFare;
                    $fixedPackageDataList[] = $this->packageData;
                    $fixedPackageRoomFareDetail[] = $this->roomFareDetail;
                }
                if ($packageType == '额外儿童' && is_null($this->isFixedPackage)){
                    $this->isFixedPackage = false;
                }

            }
        }
        if (count($fixedRoomFareList) >= 1 && count($fixedPackageDataList) >=1 && (is_null($this->isFixedPackage) || $this->isFixedPackage)){
            $this->isFixedPackage = true;
            $totalRoomFare = max($fixedRoomFareList);
            $key = array_search($totalRoomFare, $fixedRoomFareList);
            $this->packageData = $fixedPackageDataList[$key];
            $this->roomFareDetail = $fixedPackageRoomFareDetail[$key];
        }else{
            $baseRoomFare = 0;
            $baseRoomFareList = $basePackageDataList = $basePackageRoomFareDetail = [];
            foreach ($seasonUniqueIdSet as $seasonUniqueIdList) {
                $seasonUniqueIdAndNightList = array_count_values($seasonUniqueIdList);
                foreach ($seasonUniqueIdAndNightList as $seasonUniqueId => $nights) {
                    $this->roomFareDetail = [];
                    if ($packageType == '标准成人'){
                        $this->packageRoomData = [];
                        $this->packageData = [];
                    }
                    $this->seasonUniqueId = $seasonUniqueId;
                    $this->packageType = $packageType;
                    $this->stayingNights = $nights;
                    $baseRoomFare += $this->pricingBasePackageRoomFare();
                }
                if ($baseRoomFare){
                    $baseRoomFareList[] = $baseRoomFare;
                    $basePackageDataList[] = $this->packageData;
                    $basePackageRoomFareDetail[] = $this->roomFareDetail;
                    $baseRoomFare = 0;
                }
            }
            if (count($baseRoomFareList) >= 1 && count($basePackageDataList) >= 1){
                $totalRoomFare = max($baseRoomFareList);
                $key = array_search($totalRoomFare, $baseRoomFareList);
                $this->packageData = $basePackageDataList[$key];
                $this->roomFareDetail = $basePackageRoomFareDetail[$key];
            }
        }
        return $totalRoomFare;

    }

    public function pricingFixedPackageRoomFare()
    {
        $fareDetail = '';
        $type = $this->formatPackageType();
        for ($i = 1; $i <= $this->stayingNights; $i++) {
            $this->packageName = ($i + 1) .'D' . $i.'N';
            $this->extensionNight = $this->stayingNights - $i;
            $this->getPackageRoomData();
        }
        if ($this->packageRoomData){
            if ($this->packageRoomData->total_price == '房型总价') {
                $type = $type == 'adult_fare' ? 'room_price' : $type;
                $roomFare = $this->packageRoomData->$type;
                $roomFare = json_decode($roomFare, true);
                $packageRoomFare = (int)$roomFare[0]['standard_price'];
                $roomExtensionFare =  (int)$roomFare[0]['extension_price'] / $this->quantityOfPassenger;
                $this->extensionFare = $roomExtensionFare;
                $roomFare = ((int)$packageRoomFare +  $this->extensionNight * (int)$roomExtensionFare) / $this->quantityOfPassenger;
                if ($roomFare !== 0){
                    if ($this->extensionNight > 1){
                        $fareDetail = $packageRoomFare . $this->currencyUnit . "+  $this->extensionNight * $roomExtensionFare";
                        if ( $this->quantityOfPassenger > 1){
                            $fareDetail = "(" . $fareDetail . ") /" . $this->quantityOfPassenger;
                        }
                    }else{
                        $fareDetail = $packageRoomFare . $this->currencyUnit;
                        if ( $this->quantityOfPassenger > 1){
                            $fareDetail = $fareDetail . "/" . $this->quantityOfPassenger;
                        }
                    }
                }

            }else{
                $roomFare = $this->packageRoomData->$type;
                $roomFare = json_decode($roomFare, true);
                if (count($roomFare) == 0) {
                    $roomFare = 0;
                }else{
                    if (isset($roomFare[$this->quantityOfPassenger - 1])){
                        $roomFare = $roomFare[$this->quantityOfPassenger - 1];
                    }else{
                        $roomFare = $roomFare[0];
                    }
                }

                $packageRoomFare = $roomFare['standard_price'];
                $roomExtensionFare = $roomFare['extension_price'] / $this->quantityOfPassenger;
                $extensionNightDetail =  $this->extensionNight > 1 ? '' : "+  $this->extensionNight * $roomExtensionFare";
                $this->extensionFare = $roomExtensionFare;
                $roomFare = $packageRoomFare +  $this->extensionNight * $roomExtensionFare;
                $fareDetail = $packageRoomFare . $this->currencyUnit . $extensionNightDetail;
            }
//            if ($type == 'extra_child_fare' && $this->packageRoomData->child_is_bed > 0){
//                $extraBedFare = $this->packageRoomData->child_is_bed;
//            }
//            if (isset($extraBedFare)){
//                if ($roomFare === 0 ){
//                    $fareDetail = '无需房费,但需加床费:'. $extraBedFare . $this->currencyUnit;
//                }
//                if ($roomFare !== 0 ){
//                    $fareDetail = '房费:' . $fareDetail . ' + 加床费:' . $extraBedFare . $this->currencyUnit;
//                }
//                $roomFare += $extraBedFare;
//            }
//            if ($type == 'extra_child_fare' && $roomFare === 0 ){
//                $fareDetail = '无需房费';
//            }
            $roomFare = $roomFare / $this->exchangeRate;
            $this->roomFareDetail[$this->roomName] = $fareDetail;
            return $roomFare;
        }
        $this->error = '套餐不存在!';
        return false;
    }

    public function pricingBasePackageRoomFare()
    {
        $roomFare = 0;
        $this->packageName = '基础套餐';
        $type = $this->formatPackageType();
        $this->getPackageRoomData();
        if ($this->packageRoomData){
            if ($this->packageRoomData->total_price == '房型总价') {
                $type = $type == 'adult_fare' ? 'room_price' : $type;
                $roomFare = $this->packageRoomData->$type;
                $roomFare = json_decode($roomFare, true);
                $packageRoomFare = $roomFare[0]['standard_price'];
                $this->extensionFare = (int)$packageRoomFare / $this->quantityOfPassenger;
                $roomFare = ((int)$packageRoomFare * $this->stayingNights) / (int)$this->quantityOfPassenger;
                $fareDetail = $packageRoomFare . "*" . $this->stayingNights;
                if ( $this->quantityOfPassenger > 1){
                    $fareDetail = $fareDetail . "/" . $this->quantityOfPassenger;
                }
            }else{
                $roomFare = $this->packageRoomData->$type;
                $roomFare = json_decode($roomFare,true);
                if (count($roomFare) === 0) {
                    $packageRoomFare = 0;
                }else{
                    if (isset($roomFare[$this->quantityOfPassenger-1])){
                        $packageRoomFare = $roomFare[$this->quantityOfPassenger-1]['standard_price'];
                    }else{
                        $packageRoomFare = $roomFare[0]['standard_price'];
                    }
                }
                $this->extensionFare = $packageRoomFare / $this->quantityOfPassenger;
                $roomFare = $packageRoomFare * $this->stayingNights;
                $fareDetail = $packageRoomFare . $this->currencyUnit . "*" . $this->stayingNights;
            }
//            if ($type == 'extra_child_fare' && $this->packageRoomData->child_is_bed > 0){
//                $roomFare += $this->packageRoomData->child_is_bed;
//                $fareDetail = $fareDetail . ' + ' . $this->packageRoomData->child_is_bed . $this->currencyUnit;
//            }
            if (isset($this->roomFareDetail[$this->roomName])){
                $this->roomFareDetail[$this->roomName] = $this->roomFareDetail[$this->roomName] . "+ $fareDetail";
            }else{
                $this->roomFareDetail[$this->roomName] = $fareDetail;
            }
            $roomFare = $roomFare / $this->exchangeRate;
        }
        return $roomFare;
    }

    public function pricingVehicleFare($vehicleId, $vehicleCategory, $itineraryType, $packageType)
    {
        $vehicleFare = 0;
        $itineraryType = $this->formatItineraryType($itineraryType);
        if($vehicleCategory == '单程交通'){
            $vehicleModel = VehicleModel::get($vehicleId);
            if (is_null($vehicleModel)){
                $vehicleFare = 0;
                $this->vehicleFareDetail[$itineraryType][] = [
                    '单程交通'=>'当前交通不存在!'
                ];
                return $vehicleFare;
            }
            $vehicleName = $vehicleModel->vehicle_name;
            $fareData = $vehicleModel->singleBase;
        }else{
            $vehicleName = $vehicleCategory;
            $vehicleModel = new VehicleModel();
            $fareData = VehicleBaseModel::get($vehicleId);
            $data = $vehicleModel->where('vehicle_base_id', $vehicleId)->select();
            if (is_null($fareData)){
                $vehicleFare = 0;
                $this->vehicleFareDetail[$itineraryType][] = [
                    $vehicleName=>'当前交通不存在!'
                ];
                return $vehicleFare;
            }
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
                $this->vehicleFareDetail[$itineraryType][] = [$vehicleName => $fareData->rental_fare . $currencyUnit . '/' . $standardAmount];
            }elseif($packageType == '额外成人'){
                $vehicleFare = $fareData->rental_fare / ($extraAdultAmount + $standardAmount);
                $this->vehicleFareDetail[$itineraryType][] = [$vehicleName => $fareData->rental_fare . $currencyUnit . '/' . ($extraAdultAmount + $standardAmount)];
            }elseif($packageType == '额外儿童'){
                $vehicleFare = $fareData->rental_fare / ($extraChildAmount + $standardAmount);
                $this->vehicleFareDetail[$itineraryType][] = [$vehicleName=> $fareData->rental_fare . $currencyUnit . '/' . ($extraChildAmount + $standardAmount)];
            }
        }else{
            if ($packageType == '标准成人' || $packageType == '额外成人'){
                $vehicleFare = $fareData->adult_fare;
            }else{
                $vehicleFare = $fareData->child_fare;
            }
            $this->vehicleFareDetail[$itineraryType][] = [$vehicleName=>$vehicleFare.$currencyUnit];
        }
        $vehicleFare = $vehicleFare / $exchangeRate;
        return $vehicleFare;
    }

    public function getSeasonUniqueIdSet()
    {
        $contractModel = new ContractPackageModel();
        $contractIdList = $contractModel->getGroupColumn('contract_id',['hotel_id'=>$this->hotelId]);
        $everyItineraryDate = $this->getEveryItineraryDateByCheckInDate();
        $contractSeasonModel = new ContractSeasonModel();
        $seasonData = $contractSeasonModel->where('contract_id','IN',$contractIdList)->where('status',1)->select();
        $seasonUniqueIdSet = $seasonUniqueIdList = [];
        if($seasonData){
            foreach ($seasonData as $item) {
                foreach ($everyItineraryDate as $index => $date) {
                    $startDate = strtotime($item->season_start_date);
                    $endDate = strtotime($item->season_end_date);
                    if ($item->date_type == '所有日期'){
                        if ($date >= $startDate && $date <= $endDate){
                            $seasonUniqueIdList[$index] =  $item->season_unqid;
                        }
                    }elseif ($item->date_type == '某几天'){
                        $startDate = strtotime($item->someday_start);
                        $endDate = strtotime($item->someday_end);
                        if ($date >= $startDate && $date <= $endDate){
                            $seasonUniqueIdList[$index] = $item->season_unqid;
                        }
                    }elseif ($item->date_type == '周末'){
                        $result = $this->checkDateByWeekendType($startDate,$endDate);
                        if (in_array($date,$result)){
                            $seasonUniqueIdList[$index] = $item->season_unqid;
                        }
                    }elseif ($item->date_type == '工作日'){
                        $result = $this->checkDateByWeekdayType($startDate,$endDate);
                        if (in_array($date,$result)){
                            $seasonUniqueIdList[$index] = $item->season_unqid;
                        }
                    }
                }
                if ($seasonUniqueIdList && count($seasonUniqueIdList) == $this->stayingNights){
                    $seasonUniqueIdSet[] = $seasonUniqueIdList;
                    $seasonUniqueIdList = [];
                }
            }
        }
        return $seasonUniqueIdSet;
    }

    public function getEveryItineraryDateByCheckInDate()
    {
        $everyItineraryDate = [];
        $checkInDate =  strtotime($this->checkInDate);
        for ($i = 0; $i < $this->stayingNights;$i++){
            $everyItineraryDate[] = $checkInDate + $i * 3600*24;
        }
        return $everyItineraryDate;
    }

    public function checkDateByWeekendType($startDate, $endDate,$weekendList = [])
    {
        while ($startDate <= $endDate) {
            $week = date('w',$startDate);
            if ($week == 0 || $week == 6) {
                $weekendList[] = $startDate;
            }
            $startDate += 3600*24;
        }
        return $weekendList;
    }

    public function checkDateByWeekdayType($startDate, $endDate, $weekdayList = [])
    {
        while ($startDate <= $endDate) {
            $week = date('w',$startDate);
            if ($week != 0 && $week != 6) {
                $weekdayList[] = $startDate;
            }
            $startDate += 3600*24;
        }
        return $weekdayList;
    }

    public function getPackageRoomData()
    {
        $contractPackageModel = new ContractPackageModel();
        $contractRoomModel = new ContractRoomModel();
        $where['package_name'] = $this->packageName;
        $where['season_unqid'] = $this->seasonUniqueId;
        $where['package_type'] = $this->packageType;
        $packageData = $contractPackageModel->where($where)->find();
        if ($packageData){
            if ($this->packageType == '标准成人') {
                $this->adultPackageData = $packageData;
            };
            $packageRoomData = $contractRoomModel
                ->where('room_id',$this->roomId)
                ->where('package_unqid',$packageData->package_unqid)
                ->find();
            $this->packageData = $packageData;
            $this->packageRoomData = $packageRoomData;
            $this->contractId = $packageData->contract_id;
        }
    }

    public function formatPackageType()
    {
        $packageType = $this->packageType;
        if ($packageType == '标准成人'){
            $type = 'adult_fare';
            $this->quantityOfPassenger = $this->quantityOfAdult;
        }elseif($packageType == '额外成人'){
            $type = 'extra_adult_fare';
//            $this->quantityOfPassenger = $this->quantityOfExtraAdult;
            $this->quantityOfPassenger = 1;
        }else{
            $type = 'extra_child_fare';
//            $this->quantityOfPassenger = $this->quantityOfChild;
            $this->quantityOfPassenger = 1;
        }
        return $type;
    }

    public function formatItineraryType($itineraryType)
    {
        if ($itineraryType == '去程'){
            $itineraryType = 'go';
        }else{
            $itineraryType = 'back';
        }
        return $itineraryType;
    }

    public function getExchangeData($currencyUnit)
    {
        $exchangeModel = new ExchangeModel();
        $exchangeModel = $exchangeModel->where('currency_unit',$currencyUnit)->find();
        $exchangeRate = $exchangeModel->exchange_rate;
        return $exchangeRate;
    }
}