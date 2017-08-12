<?php
namespace app\index\controller;

use app\index\model\Account as AccountModel;
use app\index\model\Employees;
use think\Session;
use think\Request;

class Account extends BaseController
{

    const TOKEN = 'aDMShFarYMMIv7*h';

    public function login(Request $request)
    {
        $name = $request->post('username','');
        $pwd = $request->post('password','');
        $account = AccountModel::get(['acct_name'=>$name]);

        if ($account == null) {

            return json('当前账号不存在!');
        }

        $encryptedPwd = md5($pwd . $account->salt . self::TOKEN);

        if ($encryptedPwd === $account->password) {

            date_default_timezone_set('PRC');
            $info['emp_sn'] = $account->emp_sn;
            $info['login_ip'] = $request->ip();
            $info['login_times'] = $account->login_times + 1 ;
            $info['login_time'] = date('Y-m-d H:i:s');
            if ($account->update($info)) {
                 Session::set('emp_info',$account->employees);
                 Session::set('is_login',md5($account->emp_sn.self::TOKEN));
                 $empModel = Employees::get($account->emp_sn);
                 $return['id']    = $empModel->emp_sn;
                 $return['position'] = $empModel->dept->dept_name;
                 $return['name']    = $empModel->emp_cn_name;
                 $return['post']    = $empModel->title->title;
                 $return['avatar']  = $empModel->avatar;
                 $return['emp_sn']  = $info['emp_sn'];
                 return json($return);
            }else{
                return json('登录失败');
            }
        }
        return json('密码错误!');
    }
}