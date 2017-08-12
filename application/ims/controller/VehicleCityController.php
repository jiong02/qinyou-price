<?php

namespace app\ims\controller;

use app\ims\model\HotelCityModel;
use app\ims\model\VehicleBaseModel;
use app\ims\model\VehicleCityModel;
use app\ims\model\VehicleModel;

class VehicleCityController extends VehicleBasicController
{
    /**
     * 新增城市信息
     */
    public function addCityData($data = [])
    {
        $hotelId = $this->hotelId;
        $cityName = json_decode($this->post('city_name'),true);
        foreach ($cityName as $index => $item) {
            $ret['hotel_id'] = $hotelId;
            $ret['city_name'] = $item;
            $data[] = $ret;
        }
        $vehicleCityModel = new HotelCityModel();
        if($vehicleCityModel->saveAll($data)){
            return getSucc('交通添加成功');
        }
        return getErr('交通城市添加失败');

    }

    /**
     * 删除城市线路信息
     * @return \think\response\Json
     */
    public function deleteCityRouteInfo()
    {
        $vehicleId = $this->vehicleId;
        $vehicleCityModel = VehicleCityModel::get($vehicleId);
        if($vehicleCityModel->delete()) {
            return getSucc('删除成功!');
        }
        return getSucc('删除失败!');
    }

    /**
     * 获取所有城市信息
     */
    public function getAllCityData($return = [])
    {
        $hotelId = $this->hotelId;
        $cityData = HotelCityModel::where('hotel_id',$hotelId)->select();
        foreach ($cityData as $index => $cityDatum) {
            $return[] = $cityDatum->formatOutPut();
        }
        return getSucc($return);
    }

    public function addCityRouteData()
    {
        $vehicleIdArray =  json_decode($this->vehicleId,true);
        $week = json_decode($this->post('week'),true);
        $vehicleBaseId = '';
        foreach ($vehicleIdArray as $index => $item) {
            $vehicleBaseId .= vehicleModel::get($item)->vehicle_base_id.',';
        }
        $vehicleBaseId = trim($vehicleBaseId,',');
        $data['vehicle_base_id'] = $vehicleBaseId;
        $data['hotel_city_id'] = $this->post('city_id');
        $data['city_departure_week'] = implode(',',$week);
        $data['city_journey_type'] = $this->journeyType;
        $data['min_passengers'] = $this->post('min');
        $data['max_passengers'] = $this->post('max');
        $data['hotel_id'] = $this->hotelId;
        $vehicleCityModel = new VehicleCityModel();
        if($vehicleCityModel->save($data)){
            return getSucc('数据新增完成');
        }
        return getErr('数据新增失败');
    }

    public function getCityRouteInfo()
    {
        $vehicleId = $this->vehicleId;
        $vehicleCityModel = VehicleCityModel::get($vehicleId);
        $return = $vehicleCityModel->hidden(['vehicle_base_id'])->formatOutPut();
        $vehicleModel = new VehicleModel;
        $baseData = $vehicleModel->where('vehicle_base_id','IN',$vehicleCityModel->vehicle_base_id)
            ->where('vehicle_category','单程交通')
            ->select();
        foreach ($baseData as $index => $baseDatum) {
            $banner = $baseDatum->formatData('nav');
            $return['banner'][] = $banner;
        }
        return getSucc($return);
    }

    public function queryCityList($return = [])
    {
        $cityId = $this->post('city_id');
        $week  = $this->post('week');
        $where['hotel_id'] = $hotelId = $this->hotelId;

        if(isset($this->journeyType) && !empty($this->journeyType)) {
            $where['city_journey_type'] = $this->journeyType;
        }
        if(isset($cityId)){
            $where['hotel_city_id'] = $cityId;
        }
        if(isset($week)){
            $where['city_departure_week'] = ['like',"%".$week."%"];
        }

        $cityName = HotelCityModel::get($cityId)->city_name;
        $vehicleCityModel = new VehicleCityModel();
        $vehicleModel = new VehicleModel;
        $cityData = $vehicleCityModel->where($where)->select();

//        halt($vehicleCityModel->where($where)->buildSql());
        foreach ($cityData as $index => $cityDatum) {
            $ret['type'] = $cityName . '往返';
            $ret['days'] = $cityDatum->city_departure_week;
            $ret['min'] = $cityDatum->min_passengers;
            $ret['max'] = $cityDatum->max_passengers;
            $ret['trf_id'] = $cityDatum->id;
            $ret['city_id'] = $cityDatum->hotel_city_id;
            $baseData = $vehicleModel->where('vehicle_base_id','IN',$cityDatum->vehicle_base_id)
                ->where('vehicle_category','单程交通')
                ->field('departure_place_name,destination_name')
                ->select();

/*            echo($vehicleModel->where('vehicle_base_id','IN',$cityDatum->vehicle_base_id)
                ->where('vehicle_category','单程交通')
                ->field('departure_place_name,destination_name')
                ->buildSql());

            halt($baseData);*/

            foreach ($baseData as $key => $value) {
                if($key === 0){
                    $ret['place'][] = ['name'=>$value->departure_place_name];
                }
                $ret['place'][] = ['name'=>$value->destination_name];
            }
//            halt($ret);
            $return[] = $ret;
        }
        return getSucc($return);
    }

    public function queryCityData($return = [])
    {
        $where['hotel_id'] = $hotelId = $this->hotelId;
        if(isset($this->journeyType) && !empty($this->journeyType)) {
            $where['city_journey_type'] = $this->journeyType;
        }
        $vehicleCityModel = new VehicleCityModel();
        $vehicleModel = new VehicleModel;
        $cityData = $vehicleCityModel->where($where)->select();
        foreach ($cityData as $index => $cityDatum) {
            $ret['journey_type'] =  '往返交通';
            $ret['week'] = $cityDatum->city_departure_week;
            $ret['min_passengers'] = $cityDatum->min_passengers;
            $ret['max_passengers'] = $cityDatum->max_passengers;
            $ret['trf_id'] = $cityDatum->id;
            $ret['city_id'] = $cityDatum->hotel_city_id;
            $baseData = $vehicleModel->where('vehicle_base_id','IN',$cityDatum->vehicle_base_id)
                ->where('vehicle_category','单程交通')
                ->field('departure_place_name,destination_name')
                ->select();
            foreach ($baseData as $key => $value) {
                if($key === 0){
                    $ret['place'][] = ['name'=>$value->departure_place_name];
                }
                $ret['place'][] = ['name'=>$value->destination_name];
            }
            $return[] = $ret;
            $ret['place'] = [];
        }
        return getSucc($return);
    }
}
