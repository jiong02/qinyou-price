<?php

namespace app\index\model;

class OrderItin extends BaseModel
{
    protected $table = 'ims_order_itinerary';

    protected function order()
    {
        return $this->belongsTo('Order','order_id');
    }

    protected function cust()
    {
        return $this->hasMany('Cust','itin_id');
    }

    protected function orderTrf()
    {
        return $this->hasMany('OrderTrf','itin_id');
    }
}
