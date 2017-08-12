<?php

namespace app\ims\model;

/**
* 模型字段列表
* @param id '部门id'
* @param department_name '部门名称'
* @param superior_id '上级部门id'
 */

class DepartmentModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_department';
}