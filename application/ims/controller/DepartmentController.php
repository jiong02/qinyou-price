<?php

namespace app\ims\controller;

use app\ims\model\DepartmentModel;
use think\Request;

class DepartmentController extends PrivilegeController
{
    public function getAllDepartmentNameByDepartmentId(Request $request)
    {
        $departmentId = $request->param('department_id');
        $departmentModel = new DepartmentModel();
        $allDepartmentName = $departmentModel->field('id, superior_id, department_name')->select();
        $departmentNameSet = $this->tree($allDepartmentName,$departmentId);
        if (count($departmentNameSet) == 0){
            $departmentNameSet[] = $departmentModel->get($departmentId);
            if (!$departmentNameSet) {
                return getError('部门列表获取失败!');
            }
        }
        return getSuccess($departmentNameSet);
    }

    public function tree($data, $pid)
    {
        $treeArray = array();
        foreach ($data as $v)
        {
            if ($v['superior_id'] == $pid)
            {
                $treeArray[] = $v;
                $this->tree($data, $v['id']);
            }
        }
        return $treeArray;
    }

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
