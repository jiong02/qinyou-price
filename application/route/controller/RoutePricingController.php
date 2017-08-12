<?php

namespace app\route\controller;

use app\ims\model\ContractPackageModel;
use app\ims\model\ContractRoomModel;
use app\ims\model\ContractSeasonModel;
use app\ims\model\HotelDefaultVehicleModel;
use app\ims\model\HotelModel;
use app\ims\model\HotelRoomModel;
use app\ims\model\VehicleBaseModel;
use app\ims\model\VehicleModel;
use app\ims\controller\PrivilegeController;
use app\route\model\RouteModel;
use PHPExcel;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use think\Request;

class RoutePricingController extends PrivilegeController
{
    public $roomId;
    public $hotelId;
    public $roomData;
    public $packageData;
    public $itineraryDays;
    public $departureDate;
    public $totalFare = [];
    public $excelSheet;
    public $excelObj;
    public $excelName;

    public function testPricing($roomId,$departureDate,$itineraryDays)
    {
        $data = $this->pricing($roomId,$departureDate,$itineraryDays);
        return $data;
    }


    public function pricing($roomId,$departureDate,$itineraryDays)
    {
        $totalFare = [];
        $fixedPackageAdultRoomFare = 0;
        $fixedPackageExtraAdultRoomFare = 0;
        $fixedPackageExtraChildRoomFare = 0;
        $basePackageAdultRoomFare = 0;
        $basePackageExtraAdultRoomFare = 0;
        $basePackageExtraChildRoomFare = 0;
        $this->getInputPricingData($roomId,$departureDate,$itineraryDays);
        $seasonIdArray = $this->checkDepartureDate();
        $seasonIdAndDayArray = $this->getSeasonIdAndDay($seasonIdArray);
        foreach ($seasonIdAndDayArray as $k => $v) {
            if(count($v) > 2){
                abortError('跨了3个价格季,无法处理');
            }
            foreach ($v as $index => $item){
                $nullAmount = 1;
                for ($i=2; $i <= $item['day']; $i++) {
                    //当前循环的套餐名
                    $packageName = $i.'D'.($i-1).'N';
                    $seasonModel = ContractSeasonModel::get($item['season_id']);
                    $extensionNight = $item['day'] - $i;
                    //当价格季大于一时
                    $night = $item['day'];
                    if (!isset($v[$index+1])){
                        $night --;
                    }
                    $seasonUniqid = $seasonModel->season_unqid;
                    $standardAmount =$this->roomData->standard_adult;
                    $extraAdultAmount =$this->roomData->extra_adult;
                    $extraChildAmount =$this->roomData->extra_child;
                    $packageType = '标准成人';
                    $fixedPackageRoomData = $this->getPackageRoomData($packageName,$seasonUniqid,$packageType);
                    if ($fixedPackageRoomData){
                        static $isFixedPackage = true;
                        $totalFare['fixed']['adult_fare']['package_unqid'] = $this->getPackageUniqid();
                        if ($index == 0){
                            $fixedPackageAdultRoomFare = $this->getFixedPackageRoomFare($fixedPackageRoomData,$extensionNight,$packageType,$standardAmount);
                        }elseif($index == 1){
                            //当固定套餐适合第一个价格季且有第二个价格季时
                            $fixedPackageAdultRoomFare += $this->getPackageExtensionNightRoomFare($fixedPackageRoomData,$night,$packageType,$standardAmount);
                        }
                        $totalFare['fixed']['adult_fare']['room_fare'] = $fixedPackageAdultRoomFare;
                        $fixedPackageAdultVehicleFare = $this->getPackageVehicleFare($packageType);
                        if ($fixedPackageAdultVehicleFare != 0){
                            $totalFare['fixed']['adult_fare']['vehicle_fare'] = $fixedPackageAdultVehicleFare;
                        }
                        if ($extraAdultAmount >= 1){
                            $packageType = '额外成人';
                            $fixedExtraAdultPackageRoomData = $this->getPackageRoomData($packageName,$seasonUniqid,$packageType);
                            if ($fixedExtraAdultPackageRoomData){
                                $fixedPackageRoomData = $fixedExtraAdultPackageRoomData;
                            }
                            $totalFare['fixed']['extra_adult_fare']['package_unqid'] = $this->getPackageUniqid();
                            if ($index == 0){
                                $fixedPackageExtraAdultRoomFare = $this->getFixedPackageRoomFare($fixedPackageRoomData,$extensionNight,$packageType,$standardAmount);
                                $totalFare['fixed']['extra_adult_fare']['room_fare'] = $fixedPackageExtraAdultRoomFare;
                            }elseif($index == 1){
                                //当固定套餐适合第一个价格季且有第二个价格季时
                                $fixedPackageExtraAdultRoomFare += $this->getPackageExtensionNightRoomFare($fixedPackageRoomData,$night,$packageType,$standardAmount);
                            }
                            $totalFare['fixed']['extra_adult_fare']['room_fare'] = $fixedPackageExtraAdultRoomFare;
                            $fixedPackageExtraAdultVehicleFare = $this->getPackageVehicleFare($packageType);
                            if ($fixedPackageExtraAdultVehicleFare != 0){
                                $totalFare['fixed']['extra_adult_fare']['vehicle_fare'] = $fixedPackageExtraAdultVehicleFare;
                            }

                        }
                        if ($extraChildAmount >= 1){
                            $packageType = '额外儿童';
                            $fixedExtraChildPackageRoomData = $this->getPackageRoomData($packageName,$seasonUniqid,$packageType);
                            if ($fixedExtraChildPackageRoomData){
                                $fixedPackageRoomData = $fixedExtraChildPackageRoomData;
                            }
                            $totalFare['fixed']['extra_child_fare']['package_unqid'] = $this->getPackageUniqid();
                            if ($index == 0){
                                $fixedPackageExtraChildRoomFare = $this->getFixedPackageRoomFare($fixedPackageRoomData,$extensionNight,$packageType,$extraChildAmount);
                            }elseif($index == 1){
                                //当固定套餐适合第一个价格季且有第二个价格季时
                                $fixedPackageExtraChildRoomFare += $this->getPackageExtensionNightRoomFare($fixedPackageRoomData,$night,$packageType,$extraChildAmount);
                            }
                            $totalFare['fixed']['extra_child_fare']['room_fare'] = $fixedPackageExtraChildRoomFare;
                            $fixedPackageExtraChildVehicleFare = $this->getPackageVehicleFare($packageType);
                            if ($fixedPackageExtraChildVehicleFare != 0) {
                                $totalFare['fixed']['extra_child_fare']['vehicle_fare'] = $fixedPackageExtraChildVehicleFare;
                            }
                        }
                    }else{
                        $nullAmount++;
                    }
                    //当固定套餐不适合第一个价格季时进入基础套餐
                    if ($nullAmount == $item['day'] && !isset($isFixedPackage)){
                        $packageType = '标准成人';
                        $packageName = '基础套餐';
                        $basePackageRoomData = $this->getPackageRoomData($packageName,$seasonUniqid,$packageType);
                        if ($basePackageRoomData){
                            $totalFare['base']['adult_fare']['package_unqid'] = $this->getPackageUniqid();
                            $basePackageAdultRoomFare += $this->getBasePackageRoomData($basePackageRoomData,$night,$packageType,$standardAmount);
                            $totalFare['base']['adult_fare']['room_fare'] = $basePackageAdultRoomFare;
                            $basePackageAdultVehicleFare = $this->getPackageVehicleFare($packageType);
                            if ($basePackageAdultVehicleFare != 0){
                                $totalFare['base']['adult_fare']['vehicle_fare'] = $basePackageAdultVehicleFare;
                            }
                            if ($extraAdultAmount >= 1){
                                $packageType = '额外成人';
                                $baseExtraAdultPackageRoomData = $this->getPackageRoomData($packageName,$seasonUniqid,$packageType);
                                if ($baseExtraAdultPackageRoomData){
                                    $basePackageRoomData = $baseExtraAdultPackageRoomData;
                                }
                                $totalFare['base']['extra_adult_fare']['package_unqid'] = $this->getPackageUniqid();
                                $basePackageExtraAdultRoomFare += $this->getBasePackageRoomData($basePackageRoomData,$night,$packageType,$extraAdultAmount);
                                $totalFare['base']['extra_adult_fare']['room_fare'] = $basePackageExtraAdultRoomFare;
                                $basePackageExtraAdultVehicleFare = $this->getPackageVehicleFare($packageType);
                                if ($basePackageExtraAdultVehicleFare != 0) {
                                    $totalFare['base']['extra_adult_fare']['vehicle_fare'] = $basePackageExtraAdultVehicleFare;
                                }

                            }
                            if ($extraChildAmount >= 1){
                                $packageType = '额外儿童';
                                $baseExtraChildPackageRoomData = $this->getPackageRoomData($packageName,$seasonUniqid,$packageType);
                                if ($baseExtraChildPackageRoomData){
                                    $basePackageRoomData = $baseExtraChildPackageRoomData;
                                }
                                $totalFare['base']['extra_child_fare']['package_unqid'] = $this->getPackageUniqid();
                                //基础套餐额外儿童房型价格
                                $basePackageExtraChildRoomFare += $this->getBasePackageRoomData($basePackageRoomData,$night,$packageType,$extraChildAmount);
                                $totalFare['base']['extra_child_fare']['room_fare'] = $basePackageExtraChildRoomFare;
                                //基础套餐额外儿童交通价格
                                $basePackageExtraChildVehicleFare = $this->getPackageVehicleFare($packageType);
                                if ($basePackageExtraChildVehicleFare != 0) {
                                    $totalFare['base']['extra_child_fare']['vehicle_fare'] = $basePackageExtraChildVehicleFare;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $totalFare;
    }

    public function getPackageUniqid()
    {
        $packageData = $this->packageData->toArray();
        $packageUniqid = $packageData['package_unqid'];
        return $packageUniqid;
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
            $roomData = $contractRoomModel
                ->where('room_id',$this->roomId)
                ->where('package_unqid',$packageData->package_unqid)
                ->find();
        }
        return isset($roomData) ? $roomData : false;
    }

    public function getPackageExtensionNightRoomFare($fixedPackageRoomData,$extensionNight,$packageType,$packagePassengerAmount)
    {
        $type = $this->formatPackageType($packageType);
        if ($fixedPackageRoomData->total_price == '房型总价') {
            $type = $type == 'adult_fare' ? 'room_price' : $type;
            $roomTotalFare = $fixedPackageRoomData->$type;
            $roomTotalFare = json_decode($roomTotalFare, true);
            $roomExtensionFare =  $roomTotalFare[0]['extension_price'];
            $roomFare = ($extensionNight * $roomExtensionFare) / $packagePassengerAmount;
        }else{
            $roomTotalFare = $fixedPackageRoomData->$type;
            $roomTotalFare = json_decode($roomTotalFare, true);
            $roomExtensionFare = $roomTotalFare[$packagePassengerAmount-1]['extension_price'];
            $roomFare = $extensionNight * $roomExtensionFare;
        }
        if ($type == 'extra_child_fare' && $fixedPackageRoomData->child_is_bed != -1){
            $roomFare += $fixedPackageRoomData->child_is_bed;
        }
        return $roomFare;

    }

    public function getFixedPackageRoomFare($fixedPackageRoomData,$extensionNight,$packageType,$packagePassengerAmount)
    {
        $type = $this->formatPackageType($packageType);
        if ($fixedPackageRoomData->total_price == '房型总价') {
            $type = $type == 'adult_fare' ? 'room_price' : $type;
            $roomTotalFare = $fixedPackageRoomData->$type;
            $roomTotalFare = json_decode($roomTotalFare, true);
            $packageRoomFare = $roomTotalFare[0]['standard_price'];
            $roomExtensionFare =  $roomTotalFare[0]['extension_price'];
            if ($type == 'adult_fare' || $type == 'room_price'){
                $roomFare = ($packageRoomFare + $extensionNight * $roomExtensionFare) / $packagePassengerAmount;
            }else{
                $roomFare = $packageRoomFare + $extensionNight * $roomExtensionFare;
            }
        }else{
            $roomTotalFare = $fixedPackageRoomData->$type;
            $roomTotalFare = json_decode($roomTotalFare, true);
            if (isset($roomTotalFare[$packagePassengerAmount-1])){
                $roomFare = $roomTotalFare[$packagePassengerAmount-1];
            }else{
                $roomFare = $roomTotalFare[0];
            }
            $packageRoomFare = $roomFare['standard_price'];
            $roomExtensionFare = $roomFare['extension_price'];
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
            $roomFare = json_decode($roomFare,true);
            $roomFare = $roomFare[0]['standard_price'];
            if ($type == 'adult_fare' || $type == 'room_price'){
                $roomFare = ($roomFare * $night) / $packagePassengerAmount;
            }else{
                $roomFare = $roomFare * $night;
            }
        }else{
            $roomFare = $basePackageRoomData->$type;
            $roomFare = json_decode($roomFare,true);
            if (isset($roomFare[$packagePassengerAmount-1])){
                $roomFare = $roomFare[$packagePassengerAmount-1]['standard_price'];
            }else{
                $roomFare = $roomFare[0]['standard_price'];
            }
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
            if ($defaultVehicleModel){
                $defaultGoVehicle = json_decode($defaultVehicleModel->default_go_vehicle,true);
                if ($defaultGoVehicle){
                    $vehicleFare = $this->getVehicleFare($defaultGoVehicle,$packageType);
                }
                $defaultBackVehicle = json_decode($defaultVehicleModel->default_back_vehicle,true);
                if ($defaultBackVehicle){
                    $vehicleFare += $this->getVehicleFare($defaultBackVehicle,$packageType);
                }
            }else{
                $vehicleFare = 0;
            }
        }
        return $vehicleFare;
    }

    public function getVehicleFare($vehicleData,$packageType)
    {
        $vehicleFare = 0;
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
                    $vehicleFare += $fareData->rental_fare / ($extraChildAmount + $standardAmount);
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
        $seasonIdArray = $seasonId = [];
        $contractModel = new ContractPackageModel();
        $contractIdArray = $contractModel->getGroupColumn('contract_id',['hotel_id'=>$this->hotelId]);
        $allItineraryDate = $this->getAllItineraryDate();
        foreach ($contractIdArray as $index => $item) {
            foreach ($allItineraryDate as $date) {
                $result = $this->checkContractDepartureDate($item,$date);
                if ($result){
                    $seasonId[] = $result;
                }
            }
            if ($seasonId){
                $seasonIdArray[] = $seasonId;
                $seasonId = [];
            }
        }
        if (!$seasonIdArray){
            abortError('当前酒店不存在价格季!');
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
        return false;
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

    public function getAdultTotalFare($roomFare,$vehicleFare,$exchangeRate)
    {
        $insurance = 123.75;
        $bigGift = 200;
        $roomFare = $roomFare == '无' ? 0 : $roomFare;
        $vehicleFare = $vehicleFare == '包含' ? 0 : $vehicleFare;
        $totalFare = ((($roomFare + $vehicleFare)/$exchangeRate) + $insurance + $bigGift)/ 0.7 + 1000;
        $totalFare *= 1.04;
        $totalFare = intval($totalFare);
        return $totalFare;
    }

    public function getChildTotalFare($roomFare,$vehicleFare,$exchangeRate)
    {
        $insurance = 102;
        $roomFare = $roomFare == '无' ? 0 : $roomFare;
        $vehicleFare = $vehicleFare == '包含' ? 0 : $vehicleFare;
        if ($roomFare == 0 && $vehicleFare == 0){
            $totalFare = 500;
        }else{
            $totalFare = ((($roomFare + $vehicleFare)/$exchangeRate) + $insurance)/ 0.7 + 1000;
            $totalFare *= 1.04;
            $totalFare = intval($totalFare);
        }
        return $totalFare;
    }

    public function getAllHotelNameAndHotelId()
    {
        $hotelModel = new HotelModel();
        $data = $hotelModel->column('id','hotel_name');
    }

    public function exportPricingExcel(Request $request)
    {
        $hotelId = $request->param('hotel_id',18);
        $departureDate = $request->param('departure_date','2017-07-15');
        $itineraryDays = $request->param('itinerary_days','4');
        $data = [
            'hotel_id'=>$hotelId,
            'departure_date'=>$departureDate,
            'itinerary_days'=>$itineraryDays,
        ];
        $rule = [
            'hotel_id'=>'require|integer|>:0',
            'departure_date'=>'require|date',
            'itinerary_days'=>'require|integer|>:2',
        ];
        $result = $this->validate($data,$rule);
        if ($result !== true){
            abortError($result);
        }
        if(!$hotelData = HotelModel::get($hotelId)){
            abortError('hotel_id错误');
        }
        $this->hotelId = $hotelId;
        $this->departureDate = $departureDate;
        $this->itineraryDays = $itineraryDays;
        $this->exportExcel();
    }

    public function exportExcel()
    {
        $hotelModel = HotelModel::get($this->hotelId);
        $hotelName = $hotelModel->hotel_name;
        $CountryName = $hotelModel->country->country_name;
        $placeName = $hotelModel->place->place_name;
        $exchangeModel = $hotelModel->exchange;
        $currencyUnit = $exchangeModel->currency_unit;
        $departureDate = $this->departureDate;
        $itineraryDays = $this->itineraryDays;
        $roomData = $hotelModel->room()->column('room_name','id');
        $baseUnit = '人民币';
        $AllPricingData = $pricingData = [];
        foreach($roomData as $index => $item){
            $data = $this->pricing($index,$departureDate,$itineraryDays);
            $adultRoomFare = '';
            $adultVehicleFare = '';
            $extraAdultRoomFare = '';
            $extraAdultVehicleFare = '';
            $extraChildRoomFare = '';
            $extraChildVehicleFare = '';
            if (!empty($data)) {
                if (isset($data['fixed'])) {
                    $adultRoomFare = $data['fixed']['adult_fare']['room_fare'];
                    $adultVehicleFare = '包含';
                    if (isset($data['fixed']['adult_fare']['vehicle_fare'])) {
                        $adultVehicleFare = $data['fixed']['adult_fare']['vehicle_fare'];
                    }
                    if (isset($data['fixed']['extra_adult_fare'])) {
                        $extraAdultRoomFare = $data['fixed']['extra_adult_fare']['room_fare'];
                        $extraAdultVehicleFare = '包含';
                        if (isset($data['fixed']['extra_adult_fare']['vehicle_fare'])) {
                            $extraAdultVehicleFare = $data['fixed']['extra_adult_fare']['vehicle_fare'];
                        }
                    }
                    if (isset($data['fixed']['extra_child_fare'])) {
                        $extraChildRoomFare = $data['fixed']['extra_child_fare']['room_fare'];
                        $extraChildVehicleFare = '包含';
                        if (isset($data['fixed']['extra_child_fare']['vehicle_fare'])) {
                            $extraChildVehicleFare = $data['fixed']['extra_child_fare']['vehicle_fare'];
                        }
                    }
                } elseif (isset($data['base'])) {
                    $adultRoomFare = $data['base']['adult_fare']['room_fare'];
                    $adultVehicleFare = '包含';
                    if (isset($data['base']['adult_fare']['vehicle_fare'])) {
                        $adultVehicleFare = $data['base']['adult_fare']['vehicle_fare'];
                    }
                    if (isset($data['base']['extra_adult_fare'])) {
                        $extraAdultRoomFare = $data['base']['extra_adult_fare']['room_fare'];
                        $extraAdultVehicleFare = '包含';
                        if (isset($data['base']['extra_adult_fare']['vehicle_fare'])) {
                            $extraAdultVehicleFare = $data['base']['extra_adult_fare']['vehicle_fare'];
                        }
                    }
                    if (isset($data['base']['extra_child_fare'])) {
                        $extraChildRoomFare = $data['base']['extra_child_fare']['room_fare'];
                        $extraChildVehicleFare = '包含';
                        if (isset($data['base']['extra_child_fare']['vehicle_fare'])) {
                            $extraChildVehicleFare = $data['base']['extra_child_fare']['vehicle_fare'];
                        }
                    }
                }
            }
            $adultTotalFare = $this->getAdultTotalFare($adultRoomFare,$adultVehicleFare,$exchangeModel->exchange_rate);
            $extraAdultTotalFare = $this->getAdultTotalFare($extraAdultRoomFare,$extraAdultVehicleFare,$exchangeModel->exchange_rate);
            $extraChildTotalFare = $this->getChildTotalFare($extraChildRoomFare,$extraChildVehicleFare,$exchangeModel->exchange_rate);
            $adultVehicleFare = $adultVehicleFare == '' || $adultVehicleFare == '包含' ? $adultVehicleFare : $adultVehicleFare;
            $extraAdultVehicleFare = $extraAdultVehicleFare == '' || $extraAdultVehicleFare == '包含' ? $extraAdultVehicleFare : $extraAdultVehicleFare;
            $extraChildVehicleFare = $extraChildVehicleFare == ''  || $extraChildVehicleFare == '包含' ? $extraChildVehicleFare : $extraChildVehicleFare;
            $pricingData['adult_room_fare'] = round($adultRoomFare) . $currencyUnit;
            $pricingData['adult_vehicle_fare'] = round($adultVehicleFare) . $currencyUnit;
            $pricingData['adult_total_fare'] = round($adultTotalFare) . $baseUnit;
            $pricingData['extra_adult_room_fare'] = round($extraAdultRoomFare) . $currencyUnit;
            $pricingData['extra_adult_vehicle_fare'] = round($extraAdultVehicleFare) . $currencyUnit;
            $pricingData['extra_adult_total_fare'] = round($extraAdultTotalFare ) . $baseUnit;
            $pricingData['extra_child_room_fare'] = round($extraChildRoomFare) . $currencyUnit;
            $pricingData['extra_child_vehicle_fare'] = round($extraChildVehicleFare) . $currencyUnit;
            $pricingData['extra_child_total_fare'] = round($extraChildTotalFare) . $baseUnit;
            $pricingData['exchange_rate'] = $exchangeModel->exchange_rate;
            $pricingData['room_name'] = $item;
            $AllPricingData[] = $pricingData;
        }
        $pricingTitle = $CountryName.'-'.$placeName.'-'.$hotelName.','.$departureDate.'入住,住'.$itineraryDays.'天';
        $pricingTips = '注:总价货币单位为人民币,房价和交通价格货币单位为'.$currencyUnit;
        $this->excelInit();
        $this->excelName = $hotelName.'房型报价';
        $this->excelInit();
        $this->formatExcelPricingData($pricingTitle,$pricingTips,$AllPricingData,$currencyUnit);
        $this->excelSave();
    }
    public function excelInit()
    {
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        // Set properties
        $objPHPExcel->getProperties()
            ->setCreator("ZYone")
            ->setLastModifiedBy("ZYone")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        // 设置每一列宽度
        $objPHPExcel->getDefaultStyle()->getFont()->setName('微软雅黑')->setSize(10);//字体size
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $excelSheet = $objPHPExcel->getActiveSheet();
        $excelSheet->getDefaultRowDimension()->setRowHeight(22);
        $excelSheet->getDefaultColumnDimension()->setWidth(15);
        $excelSheet->getRowDimension('1')->setRowHeight(25);
        $excelSheet->getStyle('A1:H1')->getFont()->setSize(16)->setBold(true);
        $this->excelSheet = $excelSheet;
        $this->excelObj = $objPHPExcel;

    }

    protected function formatExcelPricingData($pricingTitle,$pricingTips,$pricingData, $currencyUnit)
    {
        $excelSheet = $this->excelSheet;
        $excelSheet->mergeCells('A1:K1');
        $excelSheet->setCellValue('A1',$pricingTitle);
        $excelSheet->mergeCells('A2:K2');
        $excelSheet->setCellValue('A2',$pricingTips);
        $excelSheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $excelSheet->getStyle('A2')->getFont()->setSize(12)->setBold(true);
        // 表头
        $excelSheet
            ->setCellValue('A3', '房型')
            ->setCellValue('B3', '标准成人房价')
            ->setCellValue('C3', '标准成人交通价格')
            ->setCellValue('D3', '标准成人总价')
            ->setCellValue('E3', '额外成人房价')
            ->setCellValue('F3', '额外成人交通价格')
            ->setCellValue('G3', '额外成人总价')
            ->setCellValue('H3', '额外儿童房价')
            ->setCellValue('I3', '额外儿童交通价格')
            ->setCellValue('J3', '额外儿童总价')
            ->setCellValue('K3', '人民币/'.$currencyUnit);
        foreach($pricingData as $key => $value){
            $order = $key + 4;
            $excelSheet
                ->setCellValue('A'.$order, $value['room_name'])
                ->setCellValue('B'.$order, $value['adult_room_fare'])
                ->setCellValue('C'.$order, $value['adult_vehicle_fare'])
                ->setCellValue('D'.$order, $value['adult_total_fare'])
                ->setCellValue('E'.$order, $value['extra_adult_room_fare'])
                ->setCellValue('F'.$order, $value['extra_adult_vehicle_fare'])
                ->setCellValue('G'.$order, $value['extra_adult_total_fare'])
                ->setCellValue('H'.$order, $value['extra_child_room_fare'])
                ->setCellValue('I'.$order, $value['extra_child_vehicle_fare'])
                ->setCellValue('J'.$order, $value['extra_child_total_fare'])
                ->setCellValue('K'.$order, $value['exchange_rate']);
        }
    }

    protected function excelSave()
    {
        $title_excel = $this->excelName;
        // 设置 sheet 名字
        $this->excelSheet->setTitle($title_excel);
        // 设置打开时选择 第一个 sheet
        $this->excelObj->setActiveSheetIndex(0);

        // 输出
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $title_excel . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($this->excelObj, 'Excel5');
        $objWriter->save('php://output');
    }
}
