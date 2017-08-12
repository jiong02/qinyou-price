<?php

namespace app\index\model;

class OrderTrfCust extends BaseModel
{
    protected $table = 'ims_order_traffic_customers';

    protected function cust()
    {
        return $this->belongsTo('Cust','cust_id');
    }
}
