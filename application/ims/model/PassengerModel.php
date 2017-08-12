<?php

namespace app\ims\model;

/**
* 模型字段列表
* @param id '客户id'
* @param order_id '所属订单id'
* @param itinerary_id '路线id'
* @param passenger_name '客户姓名'
* @param passport_no '护照号'
* @param gender '客户性别'
* @param age '年龄'
* @param birthday '出生日期'
 */

class PassengerModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_passenger';
    protected $append = ['age','state'];
    protected $mapFields = [
        'birthday' =>'birth',
	    'passenger_name' =>'name',
	    'gender' =>'sex',
	    'passport_no' =>'passport',
	    'id' =>'cust_id',
	    'order_id' =>'id',
    ];
    protected $update = ['passenger_data_status'=>1];

    public function orderModel()
    {
        return $this->belongsTo('OrderModel','order_id');
    }

    protected function getAgeAttr($value,$data)
    {
        $age = get_age($data['birthday']);
        if ($data['birthday'] == '0000-00-00') {
            $age = 0;
        }
        return $age;
    }

    protected function getStateAttr($value,$data)
    {
        return $data['passenger_data_status'];
    }

}