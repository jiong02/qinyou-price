<?php
namespace app\ims\model;

class VehicleModel extends BaseModel
{
    protected $table = 'ims_vehicle';
    protected $connection = 'ims_new';
    protected $mapFields = [
        'id'=>'trf_id',
        'vehicle_base_id'=>'base_id',
        'vehicle_name'=>'name',
        'vehicle_type'=>'type',
        'vehicle_category'=>'category',
        'single_journey_type'=>'journey_type',
        'transfer_take_time'=>'take_time',
    ];
    protected $append = [
        'waste_hour','waste_minutes',
        'min_passengers','max_passengers',
    ];

    public $nav = [
        'type',
        'trf_id',
        'name',
        'departure_city',
        'destination_city',
    ];

    public $banner = [
        'type',
        'name',
        'trf_id',
    ];

    public function singleVehicleBase()
    {
        return $this->hasOne('VehicleBaseModel','vehicle_id');
    }

    public function singleBase()
    {
        return $this->belongsTo('VehicleBaseModel','vehicle_base_id');
    }

    public function vehicleTime()
    {
        return $this->hasOne('VehicleTimeModel','vehicle_id');
    }

    protected function getWasteHourAttr($value,$data)
    {
        if(!empty($data['transfer_take_time'])){
            return explode(',',$data['transfer_take_time'])[0];
        }
        return '';
    }

    protected function getMinPassengersAttr($value,$data)
    {
        if($data['vehicle_category'] == '单程交通') {
            return $this->singleVehicleBase->min_passengers;
        }
        return '';
    }

    protected function getMaxPassengersAttr($value,$data)
    {
        if($data['vehicle_category'] == '单程交通') {
            return $this->singleVehicleBase->max_passengers;
        }
        return '';
    }

    protected function getWasteMinutesAttr($value,$data)
    {
        if(!empty($data['transfer_take_time'])){
            return explode(',',$data['transfer_take_time'])[1];
        }
        return '';
    }

    protected function getConnectWasteHourAttr($value,$data)
    {
        return $this->vehicleBase->waste_hour;
    }

    protected function getConnectWasteMinutesAttr($value,$data)
    {
        return $this->vehicleBase->waste_minutes;
    }

    protected function getDataAttr($value,$data)
    {
        $return['trf_id'] = $data['id'];
        $return['hotel_id'] = $data['hotel_id'];
        $return['base_id'] = $data['vehicle_base_id'];
        $return['name'] = $data['vehicle_name'];
        $return['type'] = $data['vehicle_type'];
        $return['category'] = $data['vehicle_category'];
        $return['journey_type'] = $data['single_journey_type'];
        $return['departure_city'] = $data['departure_city'];
        $return['departure_place_name'] = $data['departure_place_name'];
        $return['departure_place_ename'] = $data['departure_place_ename'];
        $return['destination_city'] = $data['destination_city'];
        $return['destination_name'] = $data['destination_name'];
        $return['destination_ename'] = $data['destination_ename'];
        if($data['vehicle_category'] == '单程交通') {
            $return['waste_hour'] = $this->getAttr('waste_hour');
            $return['waste_minutes'] = $this->getAttr('waste_minutes');
        }
        return $return;
    }

    protected function getBaseDataAttr($value,$data)
    {
        if($data['vehicle_category'] == '单程交通'){
            if (!is_null($this->singleBase)){
                $return['min'] = $this->singleBase->min_passengers;
                $return['max'] = $this->singleBase->max_passengers;
            }
        }
        $return['start'] = $data['departure_city'];
        $return['end'] = $data['destination_city'];
        $return['methods'] = $data['vehicle_name'];
        $return['trf_id'] = $data['id'];
        $return['type'] = $data['vehicle_type'];
        $return['days'] = '每天';
        if($data['vehicle_type'] == '定期交通') {
            $week = [];
            $weekColumn = VehicleTimeModel::where('vehicle_id',$data['id'])->column('departure_week');
            foreach ($weekColumn as $index => $item) {
                $ret = explode(',',$item);
                $week = array_merge($week,$ret);
            }
            $week  = array_unique($week);
            $return['days'] = implode(',',$week);
        }
        return $return;
    }
}