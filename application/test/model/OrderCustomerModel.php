<?php
namespace app\test\model;
use app\test\model\BaseModel;

class OrderCustomerModel extends BaseModel
{
    public $table = 'cheeru_order_customer';

    public $rules = [
            'id|ID' => 'number',
            'order_id|订单' => 'require|number',
            'customer_name|客户中文名' => 'require',
            'customer_passport|护照' => 'require',
            'validity_of_passport|护照有效期' => 'require',
            'customer_nationality|国籍' => 'require',
            'place_of_issue|签发地点' => 'require',
            'customer_phone|客户手机号码' => 'number',
    ];





}
?>