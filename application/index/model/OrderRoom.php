<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-02-25
 * Time: 17:21
 */

namespace app\index\model;


class OrderRoom extends BaseModel
{
    protected $table = 'ims_order_room';

    protected function roomList()
    {
        return $this->hasMany('OrderRoomList','order_room_id');
    }
}