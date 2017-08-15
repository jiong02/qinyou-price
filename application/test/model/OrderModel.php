<?php
namespace app\test\model;
use app\test\model\BaseModel;


class OrderModel extends BaseModel
{
    public $table = 'cheeru_order';


    public $rules = [
        'id|订单' => 'number',
        'route_name|线路名称' => 'require',
        'route_id|线路' => 'require|number',
        'trip_date|出行日期' => 'require|date',
        'room_number|房间数量' => 'require|number',
        'adult_number|成人数量' => 'require|number',
        'child_number|儿童数量' => 'require|number',
        'adult_price|成人价格' => 'require|number',
        'child_price|儿童价格' => 'require|number',
        'total_price|总价格' => 'require|number',
        'create_order_people_id|创建人' => 'require|number',
        'take_charge_people_id|跟单人' => 'number',
        'linkman_name|联系人' => 'require',
        'linkman_phone|联系人电话' => 'require|number',
    ];

    /*protected $scene = [
        'order_linkman' => ['order_name','order_id','trip_date','room_number','adult_number','child_number','adult_price','child_price','create_order_people_id'],



    ];*/




}

?>