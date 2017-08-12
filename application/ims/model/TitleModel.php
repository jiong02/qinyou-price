<?php

namespace app\ims\model;

/**
* 模型字段列表
* @param id '职务id'
* @param department_id '部门id'
* @param title '职务'
* @param is_charge '是否主管'
* @param permissions '权限值'
 */

class TitleModel extends BaseModel
{
    protected $connection = 'ims_new';
    protected $table = 'ims_title';
}