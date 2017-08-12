<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-03-13
 * Time: 20:23
 */

namespace app\index\model;


class HotelAct extends BaseModel
{
    protected $table = 'act';
    protected $connection = 'test_input';

    protected function getNewActTimeAttr($value,$data)
    {
        return json_decode($data['act_time_new'],true);
    }

    protected function getActDayAttr($value,$data)
    {
        $day = json_decode($data['act_time_new'],true)['day'];
        return explode('、',$day);
    }
}