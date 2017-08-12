<?php

namespace app\ims\model;

/**
* 模型字段列表
* @param id '账户id'
* @param employee_sn '员工编号'
* @param account_name '账户名'
* @param account_password '账户密码'
* @param account_salt '账户加密盐'
* @param login_ip '登录ip'
* @param login_times '登录次数'
* @param login_time '登录时间'
 */

class EmployeeAccountModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_employee_account';

    public function employee()
    {
        return $this->belongsTo('EmployeeModel','account_id');
    }
}