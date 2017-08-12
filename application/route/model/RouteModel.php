<?php
namespace app\route\model;

use app\ims\model\BaseModel;

class RouteModel extends BaseModel
{
    public $table = 'ims_route';
//    protected $pk = 'id';

    public $rule = [
        'route_code|线路编码' => 'alphaNum',
        'destination_place_id' => 'number',
        'start_time|线路开始时间' => 'dateFormat:Y-m-d',
        'end_time|线路结束时间' => 'dateFormat:Y-m-d',
        'package_name|套餐名称' => 'alphaNum',
        'route_type|线路类型' => 'number',
        'max_passengers|最大人数' => 'number',
        'min_passengers|最少人数' => 'number',
//        'advance_book|预定人数' => 'number',
        'route_status|线路状态' => 'number',
        ];




}

?>