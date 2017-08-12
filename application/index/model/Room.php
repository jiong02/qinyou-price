<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-02-25
 * Time: 16:02
 */

namespace app\index\model;


class Room extends BaseModel
{
    protected $connection = 'test_input';
    protected $table = 'contract_room';

    protected function package()
    {
        return $this->belongsTo('ContractPackage','package_id');
    }

    protected function room()
    {
        return $this->belongsTo('HotelApt','apt_id');
    }

    protected function roomFav()
    {
        return $this->belongsTo('ContractRoomFav','room_id');
    }

}