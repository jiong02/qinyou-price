<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-03-11
 * Time: 15:03
 */

namespace app\index\model;


class OrderRoomCust extends BaseModel
{
    protected $table = 'ims_order_room_customers';

    protected function cust()
    {
        return $this->belongsTo('Cust','cust_id');
    }
}