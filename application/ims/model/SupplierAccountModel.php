<?php
namespace app\ims\model;
use think\Validate;

class SupplierAccountModel extends BaseModel
{
        public $table = 'ims_supplier_account';
        public $connection = 'ims_new';

        public $rule = [
            'user_name|用户名' => 'require',
            'password|密码' => 'require',
        ];







}
















