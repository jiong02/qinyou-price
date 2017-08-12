<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-02-25
 * Time: 16:06
 */

namespace app\index\model;


class HotelApt extends BaseModel
{
    protected $connection = 'test_input';
    protected $table = 'hotel_apt';

    protected function getStandardAdultAttr($value,$data)
    {
        return $this->formatNum($data['num'])[0];
    }

    protected function getExtraAdultAttr($value,$data)
    {
        return $this->formatNum($data['num'])[1];
    }

    protected function getExtraChildAttr($value,$data)
    {
        return $this->formatNum($data['num'])[2];
    }

    public function formatNum($num)
    {
        $num = json_decode($num);
        return explode('-',end($num));
    }
}