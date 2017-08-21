<?php

namespace app\ims\controller;

use app\ims\model\EmployeeAccountModel;
use app\ims\model\EmployeeModel;
use think\Request;

class EmployeeController extends PrivilegeController
{
    public function addEmployeeData(Request $request)
    {
        $accountData = $this->getInputAccountData($request);
        $accountModel = new EmployeeAccountModel();
        if($accountModel->save($accountData)){
            $employeeData = $this->getInputEmployeeData($request);
            if($accountModel->employee()->save($employeeData)){
                return getSucc('用户信息新增成功!');
            };
        }
        return getErr('用户信息新增失败!');
    }

    public function deleteEmployeeData(Request $request)
    {
        $employeeId = $request->param('employee_id');
        $employeeModel = EmployeeModel::get($employeeId);
        if ($employeeModel->delete()){
            if($employeeModel->account->delete()){
                return getSucc('用户信息删除成功!');
            }
        }
        return getErr('用户信息删除失败!');

    }

    public function getAllEmployeeDataByDepartmentName(Request $request)
    {
        $departmentName = $request->param('department_name');
        $employeeModel = new EmployeeModel();
        $result = $employeeModel->view('ims_employee','id,employee_name')
            ->view('ims_title','title','ims_title.id = ims_employee.title_id')
            ->view('ims_department','department_name','ims_department.id = ims_employee.department_id')
            ->where('department_name',$departmentName)
            ->select();
        return getSucc($result);
    }

    public function getAllEmployeeData()
    {
        $employeeModel = new EmployeeModel();
        $result = $employeeModel->view('ims_employee','id,employee_name')
            ->view('ims_title','title','ims_title.id = ims_employee.title_id')
            ->view('ims_department','department_name','ims_department.id = ims_employee.department_id')
            ->select();
        return getSucc($result);
    }

    public function getAllEmployeeDataByDepartmentId(Request $request)
    {
        $departmentId = $request->param('department_id');
        $employeeModel = new EmployeeModel();
        $result = $employeeModel->view('ims_employee','id,employee_name')
            ->view('ims_title','title','ims_title.id = ims_employee.title_id')
            ->view('ims_department','department_name','ims_department.id = ims_employee.department_id')
            ->where('ims_employee.department_id',$departmentId)
            ->select();
        return getSuccess($result);
    }

    public function modifyEmployeeData(Request $request)
    {
        $employeeId = $request->param('employee_id');
        $employeeModel = EmployeeModel::get($employeeId);
        $employeeData = $this->getInputEmployeeData($request);
        if($employeeModel->save($employeeData)){
            if ($request->has('password')){
                $salt = $employeeModel->account->account_salt;
                $password = $request->param('password');
                $accountData['account_password'] = $this->getPassword($salt,$password);
            }
            $accountData['account_name'] = $request->param('account_name');
            if($employeeModel->account->save($accountData)){
                return getSucc('信息修改成功!');
            }
        }

        return getErr('信息修改失败!');
    }

    public function getEmployeeData(Request $request,$employeeId)
    {
        $employeeModel = new EmployeeModel();
        $result = $employeeModel->view('ims_employee','id,employee_name,employee_cellphone')
            ->view('ims_title','title','ims_title.id = ims_employee.title_id')
            ->view('ims_employeeAccount','account_name','ims_employeeAccount.id = ims_employee.account_id')
            ->view('ims_department','department_name','ims_department.id = ims_employee.department_id')
            ->where('Employee.id',$employeeId)
            ->find();
        return getSucc($result);
    }

    public function getInputAccountData(Request $request)
    {
        $password = $request->param('password');
        $accountName = $request->param('account_name');
        $salt = $this->getSalt();
        $accountData['account_salt'] = $salt;
        $accountData['account_password'] = $this->getPassword($salt,$password);
        $accountData['account_name'] = $accountName;
        return $accountData;
    }

    public function getInputEmployeeData(Request $request)
    {
        $inputEmployeeData = $request->param('employee');
        $inputEmployeeData = json_decode($inputEmployeeData,true);
        $employeeData['employee_name'] = $inputEmployeeData['employee_name'];
        $employeeData['employee_cellphone'] = $inputEmployeeData['employee_cellphone'];
        $employeeData['department_id'] = $inputEmployeeData['department_id'];
        $employeeData['title_id'] = $inputEmployeeData['title_id'];
        return $employeeData;
    }

    public function getPassword($salt, $password)
    {
        $token = config('employee_account_token');
        return md5($salt.$password.$token);
    }

    public function getSalt()
    {
        $salt = get_nonce_str(8);
        return $salt;
    }
}
