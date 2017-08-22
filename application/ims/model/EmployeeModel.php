<?php

namespace app\ims\model;

class EmployeeModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_employee';
    protected $resultSetType = 'collection';

    public function account()
    {
        return $this->hasOne('EmployeeAccountModel','account_name','account_name');
    }

    public function title()
    {
        return $this->belongsTo('TitleModel','title_id');
    }

    public function department()
    {
        return $this->belongsTo('DepartmentModel','department_id');
    }

    public function customTailor()
    {
        return $this->hasMany('CustomTailorModel','follow_up_employee_id');
    }

    public function checkExist($employeeId, $employeeToken)
    {
        $employeeCount = $this->where('id',$employeeId)->where('employee_token',$employeeToken)->count();
        if($employeeCount > 0 ){
            return true;
        }
        return false;
    }
}