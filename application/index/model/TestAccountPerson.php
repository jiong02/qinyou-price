<?php
namespace app\index\model;

use think\Model;

class TestAccountPerson extends Model
{
    public $table = 'test_account_trip_person';
    protected $connection = 'test_web';

    public $rule = [
        'rtip_person_name|常用人名称' => 'require',
        'trip_passport|护照号' => 'require|alphaNum',
        'passport_date|护照有效期' => 'require',
        'nationality|国籍' => 'require',
        'Place_of_issue|签发地点' => 'require',
        'trip_person_phone|电话号码' => 'require',

    ];





}






?>