<?php

namespace app\ims\model;

/**
* 模型字段列表
* @param id '订单id'
* @param order_sn '订单编号'
* @param employees_sn '所属员工编号'
* @param hotel_id '所属酒店id'
* @param order_name '订单名称'
* @param order_type '订单类型'
* @param order_source '订单来源'
* @param departure_date '出发日期'
* @param back_departure_date '返回出发地日期'
* @param passenger_amount '出行人数'
* @param passenger_representative_name '客户联系人姓名'
* @param passenger_representative_phone '客户联系人电话'
* @param itinerary_day_amount '行程天数'
* @param itinerary_amount '行程总数'
 */

class OrderModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_order';
    protected $insert = ['itinerary_day_amount','order_name','order_sn'];
    protected $append = ['dest','country','route','message','date'];
    protected $mapFields = [
        'employee_sn'=>'emp_sn',
        'order_type'=>'type_value',
        'order_source'=>'source_value',
        'departure_date'=>'set_off_date',
        'back_departure_date'=>'back_date',
        'passenger_amount'=>'amount',
        'itinerary_amount'=>'route_amount',
        'itinerary_days_amount'=>'days',
        'place_id'=>'islands_id',
        'passenger_representative_name'=>'contact',
        'passenger_representative_phone'=>'phone',
    ];
    protected function getDateAttr($value,$data)
    {
        $return = [];
        $date = get_date_from_range($data['departure_date'],$data['back_departure_date']);
        foreach ($date as $index => $item) {
            $return[] = get_week_and_day($item);
        }
        return $return;
    }

    protected function getCountryAttr($value,$data)
    {
        return explode('-',$data['order_name'])[0];
    }

    protected function getDestAttr($value,$data)
    {
        return explode('-',$data['order_name'])[1];
    }

    protected function getRouteAttr($value,$data)
    {
        $return = [];
        $itineraryId = ItineraryDateModel::getItineraryIdByOrderId($data['id']);
        foreach ($itineraryId as $index => $item) {
            $return[$index]['id'] = $item;
        }
        return $return;
    }

    protected function getMessageAttr($value,$data)
    {
        return '资料已完善';
    }

    protected function setItineraryDayAmountAttr($value,$data)
    {
        $dayAmount = get_day_amount($data['departure_date'],$data['back_departure_date']);
        return $dayAmount;
    }

    protected function setOrderNameAttr($value,$data)
    {
        $orderName = $data['country_name'] .'-'.$data['islands_name'].'-'.$data['hotel_name'].'-'.$data['itinerary_day_amount'];
        unset($this->country_name,$this->islands_name,$this->hotel_name);
        return $orderName;
    }

    public function setOrderSnAttr($value,$data)
    {
        date_default_timezone_set('PRC');
        $count = $this->whereTime('create_time', '>=', date('Y-m-d'))->count();
        $countryId = zero_fill($data['country_id'], 3);
        $placeId = zero_fill($data['place_id'], 3);
        $count = zero_fill($count++, 4);
        unset($this->country_id,$this->place_id);
        return $this->getType($data['order_type']) . $this->getType($data['order_source']) . $countryId . $placeId . date('ymd') . $count;
    }

    public function getType($type)
    {
        switch ($type) {
            case '散单':
                return 'A';
            case '团单':
                return 'B';
            case 'OTA':
                return 'C';
            case '市场':
                return 'M';
            case '直客':
                return 'D';
        }
    }
}