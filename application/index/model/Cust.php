<?php

namespace app\index\model;

class Cust extends BaseModel
{
    protected $table = 'ims_customers';

    protected function order()
    {
        return $this->belongsTo('Order','order_id');
    }

    protected function getNameAttr($value, $data)
    {
        return substr($data['cust_name'], 0, 3);
    }

    protected function getSexAttr($value, $data)
    {
        switch ($data['gender']) {
            case '男':
                $sex = 'M';
                break;
            case '女':
                $sex = 'F';
            break;
        }

        return $sex;
    }
}
