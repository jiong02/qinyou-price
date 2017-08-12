<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/12
 * Time: 17:26
 */

namespace app\test\controller;

use think\Controller;
use think\Request;
use app\test\model\TestAccount;

class BaseController extends Controller
{
    public function checkAllParam($params = [], $data = [], $rule = [])
    {
        if (empty($params)){
            abort('200','请传入参数!');
        }
        foreach ($params as $index => $param) {
            $data[$index] = $param[0];
            $rule[$index] = $param[1];
        }
        $result = $this->validate($data,$rule);
        if ($result !== true){
            abort('200',$result);
        }
    }

    /**
     * @name 验证Token
     * @auth Sam
     * @return string
     */
    public function checkAccountToken()
    {
        $request = $this->request;

        $accountId = $request->param('account_id',0);
        $token = $request->param('token','');

        if(empty($accountId) || empty($token)){
            return '数据不完整';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('id',$accountId)->find();

        if(empty($accountInfo)){
            return '账号不存在';
        }

        if($accountInfo->access_token !== $token){
            return '身份验证错误';
        }

        return 'ok';

    }

    public function formateData($data)
    {
        if(empty($data)){
            return false;
        }

        return json_decode(json_encode($data),true);

    }


}