<?php
namespace app\ims\model;
use think\Validate;

class SupplierAccountDataModel extends BaseModel
{
    public $table = 'ims_supplier_account_data';
    public $connection = 'ims_new';



    public $rule = [
        'company_name|企业名称' => 'require',
        'representative|法定代表人' => 'require',
        'travel_code|旅行社经营许可编号' => 'require',
        'mobile_phone|联系电话' => 'require|number',
        'email|电子邮件' => 'require|email',
        'address|联系地址' => 'require',
        'fix_phone|固话' => 'require',
        'grade|渠道商等级' => 'number',
    ];



}




















