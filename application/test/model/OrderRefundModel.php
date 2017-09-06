<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/9/5
 * Time: 14:11
 */

namespace app\test\model;


class OrderRefundModel extends BaseModel
{
    protected $table = 'cheeru_order_refund';

    public function getRefundOrderId($refundOrderId)
    {
        $count = $this->where('refund_order_id',$refundOrderId)->count();
        if ($count){
            return true;
        }
        return false;
    }

    public function orderModel()
    {
        return $this->belongsTo('OrderModel','order_id');
    }
}