<?php

namespace app\ims\model;

/**
* 模型字段列表
* @param id '员工id'
* @param employee_sn '员工编号'
* @param department_id '部门id'
* @param title_id '职务id'
* @param employee_name '员工中文名'
* @param employee_ename '员工英文名'
* @param employee_gender '员工性别'
* @param employee_avatar '员工头像'
* @param employee_telephone '员工座机'
* @param employee_cellphone '员工手机'
* @param employee_personal_email '个人邮箱'
* @param employee_company_email '公司邮箱'
* @param employee_birthday '出生日期'
* @param employee_hire_date '入职日期'
 */

class EmployeeModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_employee';
    protected $resultSetType = 'collection';

    public function account()
    {
        return $this->belongsTo('EmployeeAccountModel','account_id');
    }

    public function title()
    {
        return $this->belongsTo('TitleModel','title_id');
    }

    public function department()
    {
        return $this->belongsTo('DepartmentModel','department_id');
    }
}