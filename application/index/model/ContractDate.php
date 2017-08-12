<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-02-25
 * Time: 17:56
 */

namespace app\index\model;


class ContractDate extends BaseModel
{
    protected $connection = 'test_input';
    protected $table = 'contract_date';

    protected function getCheckInAttr($value,$data)
    {
        $checkIn = json_decode($data['dates'])[0];
        return strtotime(explode('~',$checkIn)[0]);
    }

    protected function getCheckOutAttr($value,$data)
    {
        $checkOut = json_decode($data['dates'])[0];
        return strtotime(explode('~',$checkOut)[0]);
    }
}