<?php

namespace app\ims\controller;

use app\ims\model\VehicleBaseModel;
use app\ims\model\VehicleModel;
use app\ims\model\VehicleTimeModel;
use app\ims\model\HotelModel;
use think\Request;

class VehicleBasicController extends PrivilegeController
{
    protected $vehicleId = 0;
    protected $vehicleCategory;
    protected $journeyType;
    protected $vehicleType;
    protected $takeTime;
    protected $hotelId;
    protected $page = 1;
    protected $listRows = 4;
    protected $pageCount = 0;

    public function __construct()
    {
        $this->hotelId = $this->post('hotel_id');
        $this->vehicleId = $this->post('trf_id');
        $this->vehicleType = $this->post('type');
        $this->vehicleCategory = $this->post('category','单程交通');
        $this->journeyType = $this->post('journey_type','');
        $this->takeTime = $this->post('take_time','');

    }

    /**
     * 添加交通时间信息
     */
    public function addTimeData()
    {
        $vehicleTimeModel = new VehicleTimeModel();
        $timeData = $this->formatInputTimeData();
        if(!$vehicleTimeModel->saveAll($timeData)){
            exception('交通时间信息添加失败');
        }
    }

    /**
     * 删除排期信息
     */
    public function deleteScheduleData($scheduleId = '')
    {
        $scheduleId = $this->post('schedule_id',$scheduleId);
        if(isset($scheduleId) && empty($scheduleId)) {
            exception('班次id不能为空');
        }
        $timeModel = new VehicleTimeModel();
        $where['schedule_id'] = $scheduleId;
        if($timeModel->where($where)->delete()){
            return getSucc('删除排期信息成功');
        }
        return getErr('删除排期信息失败');
    }

    /**
     * 删除班次信息
     */
    public function deleteShiftData($shiftId = '')
    {
        $shiftId = $this->post('shift_id',$shiftId);
        if(isset($shiftId) && empty($shiftId)) {
            exception('班次id不能为空');
        }
        $timeModel = new VehicleTimeModel();
        $where['shift_id'] = $shiftId;
        if($timeModel->where($where)->delete()){
            return getSucc('删除班次信息成功');
        }
        return getErr('删除班次信息失败');
    }

    public function queryFareInfo($id,$type)
    {
        if ($type == '单程交通') {
            $vehicleModel = VehicleModel::get($id);
            $vehicleBaseModel = $vehicleModel->singleBase;
        }else{
            $vehicleBaseModel = VehicleBaseModel::get($id);
        }
        return $this->formateData($vehicleBaseModel);
    }

    public function queryInfo()
    {
        $category = $this->vehicleCategory;
        $journeyType = $this->journeyType;
        $type = $this->vehicleType;
        $where['hotel_id'] = $this->hotelId;
        $where['vehicle_category'] = $category;
        $return = [];
        $ret = '';
        if($category == '联程交通'){
            $where['connect_journey_type'] = $journeyType;
            $vehicleBaseModel = new VehicleBaseModel();
            $baseData = $vehicleBaseModel->where($where)->select();
            foreach ($baseData as $index => $baseDatum) {
                $vehicleData = $baseDatum->connectVehicle;
                $ret['id'] = $baseDatum->id;
                $info = '';
                foreach ($vehicleData as $key => $vehicleDatum) {
                    $info .= $vehicleDatum->departure_place_name . '-' .
                        $vehicleDatum->destination_name.'/'.
                        $vehicleDatum->vehicle_name .
                        ':联程交通节点 | ';
                }
                $ret['info'] = $info;
                $return[] = $ret;
            }
        }elseif($category == '单程交通'){
            if(isset($type)){
                $where['vehicle_type'] = $type;
            }

            $where['single_journey_type'] = $journeyType;
            $vehicleModel = new VehicleModel();
            $data = $vehicleModel->where($where)->group('departure_city')->select();

            foreach ($data as $index => $datum) {
                $return[$index]['id'] = $datum->id;
                $return[$index]['info'] = $datum->departure_place_name . '-' .
                    $datum->destination_name.'/'. $datum->vehicle_name;
            }
        }else{
            return getErr('信息查询失败!');
        }
        return getSucc($return);
    }

    public function getNodeInfo($where,$ret = array())
    {
        $vehicleModel = new VehicleModel();
        if($vehicleData = $this->getPageData($vehicleModel,$where)){
            $ret = format_object_data($vehicleData,'baseData');
        }
        return $ret;
    }

    public function getNodeInfoByWeek($where,$ret = array())
    {
        $vehicleTimeModel = new VehicleTimeModel();
        if($timeData = $this->getPageData($vehicleTimeModel,$where)){
            $ret = format_object_data($timeData,'vehicle','baseData');
        }
        return $ret;
    }

    public function getPageCount($model,$where)
    {
        return  $model->where($where)->count();
    }


    public function getPageData($model,$where,$data = array())
    {
        $pageCount = $model->where($where)->count();
        $this->pageCount = $pageCount;
        if($pageCount > 0){
            if($this->page > ceil($pageCount/$this->listRows)) {
                exception('页码过大');
            }
            $data = $model
                ->where($where)
                ->page($this->page,$this->listRows)
                ->select();
/*            halt($model
                ->where($where)
                ->page($this->page,$this->listRows)
                ->buildSql());*/
        }
        return $data;
    }

    /**
     * 获取所有节点的信息
     */
    public function getAllNodeInfo()
    {
        $week = $this->post('week');
        $name = $this->post('method');
        $this->page = $this->post('page',1);
        $this->listRows = $this->post('listRows',4);
        if($name){
            $where['vehicle_name'] = $name;
        }
        $where['hotel_id']=$this->hotelId;
        $where['vehicle_category']=$this->vehicleCategory;
        if($this->vehicleCategory == '联程交通'){
            $where['vehicle_base_id'] = 0;
        }
        if ($this->vehicleType == '定期交通' && !empty($week)) {
            $where['departure_week']=['like',"%".$week."%"];
            $ret = $this->getNodeInfoByWeek($where);
        }else{
            if (!empty($this->vehicleType)){
                $where['vehicle_type'] = $this->vehicleType;
            }
            $ret = $this->getNodeInfo($where);
        }
        $return['list'] = $ret;
        $return['total'] = $this->pageCount;
        return getSucc($return);
    }

    /*******格式化提交的交通工具表数据*******/
    public function formatInputData()
    {
        $data['hotel_id'] = $this->hotelId;
        $data['vehicle_type'] = $this->vehicleType;
        $data['vehicle_name']= $this->post('name');
        $data['vehicle_category'] = $this->vehicleCategory;
        $data['single_journey_type'] = $this->journeyType;
        $data['departure_city'] = $this->post('departure_city');
        $data['departure_place_name'] = $this->post('departure_place_name');
        $data['departure_place_ename'] = $this->post('departure_place_ename');
        $data['destination_city'] = $this->post('destination_city');
        $data['destination_name'] = $this->post('destination_name');
        $data['destination_ename'] = $this->post('destination_ename');
        if($this->vehicleType == '接驳交通'){
            $wasteHour = $this->post('waste_hour');
            $wasteMinutes = $this->post('waste_minutes');
            $data['transfer_take_time'] = $wasteHour . ',' . $wasteMinutes;
        }
        return $data;
    }

    /*******格式化提交的交通时间表数据*******/

    public function formatInputTimeData($timeData = array())
    {
        $inputShiftData = json_decode($this->post('step_shift'),true);
        $inputTimeData['hotel_id'] = $this->hotelId;
        $inputTimeData['vehicle_category'] = $this->vehicleCategory;
        $inputTimeData['vehicle_id'] = $this->vehicleId;
        foreach ($inputShiftData as $index => $timeDatum) {
            if(isset($timeDatum['schedule_id'])){
                $this->deleteScheduleData($timeDatum['schedule_id']);
            }
            $inputTimeData['schedule_id'] = uniqid();
            foreach ($timeDatum['shift'] as $item) {
                if(isset($timeDatum['shift_id'])){
                    $this->deleteScheduleData($timeDatum['shift_id']);
                }
                $inputTimeData['shift_id'] = uniqid();
                $inputTimeData['in_time'] = $item['enter_time'];
                $inputTimeData['out_time'] = $item['leave_time'];
                $inputTimeData['departure_time'] = $item['start_time'];
                $inputTimeData['arrival_time'] = $item['end_time'];
                $inputTimeData['arrival_days'] = (integer)$item['days'];
                $seconds = strtotime($inputTimeData['departure_time']) - strtotime($inputTimeData['arrival_time']);
                $inputTimeData['fixed_take_time'] = tran_seconds_to_hours_and_minutes($seconds);
                $inputTimeData['departure_week'] = implode(',',$timeDatum['week']);
                array_push($timeData,$inputTimeData);
                }
            }
            return $timeData;
        }


    /*******格式化提交的交通信息表数据*******/
    public function formatInputBaseData()
    {
        $baseData['hotel_id'] = $this->hotelId;
        $baseData['vehicle_id'] = $this->vehicleId;
        $baseData['vehicle_category'] = $this->vehicleCategory;
        $baseData['connect_journey_type'] = $this->journeyType;
        if($this->vehicleType == '接驳交通' && $this->vehicleCategory == '联程交通'){
            $wasteHour = $this->post('waste_hour');
            $wasteMinutes = $this->post('waste_minutes');
            $baseData['connect_take_time'] = $wasteHour . ',' . $wasteMinutes;
        }
        $minPassengers = $this->post('min_passengers');
        $maxPassengers = $this->post('max_passengers');
        $baseData['passengers_range'] = (integer)$minPassengers . ',' . (integer)$maxPassengers;
        $baseData['pricing_method']= $pricingMethod = $this->post('pricing_method');
        if ($pricingMethod == '单人'){
            $baseData['currency_unit']= $this->post('currency');
            $baseData['age_range_for_hotel']= $this->post('age_range_for_hotel');
            $baseData['adult_fare']= $this->post('adult_fare');
            $baseData['child_fare']= $this->post('child_fare');
            $baseData['infant_fare']= $this->post('infant_fare');
            if($baseData['age_range_for_hotel'] == '根据酒店'){
                $ageRange = $this->getHotelAgeRange();
            }else{
                $ageRange = $this->formatInputAgeRange();
            }
            $baseData = array_merge($baseData,$ageRange);
        }elseif($pricingMethod == '单载体'){
            $baseData['currency_unit']= $this->post('currency');
            $baseData['rental_fare']= $this->post('rental_fare');
            $baseData['seating_capacity']= $this->post('seating_capacity');
        }
        return $baseData;
    }

    public function formatInputAgeRange()
    {
        $minAdultAge = $this->post('min_adult_age');
        $maxAdultAge = $this->post('max_adult_age');
        $ageRange['adult_age_range'] = $minAdultAge . ',' . $maxAdultAge;
        $minChildAge = $this->post('min_child_age');
        $maxChildAge = $this->post('max_child_age');
        $ageRange['child_age_range'] = $minChildAge . ',' . $maxChildAge;
        $minInfantAge = $this->post('min_infant_age');
        $maxInfantAge = $this->post('max_infant_age');
        $ageRange['infant_age_range'] = $minInfantAge. ',' . $maxInfantAge;
        $ageRange['infant_age_unit'] = $this->post('infant_age_unit','岁');
        return $ageRange;
    }

    public function getHotelAgeRange()
    {
        if($hotelModel = HotelModel::get($this->hotelId)){
            return $hotelModel->age_range;
        }
        exception('酒店年龄定义获取失败');
    }

    public function query($return = [])
    {
        $where['hotel_id'] = $this->hotelId;
        $type = $this->post('type');
        $method = $this->post('method');
        $week = $this->post('week');
        $week = $this->changeWeekType($week);
        if($type == '联程交通') {
            $where['vehicle_category'] = '联程交通';
            if (isset($week)){
                $where['connect_departure_week']=['like',"%".$week."%"];
            }
            $vehicleBaseModel = new VehicleBaseModel();
            $data = $vehicleBaseModel->where($where)->select();
            foreach ($data as $index => $datum) {
                $return[$index]['type'] = '联程交通';
                $return[$index]['trf_id'] = $datum->id;
                $return[$index]['journey_type'] = $datum->connect_journey_type;
                foreach ($datum->connectVehicle as $i => $item) {
                    $return[$index]['list'][] = $this->formatSingleNodeInfo($item);
                }
            }
        }else{
            $where['vehicle_category'] = '单程交通';
            if (isset($week) && $type == '定期交通'){
                $vehicleTimeModel = new VehicleTimeModel();
                $vehicleIdArray = $vehicleTimeModel->where($where)
                    ->where('departure_week','like',"%".$week."%")
                    ->group('vehicle_id')
                    ->column('vehicle_id');
                $where['id'] = ['IN',$vehicleIdArray];
            }
            $where['vehicle_type'] = $type;
            if (isset($method)){
                $where['vehicle_name'] = $method;
            }
            $data = VehicleModel::where($where)->select();
            foreach ($data as $index => $datum) {
                $return[$index] = $this->formatSingleNodeInfo($datum);
            }
        }
        return getSucc($return);
    }

    public function formatSingleNodeInfo($model)
    {
        $return['trf_id'] = $model->id;
        $return['method'] = $model->vehicle_name;
        $return['type'] = $model->vehicle_type;
        $return['departure_name'] = $model->departure_place_name;
        $return['departure_city'] = $model->departure_city;
        $return['destination_city'] = $model->destination_city;
        $return['departure_ename'] = $model->departure_place_ename;
        $return['destination_name'] = $model->destination_name;
        $return['destination_ename'] = $model->destination_ename;
        $return['journey_type'] = $model->single_journey_type;
        $return['waste_minutes'] = $model->waste_minutes;
        $return['waste_hours'] = $model->waste_hour;
        return $return;
    }

    public function changeWeekType($week){
            switch (strtolower($week)) {
                case 'mon':
                    return '星期一';
                case 'tue':
                    return '星期二';
                case 'wed':
                    return '星期三';
                case 'thu':
                    return '星期四';
                case 'fri':
                    return '星期五';
                case 'sat':
                    return '星期六';
                case 'sun':
                    return '星期日';
                    break;
            }
    }

}
