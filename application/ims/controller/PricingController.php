<?php

namespace app\ims\controller;

use app\components\Excel;
use app\ims\model\ContractModel;
use app\ims\model\ContractSeasonModel;
use app\ims\model\HotelDefaultVehicleModel;
use app\ims\model\HotelModel;
use app\ims\model\HotelRoomModel;
use app\ims\model\PlaceModel;


class PricingController extends BasePricingController
{
    public $standardLogic = '或';
    public $standardQuantityOfAdult = 2;
    public $standardQuantityOfChild = 1;
    public $standardQuantityOfExtraAdult = 1;
    public $standardQuantityOfRoom = 0;
    public $inputQuantityOfAdult;
    public $inputQuantityOfChild;
    public $inputQuantityOfRoom;
    public $maxPassengers;
    public $minPassengers;
    public $error;

    public $quantityOfRoom;

    public function testPricingSeasonFareByHotelId($hotelId = 15)
    {
        $this->pricingSeasonFare($hotelId);
    }

    public function formatDate($date)
    {
        $result = date('Y.m.d',strtotime($date));
        return $result;
    }

    public function pricingSeasonFare($hotelId)
    {
        $stayingNights = 3;
        $hotelModel = HotelModel::get($hotelId);
        if (!$hotelModel || !$hotelModel->place || !$hotelModel->country) {
            return false;
        }
        $countryName = $hotelModel->country->country_name;
        $placeName = $hotelModel->place->place_name;
        $data['country_name'] = $countryName;
        $data['place_name'] = $placeName;
        $data['exchange_rate'] = $placeName = $hotelModel->exchange->exchange_rate;
        $contractModel = new ContractModel();
        $contractData = $contractModel->where('hotel_id',$hotelId)->where('date_type','可用')->order('contract_start_date')->select();
        halt($contractData);
        foreach ($contractData as $key => $contractDatum) {
            $data['expired_date'][] =  $this->formatDate($contractDatum['contract_start_date']).'-'.$this->formatDate($contractDatum['contract_end_date']);
            $contractSeasonModel = new ContractSeasonModel();
            $contractSeasonData = $contractSeasonModel->where('contract_id',$contractDatum['id'])->group('season_unqid')->select();
            if (count($contractSeasonData) !== 0) {
                foreach ($contractSeasonData as $index => $contractSeasonDatum) {
                    $fareDataSet[$key]['season_data'][$index]['season_name'] = $contractSeasonDatum['season_name'];
                    $fareDataSet[$key]['season_data'][$index]['season_date'] = $this->formatDate($contractSeasonDatum['season_start_date']).'-'.$this->formatDate($contractSeasonDatum['season_end_date']);
                    $date = $contractSeasonDatum['season_start_date'];
                    $roomData = $hotelModel->room;
                    $this->quantityOfRoom = count($roomData);
                    foreach ($roomData as $i => $roomDatum) {
                        $result = $this->getPackageFareByCheckInDate($roomDatum['id'], $stayingNights,$date);
                        if ($result !== false){
                            $fareData['adult_fare'] = $result['adult_fare'];
                            $fareData['adult_extension_night_fare'] = $result['adult_extension_night_fare'];
                            if ($this->quantityOfChild >= 1){
                                $fareData['child_fare'] = $result['child_fare'];
                                $fareData['child_extension_night_fare'] = $result['child_extension_night_fare'];
                            }else{
                                $fareData['child_fare'] = '无';
                                $fareData['child_extension_night_fare'] ='无';
                            }
                            if ($this->quantityOfExtraAdult >= 1){
                                $fareData['extra_adult_fare'] = $result['extra_adult_fare'];
                                $fareData['extra_adult_extension_night_fare'] = $result['extra_adult_extension_night_fare'];
                            }else{
                                $fareData['extra_adult_fare'] = '无';
                                $fareData['extra_adult_extension_night_fare'] = '无';
                            }
                            if ($index === 0){
                                if ($i == 0) {
                                    $roomNameSet[$key][] = '';
                                }
                                $roomNameSet[$key][] = $roomDatum['room_name'];
                            }
                        }
                    $fareDataSet[$key]['fare_data'][$index][] = $fareData;
                    }
                }
            }
        }
        if (isset($fareDataSet) && isset($fareData)) {
            $this->exportSeasonFare($data, $roomNameSet, $fareDataSet);
        }else{
            return false;
        }
    }

    public function exportSeasonFare($data, $roomNameSet, $fareDataSet)
    {
        $excel =  new Excel();
        $hotelModel = HotelModel::get($this->hotelId);
        $excel->init();
        $excel->getActiveSheet()->setCellValue('A1', '国家海岛');
        $excel->getActiveSheet()->setCellValue('A2', '报价有效期');
        $excel->getActiveSheet()->setCellValue('A3', '汇率');
        $excel->getActiveSheet()->setCellValue('A4', '酒店');
        $excel->getActiveSheet()->setCellValue('B1', $data['country_name'] . '/' . $data['place_name']);
        $excel->fileName = $data['country_name'] . '-' . $data['place_name'] . '-' . $hotelModel->hotel_name . '(4D3N)';
        if (count($data['expired_date']) > 1) {
            foreach ($data['expired_date'] as $key => $value) {
                $letter = get_letter(2 + $key);
                $order = 2;
                $excel->getActiveSheet()->setCellValue($letter . $order, $value);
            }
        }else{
            $excel->getActiveSheet()->setCellValue('B2', $data['expired_date'][0]);
        }
        $excel->getActiveSheet()->setCellValue('B3', '1人民币 = ' . $data['exchange_rate'] . $this->currencyUnit);
        $excel->getActiveSheet()->setCellValue('B4', $hotelModel->hotel_name . '(4D3N)');
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $order = 2;
        foreach ($roomNameSet as $roomNameContractOrder => $roomNameData) {
            $firstLetter = 'A';
            $secondLetter = 'B';
            foreach ($roomNameData as $roomNameOrder => $roomName) {
                $order += 3;
                $excel->getActiveSheet()->setCellValue($firstLetter . $order, $roomName);
                if ($roomName != '') {
                    $excel->getActiveSheet()->setCellValue($secondLetter . ($order + 0), '成人价格');
                    $excel->getActiveSheet()->setCellValue($secondLetter . ($order + 1), '儿童价格');
                    $excel->getActiveSheet()->setCellValue($secondLetter . ($order + 2), '额外成人');
                }
            }
        }
        $order = 0;
        foreach ($fareDataSet as $contractOrder => $seasonFareData) {
            foreach ($seasonFareData['season_data'] as $seasonDataOrder => $seasonData) {
                $medium = 3;
                if ($contractOrder == 0) {
                    $medium = 0;
                }
                $letter = get_letter(3 + $seasonDataOrder * 2);
                $firstOrder = 5 + $medium + $this->quantityOfRoom * $contractOrder * 3;
                $secondOrder = 6 + $medium + $this->quantityOfRoom * $contractOrder * 3;
                $excel->getActiveSheet()->getColumnDimension($letter)->setWidth(25);
                $excel->getActiveSheet()->setCellValue($letter . $firstOrder, $seasonData['season_name']);
                $excel->getActiveSheet()->setCellValue($letter . $secondOrder, $seasonData['season_date']);
            }
            foreach ($seasonFareData['fare_data'] as $seasonOrder => $fareData) {
                $medium = 3;
                if ($contractOrder == 0) {
                    $medium = 0;
                }
                $firstLetter = get_letter(3 + $seasonOrder * 2);
                $secondLetter = get_letter(4 + $seasonOrder * 2);
                foreach ($fareData as $fareOrder => $fareDatum) {
                    $firstOrder = (8 + $medium + $this->quantityOfRoom * $contractOrder * 3) + $fareOrder * 3;
                    $secondOrder = (9 + $medium + $this->quantityOfRoom * $contractOrder * 3) + $fareOrder * 3;
                    $thirdOrder = (10 + $medium + $this->quantityOfRoom * $contractOrder * 3) + $fareOrder * 3;
                    $excel->getActiveSheet()->setCellValue($firstLetter . $firstOrder, $fareDatum['adult_fare']);
                    $excel->getActiveSheet()->setCellValue($firstLetter . $secondOrder, $fareDatum['child_fare']);
                    $excel->getActiveSheet()->setCellValue($firstLetter . $thirdOrder, $fareDatum['extra_adult_fare']);
                    $excel->getActiveSheet()->setCellValue($secondLetter . $firstOrder, $fareDatum['adult_extension_night_fare']);
                    $excel->getActiveSheet()->setCellValue($secondLetter . $secondOrder, $fareDatum['child_extension_night_fare']);
                    $excel->getActiveSheet()->setCellValue($secondLetter . $thirdOrder, $fareDatum['extra_adult_extension_night_fare']);
                    if ($fareOrder == 0) {
                        $excel->getActiveSheet()->setCellValue($firstLetter . ($firstOrder - 1), '报价');
                        $excel->getActiveSheet()->setCellValue($secondLetter . ($firstOrder - 1),  '延住一晚');
                    }
                }
            }
            $hotelDefaulVehicleModel = new HotelDefaultVehicleModel();
            $vehicleData = $hotelDefaulVehicleModel->where('hotel_id',$this->hotelId)->find();
            if ($vehicleData) {
                if (!isset($fareDataSet[$contractOrder + 1])) {
                    $letter = 'A';
                    $order = $thirdOrder + 1;
                    $excel->getActiveSheet()->setCellValue($letter . $order, '去程：');
                    $excel->getActiveSheet()->setCellValue($letter . ($order + 1), '返程：');
                    foreach (json_decode($vehicleData->default_go_vehicle,true) as $key => $value) {
                        $letter = get_letter(2 + $key);
                        $excel->getActiveSheet()->setCellValue($letter . $order, $value['name']);
                    }
                    foreach (json_decode($vehicleData->default_back_vehicle,true) as $key => $value) {
                        $letter = get_letter(2 + $key);
                        $excel->getActiveSheet()->setCellValue($letter . ($order + 1), $value['name']);
                    }
                }
            }else{
                $letter = 'A';
                $order = $thirdOrder + 1;
                $excel->getActiveSheet()->setCellValue($letter . $order, '没有默认交通');
            }
        }
        if ($this->adultPackageData) {
            $letter = 'A';
            $order = $thirdOrder + 3;
            $excel->getActiveSheet()->setCellValue($letter . $order, '活动：');
            $icludeActivityData = json_decode($this->adultPackageData->include_activity, true);
            foreach ($icludeActivityData as $key => $value) {
                $letter = get_letter(2 + $key);
                $excel->getActiveSheet()->setCellValue($letter . $order, $value['activity_name']);
            }
        }
        // halt(1);
        $excel->export();
    }

    public function exportSeasonFare1($data, $seasonData, $fareDataSet)
    {
        $excel =  new Excel();
        $excel->init();
        $excel->getActiveSheet()->setCellValue('A1', '国家海岛');
        $excel->getActiveSheet()->setCellValue('A2', '报价有效期');
        $excel->getActiveSheet()->setCellValue('A3', '汇率');
        $excel->getActiveSheet()->setCellValue('B1', $data['place_name']);
        $excel->getActiveSheet()->setCellValue('B2', $data['expired_date']);
        $excel->getActiveSheet()->setCellValue('B3', $data['exchange_rate']);
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $roomOrder = 0;
        foreach ($seasonData as $seasonOrder => $seasonDatum) {
            $firstOrder = 5;
            $secondOrder = 6;
            $firstLetter = get_letter(2 + $seasonOrder * 3);
            $excel->getActiveSheet()->setCellValue($firstLetter . $firstOrder, $seasonDatum['season_name']);
            $excel->getActiveSheet()->setCellValue($firstLetter . $secondOrder, $seasonDatum['season_date']);
        }
        halt($fareDataSet);
        foreach ($fareDataSet as $roomName => $fareData) {
            dump('----');
            dump($fareData);
            $letter = 'A';
            $index = 7 + $roomOrder * 3;
            $excel->getActiveSheet()->setCellValue($letter . $index, $roomName);
            foreach ($fareData as $fareOrder => $item) {
                $order = 7 + $fareOrder * 3;
                $firstLetter = get_letter(2 + $fareOrder * 3);
                $secondLetter = get_letter(3 + $fareOrder * 3);
                $thirdLetter = get_letter(4 + $fareOrder * 3);
                $excel->getActiveSheet()->setCellValue($firstLetter . ($order + 0), '成人价格');
                $excel->getActiveSheet()->setCellValue($firstLetter . ($order + 1), '儿童价格');
                $excel->getActiveSheet()->setCellValue($firstLetter . ($order + 2), '额外成人');
                $excel->getActiveSheet()->setCellValue($secondLetter . ($order + 0), $item['adult_fare']);
                $excel->getActiveSheet()->setCellValue($secondLetter . ($order + 1), $item['child_fare']);
                $excel->getActiveSheet()->setCellValue($secondLetter . ($order + 2), $item['extra_adult_fare']);
                $excel->getActiveSheet()->setCellValue($thirdLetter . ($order + 0), $item['extra_adult_extension_night_fare']);
                $excel->getActiveSheet()->setCellValue($thirdLetter . ($order + 1), $item['child_extension_night_fare']);
                $excel->getActiveSheet()->setCellValue($thirdLetter . ($order + 2), $item['extra_adult_extension_night_fare']);
            }
            $roomOrder++;
        }
        $excel->export();
    }

    public function getQuantityOfRoom()
    {
        $this->inputQuantityOfAdult = 15;
        $this->inputQuantityOfChild = 3;
        $this->inputQuantityOfRoom = 7;
        $this->maxPassengers = 20;
        $this->minPassengers = 1;
        $roomArrangementList = $this->pricingInputQuantityOfRoom();
        if (!$roomArrangementList){
            return getError($this->error);
        }
        $roomId = 75;
        $date = '2017-05-12';
        $night = 3;
        foreach ($roomArrangementList as $roomOrder => $roomArrangement) {
            $this->quantityOfAdult = $roomArrangement['standard_quantity_of_adult'];
            $this->quantityOfExtraAdult = $roomArrangement['standard_quantity_of_extra_adult'];
            $this->quantityOfChild = $roomArrangement['standard_quantity_of_child'];
            var_dump($this->quantityOfAdult);
            var_dump($this->quantityOfExtraAdult);
            var_dump($this->quantityOfChild);
            $totalFare = $this->getPackageFareByCheckInDate($roomId,$night,$date);
            dump($totalFare);
        }
    }

    public function pricingStandardQuantityOfRoom()
    {
        $standardLogic = $this->standardLogic;
        $standardQuantityOfAdult = $this->standardQuantityOfAdult;
        $standardQuantityOfChild = $this->standardQuantityOfChild;
        $standardQuantityOfExtraAdult = $this->standardQuantityOfExtraAdult;
        $inputQuantityOfAdult = $this->inputQuantityOfAdult;
        $inputQuantityOfChild = $this->inputQuantityOfChild;
        $inputQuantityOfRoom = $this->inputQuantityOfRoom;
        $inputQuantityOfPassengers = $this->inputQuantityOfAdult + $this->inputQuantityOfChild;
        $roomArrangementList = [];
        $haveBalanceForRoomCharge = 0;
        $haveExtraAdult = 0;
        $maxQuantityOfRoom = $this->maxPassengers / $this->standardQuantityOfAdult;

        if ($inputQuantityOfAdult <= 1) {
            $this->error = '最少要有一个成人出行';
            return false;
        }
        if ($inputQuantityOfChild > 0 && $standardQuantityOfChild <= 0){
            $this->error = '该房型不适合儿童入住';
            return false;
        }
        if ($inputQuantityOfPassengers > $this->maxPassengers){
            $this->error = '出行人数大于最大值';
            return false;
        }
        if ($inputQuantityOfPassengers < $this->minPassengers){
            $this->error = '出行人数小于最小值';
            return false;
        }
        if ($inputQuantityOfRoom > $maxQuantityOfRoom) {
            $this->error = '要求房间数量大于最大房间数量';
            return false;
        }
        while ($inputQuantityOfPassengers > 0) {
            if ($inputQuantityOfAdult == 0 && $inputQuantityOfChild >= $standardQuantityOfAdult) {
                $roomArrangement['standard_quantity_of_adult'] = $standardQuantityOfAdult;
                $inputQuantityOfChild -= $standardQuantityOfChild;
            }
            if ($inputQuantityOfAdult >= $standardQuantityOfAdult) {
                $roomArrangement['standard_quantity_of_adult'] = $standardQuantityOfAdult;
                $inputQuantityOfAdult -= $standardQuantityOfAdult;
            }else{
                $roomArrangement['standard_quantity_of_adult'] = $inputQuantityOfAdult;
                if ($inputQuantityOfAdult > 0) {
                    $haveBalanceForRoomCharge += 1;
                }
                $inputQuantityOfPassengers -= $inputQuantityOfAdult;
                $inputQuantityOfAdult = 0;
            }
            if ($standardLogic == '且'){
                if ($inputQuantityOfAdult > $standardQuantityOfExtraAdult) {
                    $roomArrangement['standard_quantity_of_extra_adult'] = $standardQuantityOfExtraAdult;
                    $inputQuantityOfAdult -= $standardQuantityOfExtraAdult;
                }else{
                    $haveExtraAdult += 1;
                    $roomArrangement['standard_quantity_of_extra_adult'] = 0;
                }
            }

            if ($standardLogic == '或') {
                $roomArrangement['standard_quantity_of_extra_adult'] = 0;
                if ($inputQuantityOfAdult >= $standardQuantityOfExtraAdult && $inputQuantityOfChild == 0) {
                    $roomArrangement['standard_quantity_of_extra_adult'] = $standardQuantityOfExtraAdult;
                    $inputQuantityOfAdult -= $standardQuantityOfExtraAdult;
                    $haveExtraAdult += 1;
                }
            }
            if ($inputQuantityOfChild >= $standardQuantityOfChild) {
                $roomArrangement['standard_quantity_of_child'] = $standardQuantityOfChild;
                $inputQuantityOfChild -= $standardQuantityOfChild;
            }else{
                $roomArrangement['standard_quantity_of_child'] = $inputQuantityOfChild;
                $inputQuantityOfChild = 0;
            }
            if ($inputQuantityOfAdult == 0 && $inputQuantityOfChild == 0) {
                $inputQuantityOfPassengers = 0;
            }
            $roomArrangementList[] = $roomArrangement;
        }
        if ($haveBalanceForRoomCharge > 0 && $haveExtraAdult > 0){
            foreach ($roomArrangementList as $roomOrder => $roomArrangement){
                if ($haveBalanceForRoomCharge > 0){
                    if ($roomArrangement['standard_quantity_of_adult'] < $standardQuantityOfAdult){
                        $roomArrangementList[$roomOrder]['standard_quantity_of_adult'] += 1;
                        $haveBalanceForRoomCharge -= 1;
                    }
                    if ($roomArrangement['standard_quantity_of_extra_adult'] > 0 ){
                        $roomArrangementList[$roomOrder]['standard_quantity_of_adult'] = 0;
                    }
                }

            }
        }
        $this->standardQuantityOfRoom = count($roomArrangementList);
        return $roomArrangementList;
    }

    public function pricingInputQuantityOfRoom()
    {
        $standardRoomArrangementList = $this->pricingStandardQuantityOfRoom();
        $standardQuantityOfRoom = $this->standardQuantityOfRoom;
        $standardLogic = $this->standardLogic = '或';
        $standardQuantityOfAdult = $this->standardQuantityOfAdult;
        $standardQuantityOfChild = $this->standardQuantityOfChild;
        $standardQuantityOfExtraAdult = $this->standardQuantityOfExtraAdult;
        $inputQuantityOfAdult = $this->inputQuantityOfAdult;
        $inputQuantityOfChild = $this->inputQuantityOfChild;
        $inputQuantityOfRoom = $this->inputQuantityOfRoom;
        $inputQuantityOfPassengers = $this->inputQuantityOfAdult + $this->inputQuantityOfChild;
        $roomArrangementList = [];
        if ($inputQuantityOfRoom > $standardQuantityOfRoom) {
            for ($i = 0; $i < $inputQuantityOfRoom ; $i++) {
                if ($inputQuantityOfAdult == 0 && $inputQuantityOfChild >= $standardQuantityOfAdult) {
                    $roomArrangement['standard_quantity_of_adult'] = $standardQuantityOfAdult;
                    $inputQuantityOfPassengers -= $standardQuantityOfChild;
                    $inputQuantityOfChild -= $standardQuantityOfChild;
                }
                if ($inputQuantityOfAdult >= $standardQuantityOfAdult) {
                    $roomArrangement['standard_quantity_of_adult'] = $standardQuantityOfAdult;
                    $inputQuantityOfPassengers -= $standardQuantityOfAdult;
                    $inputQuantityOfAdult -= $standardQuantityOfAdult;
                }else{
                    $roomArrangement['standard_quantity_of_adult'] = $inputQuantityOfAdult;
                    $inputQuantityOfPassengers -= $inputQuantityOfAdult;
                    $inputQuantityOfAdult = 0;
                }
                if ($inputQuantityOfChild >= $standardQuantityOfChild) {
                    $roomArrangement['standard_quantity_of_child'] = $standardQuantityOfChild;
                    $inputQuantityOfChild -= $standardQuantityOfChild;
                    $inputQuantityOfPassengers -= $standardQuantityOfChild;
                }else{
                    $roomArrangement['standard_quantity_of_child'] = $inputQuantityOfChild;
                    $inputQuantityOfChild = 0;
                }
                if ($inputQuantityOfAdult == 0 && $inputQuantityOfChild == 0) {
                    $inputQuantityOfPassengers = 0;
                }
                $roomArrangement['standard_quantity_of_extra_adult'] = 0;
                $roomArrangementList[] = $roomArrangement;
            }
            if ($inputQuantityOfPassengers > 0) {
                foreach ($roomArrangementList as $roomOrder => $roomArrangement) {
                    if ($standardLogic == '且' && $inputQuantityOfPassengers > $standardQuantityOfExtraAdult) {
                        $roomArrangementList[$roomOrder]['standard_quantity_of_extra_adult'] = $standardQuantityOfExtraAdult;
                        $inputQuantityOfPassengers -= $standardQuantityOfExtraAdult;
                    }else{
                        $roomArrangementList[$roomOrder]['standard_quantity_of_extra_adult'] = 0;
                    }
                    if ($standardLogic == '或') {
                        $roomArrangementList[$roomOrder]['standard_quantity_of_extra_adult'] = 0;
                        if ($inputQuantityOfPassengers >= $standardQuantityOfExtraAdult && $roomArrangementList[$roomOrder]['standard_quantity_of_child'] == 0) {
                            $roomArrangementList[$roomOrder]['standard_quantity_of_extra_adult'] = $standardQuantityOfExtraAdult;
                            $inputQuantityOfPassengers -= $standardQuantityOfExtraAdult;
                        }
                    }
                }
            }
        }else{
            $roomArrangementList = $standardRoomArrangementList;
        }
        return $roomArrangementList;
    }

    public function exportRoomFare($date = '2017-08-15', $night = 3)
    {
        $placeModel = new PlaceModel();
        $hotelModel = new HotelModel();
        $hotelRoomModel = new HotelRoomModel();
        $excel =  new Excel();
        $headerData = ['国家', '海岛', '酒店', '房型', '出发日期', '成人价格', '儿童价格'];
        $bodyData = [];
        $placeIdList = $placeModel->column('id');
        $hotelIdList = $hotelModel->where('place_id','IN',$placeIdList)->column('id');
        $roomData = $hotelRoomModel->where('hotel_id','IN',$hotelIdList)->select();
        foreach ($roomData as $roomDatum) {
            $hotelModel = HotelModel::get($roomDatum->hotel_id);
            $countryName = $hotelModel->country->country_name;
            $placeName = $hotelModel->place->place_name;
            $hotelName = $hotelModel->hotel_name;
            $totalFare = $this->getPackageFareByCheckInDate($roomDatum->id,$night,$date);
            if ($totalFare){
                $data[]= $countryName;
                $data[] = $placeName;
                $data[] = $hotelName;
                $data[] = $roomDatum->room_name;
                $data[] = $date;
                $data[] = $totalFare['adult_fare'];
                if (isset($totalFare['child_fare'])){
                    $data[] = $totalFare['child_fare'];
                }else{
                    $data[] = '不允许儿童入住';
                }
                $bodyData[] = $data;
                unset($data);
            }
        }
        $excel->defaultExport($headerData, $bodyData);
    }

    public function checkFareList($date = '2017-08-15', $night = 3)
    {
        $hotelRoomModel = new HotelRoomModel();
        $placeModel = new PlaceModel();
        $hotelModel = new HotelModel();
        $placeIdList = $placeModel->column('id');
        $hotelIdList = $hotelModel->where('place_id','IN',$placeIdList)->column('id');
        $roomIdList = $hotelRoomModel->field('hotel_id,room_name,id')->where('hotel_id','IN',$hotelIdList)->select();
        echo "<h1>入住日期:$date---入住晚数:$night </h1>";
        foreach ($roomIdList as $item) {
            $hotelName = $hotelModel->where('id',$item['hotel_id'])->value('hotel_name');
            echo '酒店名称 : ' . $hotelName . '<br />';
            echo '房间ID : ' . $item['id'] . '<br />';
            echo '房间名称 : ' . $item['room_name']. '<br />';
            $totalFare = $this->getPackageFareByCheckInDate($item['id'],$night,$date);
            if (!$totalFare){
                echo $this->error. '<br />';
            }else{
                echo '成人总价 : ' . $totalFare['adult_fare'] . '人民币'. '<br />';
                foreach ($totalFare['exchange_data'] as $i => $exchange_datum) {
                    echo '货币 : ' . $i. '<br />';
                    echo '汇率 : ' . $exchange_datum. '<br />';
                }
                echo '成人房价信息 : '. $totalFare['adult_fare_detail']['room_detail'][0][$item['room_name']]. '<br />';
                if (!empty($totalFare['adult_fare_detail']['vehicle_detail']) && $totalFare['adult_fare_detail']['vehicle_detail'] != '包含'){
                    if (isset($totalFare['adult_fare_detail']['vehicle_detail']['go'])){
                        foreach ($totalFare['adult_fare_detail']['vehicle_detail']['go'] as $value) {
                            foreach ($value as $index => $vehicle) {
                                echo '成人去程 : ';
                                echo '交通方式 : ' . $index. '|';
                                echo '交通价格 : ' . $vehicle. '<br />';
                            }
                        }
                    }
                    if (isset($totalFare['adult_fare_detail']['vehicle_detail']['back'])){
                        foreach ($totalFare['adult_fare_detail']['vehicle_detail']['back'] as $value) {
                            foreach ($value as $index => $vehicle) {
                                echo '成人返程 : ';
                                echo '交通方式 : ' . $index. '|';
                                echo '交通价格 : ' . $vehicle. '<br />';
                            }
                        }
                    }
                }else{
                    echo '成人交通:包含'. '<br />';
                }
                if ($this->quantityOfChild >= 1){
                    echo '儿童总价 : ' . $totalFare['child_fare'] . '人民币'. '<br />';
                    echo '儿童房价信息 : '. $totalFare['child_fare_detail']['room_detail'][0][$item['room_name']]. '<br />';
                    if (!empty($totalFare['adult_fare_detail']['vehicle_detail']) && $totalFare['adult_fare_detail']['vehicle_detail'] != '包含'){
                        if (isset($totalFare['child_fare_detail']['vehicle_detail']['go'])){
                            foreach ($totalFare['child_fare_detail']['vehicle_detail']['go'] as $value) {
                                foreach ($value as $index => $vehicle) {
                                    echo '儿童去程 : ';
                                    echo '交通方式 : ' . $index. '|';
                                    echo '交通价格 : ' . $vehicle. '<br />';
                                }
                            }
                        }
                        if (isset($totalFare['child_fare_detail']['vehicle_detail']['back'])){
                            foreach ($totalFare['child_fare_detail']['vehicle_detail']['back'] as $value) {
                                foreach ($value as $index => $vehicle) {
                                    echo '儿童返程 : ';
                                    echo '交通方式 : ' . $index. '|';
                                    echo '交通价格 : ' . $vehicle. '<br />';
                                }
                            }
                        }
                    }else{
                        echo '儿童交通:包含'. '<br />';
                    }

                }
                echo '<hr />';
            }
        }
    }

    public function getPackageFareByCheckInDateTest($roomId = 9,$date = '2017-08-12',$night = 3)
    {
        dump("room_id:$roomId");
        dump("date:$date");
        dump("night:$night");
        $totalFare = $this->getPackageFareByCheckInDate($roomId,$night,$date);
        halt($totalFare);
    }

    public function PackageFareByCheckInDateTestByRoomIdList($checkInDate = '2017-08-15', $stayingNights = 3)
    {
        $hotelRoomModel = new HotelRoomModel();
        $placeModel = new PlaceModel();
        $placeIdList = $placeModel->column('id');
        $hotelModel = new HotelModel();
        $hotelIdList = $hotelModel->where('place_id','IN',$placeIdList)->column('id');
        $roomIdList = $hotelRoomModel->field('hotel_id,room_name,id')->where('hotel_id','IN',$hotelIdList)->select();
        foreach ($roomIdList as $item) {
            $hotelName = HotelModel::get($item['hotel_id'])->hotel_name;
            echo '酒店名称:' . $hotelName . '<br />';
            echo '房间名称:'. $item['room_name'];
            $totalFare = $this->getPackageFareByCheckInDate($item['id'],$stayingNights,$checkInDate);
        }
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

    public function extensionNightFare($fare)
    {
        $fare = $fare / $this->exchangeRate;
        $fare = ceil($fare / 10) * 10;
        $totalFare = round($fare / 0.7, -1);$totalFare = round($fare / 0.7, -1);
        return $totalFare;
    }

    public function getPackageFareByCheckInDate($roomId, $stayingNights, $checkInDate)
    {
        $this->roomId = $roomId;
        $this->stayingNights = $stayingNights;
        $this->checkInDate = $checkInDate;
        $this->totalFare = [];
        $this->exchangeData = [];
        $this->packageData = [];
        $params['room_id']= [$roomId,'require|integer|>:0'];
        $params['stay_in_nights']= [$stayingNights,'require|integer|>:0'];
        $params['check_in_date']= [$checkInDate,'require|date'];
        $this->checkAllParam($params);
        $this->initRoomData();
        $adultRoomFare = $this->pricingRoomFare('标准成人');
        if (!$adultRoomFare){
            return false;
        }
        $this->totalFare['adult_fare_detail']['room_detail'][] = $this->roomFareDetail;
        $adultVehicleFare = $this->pricingPackageVehicleFare('标准成人');
        $this->totalFare['adult_fare_detail']['vehicle_detail'] = $this->vehicleFareDetail;
        $adultItemFare = $this->pricingItemFare();
        $this->totalFare['adult_fare_detail']['item_detail'] = $this->itemFareDetail;
        $this->totalFare['adult_fare'] = $this->getAdultTotalFare($adultRoomFare, $adultVehicleFare, $adultItemFare);
        $this->totalFare['adult_extension_night_fare'] = $this->extensionNightFare($this->extensionFare);

        if ($this->quantityOfChild >= 1){
            $childRoomFare = $this->pricingRoomFare('额外儿童');
            if ($childRoomFare === false){
                return false;
            }
            $this->totalFare['child_fare_detail']['room_detail'][] = $this->roomFareDetail;
            $childVehicleFare = $this->pricingPackageVehicleFare('额外儿童');
            $this->totalFare['child_fare_detail']['vehicle_detail'] = $this->vehicleFareDetail;
            $childItemFare = $this->pricingItemFare();
            $this->totalFare['child_fare_detail']['item_detail'] = $this->itemFareDetail;
            $this->totalFare['child_fare'] = $this->getChildTotalFare($childRoomFare,$childVehicleFare, $childItemFare);
            $this->totalFare['child_extension_night_fare'] = $this->extensionNightFare($this->extensionFare);
        }
        if ($this->quantityOfExtraAdult >= 1){
            $extraAdultRoomFare = $this->pricingRoomFare('额外成人');
            if ($extraAdultRoomFare === false){
                return false;
            }
            $this->totalFare['extra_adult_fare_detail']['room_detail'][] = $this->roomFareDetail;
            $extraAdultVehicleFare = $this->pricingPackageVehicleFare('额外成人');
            $this->totalFare['extra_adult_fare_detail']['vehicle_detail'] = $this->vehicleFareDetail;
            $extraChildItemFare = $this->pricingItemFare();
            $this->totalFare['child_fare_detail']['item_detail'] = $this->itemFareDetail;
            $this->totalFare['extra_adult_fare'] = $this->getExtraAdultTotalFare($extraAdultRoomFare,$extraAdultVehicleFare, $extraChildItemFare);
            $this->totalFare['extra_adult_extension_night_fare'] = $this->extensionNightFare($this->extensionFare);
        }
        $this->totalFare['exchange_data'] = $this->exchangeData;
        return (boolean)$this->totalFare ? $this->totalFare : false;
    }

    public function pricingPackageVehicleFare($packageType)
    {
        $vehicleFare = 0;
        $this->vehicleFareDetail = [];
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
                        $vehicleFare += $this->pricingVehicleFare($item['id'], $item['category'], '去程', $packageType);
                    }
                }
                $defaultBackVehicle = json_decode($defaultVehicleModel->default_back_vehicle,true);
                if ($defaultBackVehicle){
                    foreach ($defaultBackVehicle as $item) {
                        $vehicleFare += $this->pricingVehicleFare($item['id'], $item['category'], '返程', $packageType);
                    }
                }
            }else{
                $vehicleFare = 0;
            }
        }
        return $vehicleFare;
    }
}
