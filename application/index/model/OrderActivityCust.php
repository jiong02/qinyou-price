<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-03-13
 * Time: 23:42
 */

namespace app\index\model;


class OrderActivityCust extends BaseModel
{
    protected $table = 'ims_order_activity_customers';

    protected function getStateAttr($value,$data)
    {
        switch ($data['activity_status']) {
            case '未完善':
                return 0;
            case '完善中':
                return 1;
            case '已完善':
                return 2;
        }
    }

    protected function activity()
    {
        return $this->belongsTo('HotelAct','activity_id');
    }

    protected function cust()
    {
        return $this->belongsTo('Cust','cust_id');
    }
}