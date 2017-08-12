<?php

namespace app\index\model;

class Order extends BaseModel
{
    protected $table = 'ims_order';

    protected function itin()
    {
        return $this->hasMany('OrderItin','order_id');
    }

    protected function cust()
    {
        return $this->hasMany('Cust','order_id');
    }

    protected function emp()
    {
        return $this->belongsTo('Employees','emp_sn');
    }

    protected function hotel()
    {
        return $this->belongsTo('Hotel','hotel_id');
    }

    /*获得订单信息
     * @param $userSn = 员工编号
     * @param $year = 2017 年
     * @param $month = 06 月
     */
    public function getOrderInfo($userSn,$hotelId,$year,$month)
    {
        $dateTime = $year.'-'.$month;
        $selfModel = new self();
        $orderInfo = array();
        $orderInfo = $selfModel
            ->field('emp_sn,start_date,end_date,order_name,order_type,order_source,dest_country,dest_islands,dest_hotel')
            ->where("emp_sn = '$userSn' AND hotel_id = '$hotelId' AND start_date like '$dateTime%'")
            ->select();

        $orderInfo = json_decode(json_encode($orderInfo),true);
        return $orderInfo;

    }

    //获得选择日的订单
    public function getNowOrderInfo($userSn,$hotelId,$dateTime)
    {
        $selfModel = new self();
        $orderInfo = array();
        $orderInfo = $selfModel
            ->field('emp_sn,start_date,end_date,order_name,order_type,order_source,dest_country,dest_islands,dest_hotel')
            ->where("emp_sn = '$userSn' AND hotel_id = '$hotelId' AND start_date = '$dateTime'")
            ->select();

        $orderInfo = json_decode(json_encode($orderInfo),true);
        return $orderInfo;

    }


}
