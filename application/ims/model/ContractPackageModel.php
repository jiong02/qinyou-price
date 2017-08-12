<?php
namespace app\ims\model;

class ContractPackageModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_contract_package';

    public function base($query)
    {
        $query->where('ims_contract_package.status',1);
    }
}