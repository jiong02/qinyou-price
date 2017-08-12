<?php
namespace app\index\model;

class Departments extends BaseModel
{
    protected $table = 'ims_departments';


    protected function employees()
    {
       return $this->hasOne('Employees','dept_id');
    }
}