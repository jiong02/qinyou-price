<?php
namespace app\route\model;
use think\Model;

class RouteHotelRoomModel extends Model
{
    public $table = 'ims_route_hotel_room';

    public $rule = [
        'route_id|线路' => 'number',
        'hotel_id|酒店' => 'number',
        'room_id|房型' => 'number',
        'check_in_night_amount|入住的晚数' => 'number',
    ];




}
?>