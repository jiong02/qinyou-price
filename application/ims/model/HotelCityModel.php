<?php

namespace app\ims\model;

/**
* 模型字段列表
* @param id '城市id'
* @param hotel_id '酒店id'
* @param city_name '城市名称'
 */

class HotelCityModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_hotel_city';
    protected $mapFields = [
        'id'=>'city_id',
    ];

    protected function vehicle()
    {
        return $this->hasMany('VehicleCityModel','hotel_city_id');
    }
}