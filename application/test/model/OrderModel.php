<?php
namespace app\test\model;
use app\test\model\BaseModel;
use think\Exception;
use think\Model;

class OrderModel extends Model
{
    public $table = 'cheeru_order';


    protected $connection  = [
        'datetime_format' => false,
    ];

    public $createOrder = [
        'id|订单' => 'number',
        'order_name|线路名称' => 'require',
        'route_id|线路' => 'require|number',
        'trip_date|出行日期' => 'require|date',
        'room_number|房间数量' => 'require|number',
        'adult_number|成人数量' => 'require|number',
        'child_number|儿童数量' => 'require|number',
        'adult_price|成人价格' => 'require|number',
        'child_price|儿童价格' => 'require|number',
        'update_total_price|总价格' => 'require|number',
        'create_order_people_id|创建人' => 'require|number',
        'take_charge_people_id|跟单人' => 'number',
    ];

    public $linkman = [
        'linkman_name|联系人' => 'require',
        'linkman_phone|联系人电话' => 'require|number',
    ];

    public $scene = [
        'create_order' =>['id','route_name','route_id','trip_date','room_number','adult_number','child_number','adult_price','child_price','update_total_price','create_order_people_id'],
        'linkman' => ['linkman_name','linkman_phone'],
    ];

    public function updateOrderStatus($orderPayId, $customerId, $status)
    {
        $result = $this->where('order_pay_id',$orderPayId)->where('create_order_people_id',$customerId)->find();
        if(empty($result)){
            throw new Exception('当前订单不存在!');
        }
        $result = $this->where('order_pay_id',$orderPayId)->update(['order_status'=> $status]);
        if ($result){
            return true;
        }
        return false;



    }


}

?>