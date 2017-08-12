<?php
namespace app\ims\model;

class ContractRoomModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_contract_room';
/*    protected $type = [
        'adult_fare'  =>  'array',
        'child_fare'  =>  'array',
        'baby_fare'  =>  'array',
        'extra_adult_fare'  =>  'array',
        'extra_child_fare'  =>  'array',
        'room_price'  =>  'array',
    ];*/

    public function base($query)
    {
        $query->where('ims_contract_room.status',1);
    }
}