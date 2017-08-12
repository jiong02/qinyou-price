<?php
namespace app\route\model;

use think\Model;

class RouteDescriptionModel extends Model
{
    public $table = 'ims_route_description';

    public $rule = [
        'route_id|线路' => 'number|require',
        'departure_place_name|出发地' => 'require',
        'package_day|行程日期' => 'number|require',
        'package_name|使用套餐' => 'alphaNum|require',
    ];


}




?>