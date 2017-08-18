<?php

namespace app\ims\controller;

use app\components\Math;
use app\components\wechat\SendMessageWechatEnterprise;
use app\ims\model\DepartmentModel;
use app\ims\model\EmployeeAccountModel;
use app\ims\model\EmployeeModel;
use think\Cache;
use think\Request;

class EmployeeAccountController extends BaseController
{
    public $verifyCodeExpireTime = 15*3600;
    public $verifyCodeLength = 4;

    /*public function login(Request $request)
    {
        $accountName = $request->post('account_name','');
        $verifyCode = $request->post('verify_code','');
        $employeeAccount = new EmployeeAccountModel();
        if(!$employeeAccount->checkAccountName($accountName)){
            $cacheVerifyCode  = Cache::get($accountName);
            if ($verifyCode === $cacheVerifyCode){
                date_default_timezone_set('PRC');
                $info['login_ip'] = $request->ip();
                $info['login_times'] = $employeeAccount->login_times + 1 ;
                $info['login_time'] = date('Y-m-d H:i:s');
                if ($employeeAccount->save($info)) {
                    $empModel = EmployeeModel::get(['account_id'=>$employeeAccount->id]);
                    $return['id']    = $employeeAccount->account_name;
                    $return['employee_id']    = $empModel->id;
                    $return['department_name'] = $empModel->department->department_name;
                    $return['superior_department_name'] = DepartmentModel::get($empModel->department->superior_id)->department_name;
                    $return['employee_name'] = $empModel->employee_name;
                    $return['title'] = $empModel->title->title;
                    $return['employee_avatar']  = $empModel->employee_avatar;
                    $return['account_id'] = $empModel->account_id;
                    return getSuccess($return);
                }else{
                    return getError('登录失败');
                }
            }
        }else{
            return getError('当前账号不存在');
        }
    }

    public function sendVerifyCode($accountName)
    {
        $employeeAccount = new EmployeeAccountModel();
        $result = $employeeAccount->checkAccountName($accountName);
        if($result){
            $verifyCode = Math::generateRandomNumber($this->verifyCodeLength);
            $sendMessage = new SendMessageWechatEnterprise();
            $sendMessage->setUserId($accountName);
            $sendMessage->setTextContent($verifyCode);
            $sendMessage->sendTextMessage();
            if ($sendMessage->status == 'SUCCESS'){
                if ($sendMessage->invalidUser != ''){
                    return getError('消息发送失败:无效用户');
                }
                $result = Cache::set($accountName,$verifyCode,$this->verifyCodeExpireTime);
                if ($result){
                    return getSuccess('消息发送成功');
                }else{
                    return getError('验证码发送失败');
                }
            }else{
                return getError('消息发送失败:' . $sendMessage->errorMessage);
            }
        }else{
            return getError('当前账号不存在');
        }
    }*/

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
