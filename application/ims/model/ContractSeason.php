<?php
namespace app\ims\model;

class ContractSeason extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_contract_season';

    public function base($query)
    {
        $query->where('ims_contract_season.status',1);

    }
}