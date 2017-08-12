<?php
namespace app\ims\model;

class ContractItemModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_contract_item';

    public function base($query)
    {
        $query->where('ims_contract_item.status',1);
    }
}