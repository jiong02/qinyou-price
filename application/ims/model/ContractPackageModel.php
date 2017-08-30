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


    public $baseHidden = ['create_time','modify_time'];

    public function __construct($data = [])
    {
        $this->hidden = array_merge($this->hidden, $this->baseHidden);
        parent::__construct($data);
    }
}