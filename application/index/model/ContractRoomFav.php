<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-03-06
 * Time: 15:53
 */

namespace app\index\model;


class ContractRoomFav extends BaseModel
{
    protected $connection = 'test_input';
    protected $table = 'contract_room_fav';

    protected function getNewNameAttr($value,$data)
    {
        return $data['name'].'('.$data['des'].')';
    }
}