<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-04-13
 * Time: 17:09
 */

namespace app\ims\model;


class VehicleBaseModel extends BaseModel
{
    protected $table = 'ims_vehicle_base';
    protected $connection = 'ims_new';
    protected $append = ['min_adult_age', 'max_adult_age', 'min_child_age', 'max_child_age', 'min_infant_age', 'max_infant_age',];
    protected $mapFields = [
        'connect_departure_week'=>'week',
        'connect_take_time'=>'take_time',
        'connect_journey_type'=>'journey_type',
        'currency_unit'=>'currency',
    ];

    protected function connectVehicle()
    {
        return $this->hasMany('VehicleModel','vehicle_base_id');
    }

    protected function vehicleBase()
    {
        return $this->belongsTo('VehicleModel','vehicle_base_id');
    }

    protected function getMinPassengersAttr($value,$data)
    {
        return explode(',',$data['passengers_range'])[0];
    }

    protected function getMaxPassengersAttr($value,$data)
    {
        return explode(',',$data['passengers_range'])[1];
    }

    protected function getWasteHourAttr($value,$data)
    {
        if(!empty($data['connect_take_time'])){
            return explode(',',$data['connect_take_time'])[0];
        }
        return '';
    }
    protected function getWasteMinutesAttr($value,$data)
    {
        if(!empty($data['connect_take_time'])){
            return explode(',',$data['connect_take_time'])[1];
        }
        return '';
    }
    protected function getMinAdultAgeAttr($value,$data)
    {
        if(!empty($data['adult_age_range'])){
            return explode(',',$data['adult_age_range'])[0];
        }
        return '';
    }
    protected function getMaxAdultAgeAttr($value,$data)
    {
        if(!empty($data['adult_age_range'])){
            return explode(',',$data['adult_age_range'])[1];
        }
        return '';
    }
    protected function getMinChildAgeAttr($value,$data)
    {
        if(!empty($data['child_age_range'])){
            return explode(',',$data['child_age_range'])[0];
        }
        return '';
    }
    protected function getMaxChildAgeAttr($value,$data)
    {
        if(!empty($data['child_age_range'])){
            return explode(',',$data['child_age_range'])[1];
        }
        return '';
    }
    protected function getMinInfantAgeAttr($value,$data)
    {
        if(!empty($data['infant_age_range'])){
            return explode(',',$data['infant_age_range'])[0];
        }
        return '';
    }
    protected function getMaxInfantAgeAttr($value,$data)
    {
        if(!empty($data['infant_age_range'])){
            return explode(',',$data['infant_age_range'])[0];
        }
        return '';
    }

    protected function getDataAttr($value,$data)
    {
        $return['base_id'] = $data['id'];
        $return['hotel_id'] = $data['hotel_id'];
        $return['trf_id'] = $data['vehicle_id'];
        $return['category'] = $data['vehicle_category'];
        $return['journey_type'] = $data['connect_journey_type'];
        $return['age_range_for_hotel'] = $data['age_range_for_hotel'];
        $return['pricing_method'] = $pricingMethod = $data['pricing_method'];
        $return['min_passengers'] = $this->getAttr('min_passengers');
        $return['max_passengers'] = $this->getAttr('max_passengers');
        if($data['vehicle_category'] == '联程交通') {
            $return['waste_hour'] = $this->getAttr('waste_hour');
            $return['waste_minutes'] = $this->getAttr('waste_minutes');
        }
        $return['currency'] = $data['currency_unit'];
        if ($pricingMethod == '单人'){
            $return['min_adult_age'] = $this->getAttr('min_adult_age');
            $return['max_adult_age'] = $this->getAttr('max_adult_age');
            $return['min_child_age'] = $this->getAttr('min_child_age');
            $return['max_child_age'] = $this->getAttr('max_child_age');
            $return['min_infant_age'] = $this->getAttr('min_infant_age');
            $return['max_infant_age'] = $this->getAttr('max_infant_age');
            $return['infant_age_unit'] = $data['infant_age_unit'];
            $return['age_range_for_hotel'] = $data['age_range_for_hotel'];
            $return['adult_fare'] = $data['adult_fare'];
            $return['child_fare'] = $data['child_fare'];
            $return['infant_fare'] = $data['infant_fare'];
        }elseif($pricingMethod == '单载体'){
            $return['rental_fare'] = $data['rental_fare'];
            $return['seating_capacity'] = $data['seating_capacity'];
        }
        return $return;
    }

    protected function getConnectBaseDataAttr($value,$data)
    {
        $return['start'] = $this->departure_city;
        $return['end'] = $this->destination_city;
        $return['methods'] = $data['vehicle_name'];
        $return['trf_id'] = $data['id'];
        $return['type'] = $data['vehicle_type'];
        $return['days'] = '每天';
        if($data['vehicle_type'] == '定期交通') {
            $days = VehicleTimeModel::where('vehicle_detail_id',$data['id'])->group('departure_week')->column('departure_week');
            $return['days'] = implode(',',$days);
        }
        return $return;
    }

}