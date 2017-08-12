<?php

namespace app\ims\model;

/**
* 模型字段列表
* @param id ''
* @param hotel_id '酒店id'
* @param room_id '房型id'
* @param expired_year '有效年'
* @param expired_month '有效月'
* @param expired_day '有效日'
* @param room_amount '房型数量'
 */

class RoomModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_room';
    protected $resultSetType = 'collection';
    protected $uniqueIndex = ['room_id','expired_year','expired_month','expired_day'];
}