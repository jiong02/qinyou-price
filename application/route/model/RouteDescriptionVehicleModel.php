<?php
namespace app\route\model;

use think\Model;

class RouteDescriptionVehicleModel extends Model
{
    public $table = 'ims_route_description_vehicle';

    public $rule = [
        'description_id|描述' => 'number|require',
        'route_vehicle_id|线路交通' => 'number|require',
        'vehicle_name|交通名称' => 'number|require',
        'description_start_time|开始时间' => 'dateFormat:m-d',
    ];


}

?>