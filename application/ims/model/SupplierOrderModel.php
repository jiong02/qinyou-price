<?php
namespace app\ims\model;
use think\Model;
use app\ims\model\BaseModel;

class SupplierOrderModel extends BaseModel
{
    protected $table = 'ims_supplier_order';
    protected $connection = 'ims_new';

    public $rule = [
        'order_name|订单名称' => 'require',
        'package_unqid|套餐ID' => 'require',
        'contract_id|合同ID' => 'require|number',
        'hotel_id|酒店ID' => 'require|number',
        'room_id|房型ID' => 'require|number',
        'room_number|房间数量' => 'require|number',
    ];

    public $baseHidden = ['create_time','update_time'];

    public function __construct($data = [])
    {
        $this->hidden = array_merge($this->hidden, $this->baseHidden);
        parent::__construct($data);
    }

}
