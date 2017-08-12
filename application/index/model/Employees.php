<?php
namespace app\index\model;

class Employees extends BaseModel
{
    protected $table = 'ims_employees';

    protected function account()
    {
        return $this->belongsTo('Account','emp_sn');
    }

    protected function dept()
    {
        return $this->belongsTo('Departments','dept_id');
    }

    protected function title()
    {
       return $this->belongsTo('Titles','title_id');
    }

}