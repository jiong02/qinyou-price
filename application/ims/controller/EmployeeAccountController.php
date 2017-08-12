<?php

namespace app\ims\controller;

use app\ims\model\DepartmentModel;
use app\ims\model\EmployeeAccountModel;
use app\ims\model\EmployeeModel;
use think\Request;

class EmployeeAccountController extends BaseController
{
    public function login(Request $request)
    {
        $name = $request->post('username','');
        $pwd = $request->post('password','');

        $account = EmployeeAccountModel::get(['account_name'=>$name]);
        if ($account == null) {
            return json('当前账号不存在!');
        }

        $employeeController = new EmployeeController();
        $encryptedPassword =  $employeeController->getPassword($account->account_salt,$pwd);
        if ($encryptedPassword === $account->account_password) {
            date_default_timezone_set('PRC');
            $info['login_ip'] = $request->ip();
            $info['login_times'] = $account->login_times + 1 ;
            $info['login_time'] = date('Y-m-d H:i:s');
            if ($account->save($info)) {
                $empModel = EmployeeModel::get(['account_id'=>$account->id]);
                $return['id']    = $account->account_name;
                $return['employee_id']    = $empModel->id;
                $return['department_name'] = $empModel->department->department_name;
                $return['superior_department_name'] = DepartmentModel::get($empModel->department->superior_id)->department_name;
                $return['employee_name'] = $empModel->employee_name;
                $return['title'] = $empModel->title->title;
                $return['employee_avatar']  = $empModel->employee_avatar;
                $return['account_id'] = $empModel->account_id;
                return json($return);
            }else{
                return json('登录失败');
            }
        }
        return json('密码错误!');
    }
}
