<?php

namespace app\ims\model;


class VehicleTimeModel extends BaseModel
{
    protected $table = 'ims_vehicle_time';
    protected $connection = 'ims_new';
    protected $mapFields = [
        'in_time'=>'enter_time',
        'out_time'=>'leave_time',
        'departure_time'=>'start_time',
        'arrival_time'=>'end_time',
        'arrival_days'=>'days',
    ];
    public $shift = [
        'shift_id',
        'enter_time',
        'leave_time',
        'start_time',
        'end_time',
        'days',
    ];

    protected function vehicle()
    {
        return $this->belongsTo('VehicleModel','vehicle_id');
    }

    protected function getWeekAttr($value,$data)
    {
        return explode(',',$data['departure_week']);
    }

    protected function getStepShiftAttr($value,$data)
    {
        $scheduleId = $this->getAttr('schedule_id');
        $return = [];
        foreach ($scheduleId as $index => $scheduleDatum) {
            $ret = [];
            $result = $this->getDataByScheduleId($scheduleDatum);
            foreach ($result as $key => $item) {
                $ret['week'] = $item->week;
                $ret['schedule_id'] = $scheduleDatum;
                $show = false;
                if($key === 0){
                    $show = true;
                }
                $shift = $item->formatData('shift');
                $shift['show'] = $show;
                $ret['shift'][] = $shift;
            }
            $return[] = $ret;
        }

        return $return;
    }

    protected function getDataByScheduleId($scheduleId)
    {
        return $this->where('schedule_id',$scheduleId)->select();
    }

    protected function getScheduleIdAttr($value,$data)
    {
        return $this->where('vehicle_id',$data['vehicle_id'])
            ->group('schedule_id')
            ->column('schedule_id');
    }
}
