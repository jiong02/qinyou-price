<?php

namespace app\index\model;

class OrderTrf extends BaseModel
{
    protected $table = 'ims_order_traffic';
    public $public = [];
    public $singe = [];
    public function trfSingle()
    {
        return $this->belongsTo('Trf','trf_id');
    }

    public function getAllByitinId($itinId)
    {
        return $this->where('itin_id',$itinId)->select();
    }

    public static function getSingleOrMultiple($trfId)
    {
        if ($trf = TrfSingle::getByTrfId($trfId) || $trf = TrfMultiple::getByTrfId($trfId)) {

            return $trf;

        }

        return false;
    }

    public function getTrfSupplyNewAttr($value,$data)
    {
        if ($data['trf_supply'] == '代订') {

            return true;

        }elseif($data['trf_supply'] == '不代订'){

            return false;

        }
    }

    public function getBillAttr($bill)
    {
        return json_decode($bill,true);
    }
    public function getTrfAllocationAttr($trfAllocation)
    {
        return json_decode($trfAllocation,true);
    }
    public function getTrfAllocationNewAttr($value,$data)
    {
        $trfAllocation = json_decode($data['trf_allocation'],true);
        if (is_array($trfAllocation)) {
            return array_map(function($v){ return ['value'=> $v ];},$trfAllocation);
        }else{
            return [];
        }
    }
}
