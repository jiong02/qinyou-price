<?php

namespace app\ims\model;

/**
* 模型字段列表
 */

class HotelDefaultVehicleModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_hotel_default_vehicle';

    public function getDefaultGoVehicleAttr($value)
    {
        return json_decode($value);
    }

    public function getDefaultBackVehicleAttr($value)
    {
        return json_decode($value);
    }
}