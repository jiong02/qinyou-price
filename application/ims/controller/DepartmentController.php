<?php

namespace app\ims\controller;

use app\ims\model\DepartmentModel;
use think\Request;

class DepartmentController extends PrivilegeController
{
    public function getAllDepartmentName()
    {
        $departmentModel = new DepartmentModel();
        $result = $departmentModel->field('id,department_name')->select();
        return getSucc($result);
    }

    public function addDepartmentData(Request $request)
    {
        $inputData['superior_id'] = $request->param('superior_id',0);
        $inputData['department_name'] = $request->param('department_name');
        if(DepartmentModel::create($inputData)){
            return getSucc('部门新增成功!');
        }
        return getErr('部门新增失败!');
    }
}
