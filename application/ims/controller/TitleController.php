<?php

namespace app\ims\controller;

use app\ims\model\TitleModel;
use think\Request;

class TitleController extends PrivilegeController
{
    public function getAllTitle(Request $request,$departmentId)
    {
//        $departmentId = $request->param('department_id');
        $titleModel = new TitleModel();
        $result = $titleModel->where('department_id',$departmentId)->field('id,is_charge,title')->select();
        if ($result){
            return getSucc($result);
        }
        return getErr('数据查询失败');
    }

    public function addTitleData(Request $request)
    {
        $inputData = $request->param();
        if(TitleModel::create($inputData)){
            return getSucc('职务新增成功');
        }
        return getErr('职务新增失败');
    }
}
