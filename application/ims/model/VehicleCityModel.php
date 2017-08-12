<?php

namespace app\ims\model;

/**
* 模型字段列表
* @param 'id' '城市往返交通id'
* @param 'hotel_id' '酒店id'
* @param 'hotel_city_id' '酒店城市id'
* @param 'vehicle_base_id' '交通基本信息id集合,用逗号分隔'
* @param 'city_journey_type' '往返城市行程类型'
* @param 'city_departure_week' '往返城市适用出发周'
* @param 'city_passengers_range' '往返交通适用人数范围'
 */

class VehicleCityModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_vehicle_city';
    public $append = ['min_passengers','max_passengers'];
    public $hidden = ['create_time','modify_time','city_passengers_range'];
    public $auto = ['city_passengers_range'];

    protected function setCityPassengersRangeAttr($value,$data)
    {
        if(!empty($data['min_passengers']) && !empty($data['max_passengers'])){
            $cityPassengersRange = $data['min_passengers'] . ',' . $data['max_passengers'];
            unset($this->min_passengers,$this->max_passengers);
            return $cityPassengersRange;
        }
        return '';
    }

    protected function getMinPassengersAttr($value,$data)
    {
        if(!empty($data['city_passengers_range'])) {
            return explode(',',$data['city_passengers_range'])[0];
        }
        return '';
    }

    protected function getMaxPassengersAttr($value,$data)
    {
        if(!empty($data['city_passengers_range'])) {
            return explode(',',$data['city_passengers_range'])[1];
        }
        return '';
    }

    protected function getVehicleBaseIdAttr($value)
    {
        return explode(',',$value);
    }
}