<?php

namespace app\ims\model;

/**
* 模型字段列表
* @param id '线路日期id'
* @param order_id '所属订单id'
* @param itinerary_id '所属线路id'
* @param itinerary_date '线路日期'
 */

class ItineraryDateModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_itinerary_date';

    public static function getItineraryIdByOrderId($orderId)
    {
        $itineraryId = self::where('order_id',$orderId)
            ->group('itinerary_id')
            ->column('itinerary_id');
        return $itineraryId;
    }
}