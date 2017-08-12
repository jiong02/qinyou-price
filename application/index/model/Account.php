<?php
namespace app\index\model;

class Account extends BaseModel
{
    protected $table = 'internal_management_system.ims_emp_account';

    protected function employees()
    {
       return $this->hasOne('Employees','emp_sn');
    }
}