<?php

namespace app\ims\controller;

use app\components\Data;
use app\components\wechat\wechatEnterprise\WechatEnterpriseSendMessage;
use app\ims\model\EmployeeAccountModel;
use app\ims\model\EmployeeModel;
use think\Cache;
use think\Request;

class EmployeeAccountController extends BaseController
{
    public $verifyCodeExpireTime = 15*3600;
    public $verifyCodeLength = 4;

    public function wechatEnterpriseLogin(Request $request)
    {
        $accountName = $request->param('account_name','');
        $verifyCode = $request->param('verify_code','');
        $employeeAccountModel = new EmployeeAccountModel();
        $result = $employeeAccountModel->checkAccountName($accountName);
        $employeeAccountModel = $employeeAccountModel->where('account_name',$accountName)->find();
        if($result){
            $cacheVerifyCode = Cache::get($accountName);
            if ($verifyCode == $cacheVerifyCode){
                date_default_timezone_set('PRC');
                $info['login_ip'] = $request->ip();
                $info['login_times'] = $employeeAccountModel->login_times + 1 ;
                $info['login_time'] = date('Y-m-d H:i:s');
                if ($employeeAccountModel->save($info)) {
                    $employeeModel = new EmployeeModel();
                    $employeeModel = $employeeModel->where('account_name',$accountName)->find();
                    $return['id'] = $accountName;
                    $return['employee_id'] = $employeeModel->id;
                    $return['employee_token'] = $employeeModel->employee_token;
                    $return['department_name'] = $employeeModel->department_name;
                    $return['department_id'] = $employeeModel->department_id;
                    $return['employee_name'] = $employeeModel->employee_name;
                    $return['title'] = $employeeModel->title;
                    $return['employee_avatar']  = $employeeModel->employee_avatar;
                    $return['account_id'] = $employeeAccountModel->id;
                    return getSuccess($return);
                }else{
                    return getError('登录失败');
                }
            }else{
                return getError('验证码不正确');
            }
        }else{
            return getError('当前账号不存在');
        }
    }

    public function sendVerifyCode(Request $request)
    {
        $accountName = $request->param('account_name');
        $employeeAccount = new EmployeeAccountModel();
        $result = $employeeAccount->checkAccountName($accountName);
        if($result){
            $verifyCode = Data::generateRandomNumber($this->verifyCodeLength);
            $sendMessage = new WechatEnterpriseSendMessage();
            $sendMessage->sendTextMessage($accountName, $verifyCode);
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
    }

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
                $return['id'] = $account->account_name;
                $return['employee_id'] = $empModel->id;
                $return['employee_token'] = $empModel->token;
                $return['department_name'] = $empModel->department_name;
                $return['employee_name'] = $empModel->employee_name;
                $return['title'] = $empModel->title;
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
