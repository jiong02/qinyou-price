<?php
namespace app\route\model;

use think\Model;

class RouteVehicleModel extends Model{
    public $table = 'ims_route_vehicle';

    public $rule = [
            'vehicle_id|交通标志' => 'number|require',
            'route_id|线路ID' => 'number',
            'departure_place_name|出发地' => 'require',
            'destination_name|目的地' => 'require',
            'package_vehicle_status|套餐状态' => 'number',
            'vehicle_category|交通分类' => 'in:单程交通,联程交通,往返交通',
    ];





}


?>