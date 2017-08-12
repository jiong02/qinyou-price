<?php
namespace app\ims\model;

class ContractSeasonModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_contract_season';

    public function base($query)
    {
        $query->where('ims_contract_season.status',1);
    }

    public function getContractIdColumnByHotelId($hotelId)
    {
        return $this->where('hotel_id',$hotelId)->group('contract_id')->column('contract_id');
    }
}