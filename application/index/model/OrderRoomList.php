<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-02-27
 * Time: 12:22
 */

namespace app\index\model;


class OrderRoomList extends BaseModel
{
    protected $table = 'ims_order_room_list';

    protected function orderRoom()
    {
        return $this->belongsTo('OrderRoom','order_room_id');
    }
}