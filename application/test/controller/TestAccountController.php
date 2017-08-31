<?php
namespace app\test\controller;
use think\Controller;
use app\test\model\TestAccount;
use app\test\model\TestAccountWechat;
use think\Session;
use think\Cookie;
use think\Request;
use app\test\model\TestAccountQqModel;
use app\test\model\TestAccountPerson;
use \think\Validate;
use app\test\model\TestAccountBackVisitModel;

class TestAccountController extends Controller
{
    /**
     * @name 回访表添加数据
     * @auth Sam
     */
    public function addVisitTable()
    {
        for($i=0;$i<5;$i++){
            $visitModel = new TestAccountBackVisitModel();

            $visitModel->account_id = rand(1,3);;
            $visitModel->visit_time = date('Y-m-d H:i:s',mktime(rand(1,24),rand(1,59),rand(1,59),rand(1,12),rand(1,31),2017));
            $visitModel->visit_number = 1;

            $visitModel->save();
        }
    }




    /**
     * @name 在回访表中添加数据
     * @auth Sam
     * @param $accountId
     */
    public function actionToVisit($accountId)
    {
        $time = date('Y-m-d H:i:s',time());
        $dayTime = date('Y-m-d',time());
        $visitModel = new TestAccountBackVisitModel();

        $visitInfo = $visitModel->where("account_id = $accountId  AND visit_time like '$dayTime%'")->find();
//        halt($visitInfo);
        if(empty($visitInfo)){
            $visitModel->account_id = $accountId;
            $visitModel->visit_number = 1;
            $visitModel->visit_time = $time;

            $visitModel->save();
        }
    }




    /**
     * @auth Sam
     * @name 微信登录页面
     * @return html
     */
    public function wechatLoginView()
    {
        return $this->redirect("https://open.weixin.qq.com/connect/qrconnect?appid=wxecfc3c0d1425ca34&redirect_uri=http%3A%2F%2Ftest.cheeruislands.com%2FTestAcc%2FwechatLogin&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect",0);

    }

    /**
     * @auth Sam
     * @name 微信登录逻辑
     * @return string
     */
    public function wechatLogin()
    {

        $request = $this->request;
        $codeInfo = $request->param();

        $str ="https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxecfc3c0d1425ca34&secret=1b4f34c083410ff6213c48f72e705420&code=$codeInfo[code]&grant_type=authorization_code";

        $accessToken = $this->curlGet($str);
        $accessToken = json_decode($accessToken,true);
        $str = '';

        $str ='https://api.weixin.qq.com/sns/userinfo?access_token='.$accessToken['access_token'].'&openid='.$accessToken['openid'];

        $userInfo = $this->curlGet($str);
        $userInfo = json_decode($userInfo,true);

        $wechatModel = new TestAccountWechat();

        $wechatInfo = $wechatModel->where('openid',$userInfo['openid'])->find();

        //数据库有微信信息，则登录成功
        if(!empty($wechatInfo)){
            $accountModel = new TestAccount();
            $accountInfo = $accountModel->where('id',$wechatInfo['account_id'])->find();

            if(empty($accountInfo)){
                $wechatInfo->delete();
                return '扫码失败，请重新扫码';
            }

            $token = substr(uniqid(),-6);

            $accountInfo->login_number = $accountInfo->login_number + 1;
            $accountInfo->last_login_time = time();
            $accountInfo->last_login_ip = $request->ip();
            $accountInfo->last_login_type = 'wechat';
            $accountInfo->access_token = $token;

            $accountInfo->save();

            $this->actionToVisit($accountInfo->id);

            Cookie('login_type','wechat');
            Cookie('user_id',$accountInfo->id);
            Cookie('user_name',urlencode($accountInfo->user_name));
            Cookie('other_user_name',urlencode($wechatInfo->nickname));
            Cookie('userw_id',$wechatInfo->id);
            Cookie('token',$token);
            Cookie('call_phone',$accountInfo->call_phone);

            return $this->redirect('http://test.cheeruislands.com/views/personalaccount.html');

        }else{
            $accountModel = new TestAccount();
            $token = substr(uniqid(),-6);
            $accountModel->login_number = 1;
            $accountModel->last_login_time = time();
            $accountModel->last_login_ip = $request->ip();
            $accountModel->last_login_type = 'wechat';
            $accountModel->register_type = 'wechat';
            $accountModel->access_token = $token;

            if(empty($accountModel->save())){
                return '注册失败，请重新扫码登录';
            }

            $this->actionToVisit($accountModel->id);

            $accountId = $accountModel->id;

            $userInfo['account_id'] = $accountId;

            $wechatModel = new TestAccountWechat();

            if(!$wechatModel->save($userInfo)){
                return '注册失败，请重新扫码登录2';
            }

            Cookie('login_type','wechat');
            Cookie('user_id',$accountId);
            Cookie('user_name',urlencode($wechatModel->nickname));
            Cookie('other_user_name',urlencode($wechatModel->nickname));
            Cookie('userw_id',$wechatModel->id);
            Cookie('token',$token);
            Cookie('call_phone','');

            return $this->redirect('http://test.cheeruislands.com/views/personalaccount.html');
        }
    }

    public function qqLoginView()
    {
        return $this->redirect("https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101412324&redirect_uri=http%3A%2F%2Ftest.cheeruislands.com%2FTestAcc%2FqqLogin");

    }

    public function qqLogin()
    {
        $request = $this->request;
        $codeInfo = $request->param();

        if(empty($codeInfo['code']) || !is_array($codeInfo)){
            return $this->error('登录失败，请重新登录',"https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101412324&redirect_uri=http%3A%2F%2Ftest.cheeruislands.com%2FTestAcc%2FqqLoginView");
        }

        //获得accessTocken
        $accToken = $this->curlGet("https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=101412324&client_secret=42ae4f4baca8c1848ff3d9c221623300&code=$codeInfo[code]&redirect_uri=http%3A%2F%2Ftest.cheeruislands.com%2FTestAcc%2FqqLogin");

        $accToken = $this->formateUrlParam($accToken);

        if(empty($accToken['access_token'])){
            return $this->error('登录失败，请重新登录2',"https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101412324&redirect_uri=http%3A%2F%2Ftest.cheeruislands.com%2FTestAcc%2FqqLoginView");
        }

        //获得用户openID
        $openId = $this->curlGet("https://graph.qq.com/oauth2.0/me?access_token=$accToken[access_token]");

        $openId = trim($openId,"callback( ");

        $openId = explode(')',$openId);
        $openId = trim($openId[0],' ');


        $openId = json_decode($openId,true);

        if(empty($openId['openid'])){
            return $this->error('登录失败，请重新登录3',"https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101412324&redirect_uri=http%3A%2F%2Ftest.cheeruislands.com%2FTestAcc%2FqqLoginView");
        }

        $userInfo = $this->curlGet("https://graph.qq.com/user/get_user_info?access_token=$accToken[access_token]&oauth_consumer_key=101412324&openid=$openId[openid]");

        $userInfo = json_decode($userInfo,true);

        $userInfo['openid'] = $openId['openid'];

        $accountModel = new TestAccount();
        $accountQQModel = new TestAccountQqModel();

        $qqInfo = $accountQQModel->where('openid',$userInfo['openid'])->find();

        if(!empty($qqInfo)){
            $accountInfo = $accountModel->where('id',$qqInfo['account_id'])->find();

            if(!empty($accountInfo)){
                $token = substr(uniqid(),-6);
                $accountInfo->login_number = $accountInfo->login_number + 1;
                $accountInfo->last_login_time = time();
                $accountInfo->last_login_ip = $request->ip();
                $accountInfo->last_login_type = 'qq';
                $accountInfo->access_token = $token;

                $accountInfo->save();

                $this->actionToVisit($accountInfo->id);

                Cookie('login_type','qq');
                Cookie('user_id',$accountInfo->id);
                Cookie('user_name',urlencode($accountInfo->user_name));
                Cookie('other_user_name',urlencode($qqInfo->nickname));
                Cookie('userq_id',$qqInfo->id);
                Cookie('token',$token);
                Cookie('call_phone',$accountInfo->call_phone);

                return $this->redirect('http://test.cheeruislands.com/views/personalaccount.html');

            }else{
                $qqInfo = array();
            }


        }

        if(empty($qqInfo)){
            $token = substr(uniqid(),-6);
            $accountModel->login_number = 1;
            $accountModel->last_login_time = time();
            $accountModel->last_login_ip = $request->ip();
            $accountModel->last_login_type = 'qq';
            $accountModel->register_type = 'qq';
            $accountModel->access_token = $token;

            if(empty($accountModel->save())){
                return $this->error('登录失败，请重新登录4',"https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101412324&redirect_uri=http%3A%2F%2Ftest.cheeruislands.com%2FTestAcc%2FqqLoginView");
            }

            $accountId = $accountModel->id;

            $this->actionToVisit($accountId);

            $userInfo['account_id'] = $accountId;

            if(empty($accountQQModel->save($userInfo))){
                $accountModel->delete($accountId);
            }

            $qqId = $accountQQModel->id;


            Cookie('login_type','qq');
            Cookie('user_id',$accountId);
            Cookie('user_name',urlencode($userInfo['nickname']));
            Cookie('other_user_name',urlencode($userInfo['nickname']));
            Cookie('userq_id',$qqId);
            Cookie('token',$token);
            Cookie('call_phone','');

            return $this->redirect('http://test.cheeruislands.com/views/personalaccount.html');

        }



    }

    /**
     * @name 解析地址栏参数
     * @auth Sam
     * @param $str
     * @return array|bool
     */
    public function formateUrlParam($str)
    {
        if(empty($str) || !is_string($str)){
            return false;
        }

        $arr = explode('&',$str);

        $keyValue = array();
        $newArr = array();
        foreach($arr as $k=>$v){
            $keyValue = explode('=',$v);

            $newArr[$keyValue[0]] = $keyValue[1];

            $keyValue = array();

        }

        return $newArr;
    }



    /**
     * @name 发送SMS短信
     * @auth Sam
     * @param integer $mobile 电话号码
     * @param string $content 验证码内容
     * @return bool
     */
    public function sendSMS($mobile,$content)
    {
        include APP_PATH."components/alidayu/TopSdk.php";
        include APP_PATH."components/alidayu/top/request/AlibabaAliqinFcSmsNumSendRequest.php";
        date_default_timezone_set('Asia/Shanghai');

        $c = new \TopClient();
        $c->appkey = "23358963";
        $c->secretKey = "d60915fd89faed62bb1a0dea8af438a8";
        $req = new \AlibabaAliqinFcSmsNumSendRequest();
        $req->setSmsType("normal");
        $req->setSmsFreeSignName("身份验证");
        $req->setSmsParam("{\"code\":\"".$content."\",\"product\":\"沁游假期\"}");
        $req->setRecNum($mobile);
        $req->setSmsTemplateCode("SMS_8970078");
        $resp = $c->execute($req);
//return $resp;
//        return true;

        if($resp->result->success)
        {
            return true;
        }
        else
        {
            return $resp;
            return false;
        }
    }





    //php curl（GET）请求
    public function curlGet($url){
        if(empty($url)){
            return false;
        }
        $output = '';

        $ch = curl_init();
        $str =$url;
        curl_setopt($ch, CURLOPT_URL, $str);
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        $output = curl_exec($ch);
        return $output;
    }



    /**
     * @name 检测是否频繁注册
     * @auth Sam
     * @param $accountInfo
     * @return string
     */
    public function sendVerifyCode($accountInfo)
    {
        if(empty($accountInfo)){
            return '账号不存在';
        }

        $registerTime = explode(',',$accountInfo['register_verify_time']);
        $time = (string)time();

        if(empty($registerTime)){
            return 'ok';
        }

        if($registerTime[0] <= $time && $time <= $registerTime[1]){
            return '请勿频繁获取验证码';
        }

        return 'ok';

    }

    /**
     * @name 检测验证码是否正确（注册页面检测验证码是否正确）
     * @auth Sam
     * @param $request
     * @return string
     */
    public function checkVerifyCode(Request $request)
    {
        $callPhone = $request->param('call_phone',0);
        $codeNumber = $request->param('code_number',0);
        $time = time();

        if(empty($callPhone) || empty($codeNumber)){
            return '手机号或验证码不存在';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('call_phone',$callPhone)->find();

        if(empty($accountInfo)){
            return '没有手机号码';
        }

        $codeTime = explode(',',$accountInfo['code_time']);

        if(empty($codeTime)){
            return '没有验证时间';
        }

        if($codeTime[0] <= $time && $codeTime[1] >= $time){
            if($accountInfo->phone_code == $codeNumber){
                $accountInfo->allow_update_password = 1;
                if($res = $accountInfo->save()){
                    return '验证通过';
                }

                if(empty($res)){
                    if($accountInfo->update()){
                        return '验证通过';
                    }
                }
            }

            return '验证不通过';

        }else{
            return '验证码时间已过期';
        }
    }



    /**
     * @auth Sam
     * @name json格式的数据转化为数组
     * @param $data
     * @return mixed|null
     */
    public function formateData($data)
    {
        if(empty($data)){
            return [];
        }

        return json_decode(json_encode($data),true);

    }

    /**
     * @name 登录功能
     * @param Request $request
     * @return string
     */
    public function accountLogin(Request $request)
    {
        $callPhone = $request->param('call_phone','');
        $password = $request->param('account_password','');

        if(empty($callPhone) || empty($password)){
            return '请输入完整数据';
        }

        $token = substr(uniqid(),-6);

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('call_phone',$callPhone)->find();

        if(empty($accountInfo)){
            return '手机号码不存在';
        }

        $password = $this->passwordAddSalt($password,$accountInfo->salt);


        if($accountInfo->password !== $password['new_password']){
            return '密码错误';
        }

        $accountInfo->access_token = $token;

        if($accountInfo->save()){
            $returnArr['acc_id'] = $accountInfo->id;
            $returnArr['user_name'] = $accountInfo->user_name;
            $returnArr['token'] = $accountInfo->access_token;
            $returnArr['call_phone'] = $accountInfo->call_phone;

            return $returnArr;
        }

        return '登录失败';
    }

    /**
     * @name 账号发送验证码（注册页面发送验证码）
     * @auth Sam
     * @param Request $request
     * @return bool|string
     */
    public function accountSendCode(Request $request)
    {
        $callPhone = $request->param('call_phone',0);
        $type = $request->param('type','register');//register 或 forget_passport

        if(empty($callPhone)){
            return '请输入手机号码';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('call_phone',$callPhone)->find();

        if(!empty($accountInfo)){

            if($accountInfo->is_register == 1){
                if($type == 'register'){
                    return '手机号码已存在，请登录系统';
                }
            }

            $result = $this->sendVerifyCode($accountInfo);

            if($result == 'ok'){

                $codeArr = $this->getTime();

                $accountInfo->phone_code = $codeArr['rand_number'];
                $accountInfo->code_time = $codeArr['code_time_01'].','.$codeArr['code_time_02'];
                $accountInfo->register_verify_time = $codeArr['register_verify_time_01'].','.$codeArr['register_verify_time_02'];

                if($accountInfo->save()){

                    $result = $this->sendSMS($accountInfo->call_phone,$codeArr['rand_number']);
//                    return $result;
                    if(!empty($result)){
                        return '短信已发送，请注意查收';
                    }

                }

                return '验证码错误，发送短信失败,也有可能是没钱了';

            }
            return $result;
        }

        if(empty($accountInfo)){

            if($type == 'forget_passport'){
                return '手机号码不存在，请进行注册';
            }

            $codeArr = $this->getTime();

            $accountModel->phone_code = $codeArr['rand_number'];
            $accountModel->code_time = $codeArr['code_time_01'].','.$codeArr['code_time_02'];
            $accountModel->register_verify_time = $codeArr['register_verify_time_01'].','.$codeArr['register_verify_time_02'];
            $accountModel->call_phone = $callPhone;
            $accountModel->register_time = time();

            if($accountModel->save()){
                $result = $this->sendSMS($callPhone,$codeArr['rand_number']);
//                return $result;
                if(!empty($result)){
                    return '短信已发送2，请注意查收';
                }

//                return $result;
            }

            return '验证码错误2，发送短信失败,也有可能是没钱了';
        }

    }


    /**
     * @name 修改密码（注册页面修改密码）
     * @param Request $request
     * @return string
     */
    public function updateAccountPassport(Request $request)
    {
        $callPhone = $request->param('call_phone',0);
        $accId = $request->param('acc_id',0);
        $token = $request->param('token','');
        $password = $request->param('password','');
        $requestPassword = $request->param('request_password','');
        $type = $request->param('type','register');

        if(empty($password) || empty($requestPassword)){
            return '数据不完整';
        }

        $accountModel = new TestAccount();

        if(!empty($accId)){
            $accountInfo = $accountModel->where('id',$accId)->find();
        }

        if(!empty($callPhone)){
            $accountInfo = $accountModel->where('call_phone',$callPhone)->find();
        }

        if(empty($accountInfo)){
            return '手机号码不存在';
        }

        if($type !== 'register'){
            if($accountInfo->access_token !== $token){
                return '身份认证失败';
            }
        }

        if($accountInfo->allow_update_password == 0){
            return '不能修改密码';
        }

        if($password !== $requestPassword){
            return '密码不一致';
        }

        $password = $this->passwordAddSalt($password);

        $accountInfo->password = $password['new_password'];
        $accountInfo->salt = $password['salt'];
        $accountInfo->allow_update_password = 0;
        $accountInfo->is_register = 1;

        if($accountInfo->save()){
            return '修改密码成功';
        }

        return '修改失败';
    }



    /**
     * @name 获得验证码 验证码时间 频繁注册时间
     * @auth Sam
     * @return mixed
     */
    public function getTime()
    {
        $randNumber = rand(100000,999999);
//        $randNumber = 667788;
        $returnArr = array();
        $codeTime01 = time();
        $codeTime02 = strtotime('+30 minute',time());

        $registerVerifyTime = strtotime('+ 1 minute',time());

        $returnArr['rand_number'] = $randNumber;
        $returnArr['code_time_01'] = $codeTime01;
        $returnArr['code_time_02'] = $codeTime02;
        $returnArr['register_verify_time_01'] = $codeTime01;
        $returnArr['register_verify_time_02'] = $registerVerifyTime;

        return $returnArr;
    }

    /**
     * @name 密码加盐并散列
     * @auth Sam
     * @param $password
     * @return bool/array
     */
    public function passwordAddSalt($password,$salt='')
    {
        if(empty($password)){
            return false;
        }

        if(empty($salt)){
            $salt = substr(uniqid(rand()),-6);

            if(empty($salt)){
                $salt = substr(uniqid(),-6);
            }
        }


        $newPassowrd = md5($password.$salt);

        $returnArray['old_password'] = $password;
        $returnArray['new_password'] = $newPassowrd;
        $returnArray['salt'] = $salt;

        return $returnArray;

    }

    /**
     * @name 修改账号呢称
     * @param Request $request
     * @return string
     */
    public function updateAccountUserName(Request $request)
    {
        $accId = $request->param('acc_id',0);
        $token = $request->param('token','');
        $userName = $request->param('user_name','');

        if(empty($accId) || empty($token) || empty($userName)){
            return '数据不完整';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('id',$accId)->find();

        if(empty($accountInfo)){
            return '没有手机号码';
        }

        if($accountInfo->access_token !== $token){
            return '账号验证错误';
        }

        if($accountInfo->user_name == $userName){
            return '用户名没有修改';
        }

        $accountInfo->user_name = $userName;

        if($accountInfo->save()){
            return '修改成功';
        }

        return '修改失败';
    }


    /**
     * @name 后台发送验证码
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function backSendCode(Request $request)
    {
        $accId = $request->param('acc_id',0);
        $callPhone = $request->param('call_phone',0);
        $token = $request->param('token','');

        if(empty($accId) || empty($callPhone) || empty($token)){
            return '数据不完整';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('id',$accId)->find();

        if(empty($accountInfo)){
            return '账号不存在';
        }

        if($accountInfo->access_token !== $token){
            return '身份验证失败';
        }

        $getTime = $this->getTime();

        $accountInfo->phone_code = $getTime['rand_number'];
        $accountInfo->code_time = $getTime['code_time_01'].','.$getTime['code_time_02'];
        $accountInfo->register_verify_time = $getTime['register_verify_time_01'].','.$getTime['register_verify_time_02'];

        if($accountInfo->save()){
            $this->sendSMS($callPhone,$getTime['rand_number']);

            return '短信已发送，请注意查收';

        }
        return '短信发送失败';
    }

    /**
     * @name 后台检测验证码是否成功
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function backCheckCode(Request $request)
    {
        $accId = $request->param('acc_id',0);
        $token = $request->param('token','');
        $codeNumber = $request->param('code_number',0);

        if(empty($accId) || empty($token) || empty($codeNumber)){
            return '数据不完整';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('id',$accId)->find();

        if(empty($accountInfo)){
            return '账号不存在';
        }

        if($accountInfo->access_token !== $token){
            return '身份验证失败';
        }

        if($accountInfo->phone_code == $codeNumber){
            $accountInfo->allow_update_password = 1;

            $accountInfo->update();
            if($accountInfo->save()){
                return '验证成功';
            }
            return '验证成功';
        }
        return '验证码错误';
    }

    /**
     * @name 后台修改密码
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function backUpdatePassword(Request $request)
    {
        $accId = $request->param('acc_id',0);
        $token = $request->param('token','');
        $password = $request->param('password','');
        $requestPassword = $request->param('request_password','');

        if(empty($accId) || empty($token) || empty($password) || empty($requestPassword)){
            return '数据不完整';
        }

        if($password !== $requestPassword){
            return '密码不一致';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('id',$accId)->find();

        if(empty($accountInfo)){
            return '账号不存在';
        }

        if($accountInfo->access_token !== $token){
            return '身份验证失败';
        }

        if($accountInfo->allow_update_password == 0){
            return '没有修改权限';
        }

        $password = $this->passwordAddSalt($password);

        $accountInfo->password = $password['new_password'];
        $accountInfo->salt = $password['salt'];
        $accountInfo->allow_update_password = 0;

        if($accountInfo->save()){
            return '修改成功';
        }

        return '修改失败';
    }

    /**
     * @name 修改手机号码
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function backUpdatePhone(Request $request)
    {
        $accId = $request->param('acc_id',0);
        $newCallPhone = $request->param('new_call_phone',0);
        $token = $request->param('token','');

        if(empty($accId)|| empty($newCallPhone) || empty($token)){
            return '数据不完整';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('id',$accId)->find();

        if(empty($accountInfo)){
            return '账号不存在';
        }

        if(!empty($accountInfo->call_phone)){
            if($accountInfo->call_phone == $newCallPhone){
                return '手机号码一致，无需修改';
            }
        }

        if($accountInfo->access_token !== $token){
            return '身份验证失败';
        }

        if($accountInfo->allow_update_password == 0){
            return '没有修改权限';
        }

        $accountInfo->allow_update_password = 0;
        $accountInfo->call_phone = $newCallPhone;

        if($accountInfo->save()){
            return '修改成功';
        }

        return '修改失败';

    }


    /**
     * @name 获得常用人信息
     * @auth Sam
     * @param Request $request
     * @param interge $accountId 用户ID
     * @return bool|mixed|string
     */
    public function getPersonList(Request $request)
    {
        $accoutId = $request->param('account_id',0);

        if(empty($accoutId)){
            return '账号不存在';
        }

        $accPersonModel = new TestAccountPerson();

        $accPersonList = $this->formateData($accPersonModel->where('account_id',$accoutId)->select());

        if(!empty($accPersonList)){
            return json($accPersonList);
        }

        return '没有常用人信息';

    }

    /**
     * @name 获得常用人信息
     * @auth Sam
     * @param Request $request
     * @return string|\think\response\Json
     */
    public function getPersonInfo(Request $request)
    {
        $accId = $request->param('acc_id',0);
        $token = $request->param('token','');
        $perId = $request->param('person_id',0);

        if(empty($accId) || empty($token) || empty($perId)){
            return '数据不完整';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('id',$accId)->find();

        if(empty($accountInfo)){
            return '账号不存在';
        }

        if($accountInfo->access_token !== $token){
            return '身份验证失败';
        }

        $personModel = new TestAccountPerson();

        $personInfo = $personModel->where('id',$perId)->find();

        if(empty($personInfo)){
            return '常用人不存在';
        }

        return $personInfo;

    }



    /**
     * @name 修改常用人信息
     * @auth Sam
     * @param Request $request
     * @param array $personInfo 常用人信息
     * @return mixed|string
     */
    public function updatePersonInfo(Request $request)
    {
        $personInfo = $request->param('person_info/a',array());

        if(empty($personInfo) || !is_array($personInfo)){
            return '没有常用人ID';
        }

        $accPersonModel = new TestAccountPerson();

        $validateController = new Validate($accPersonModel->rule);

        if($validateController->check($personInfo)){
            return '数据不完整';
        }


        if(!empty($personInfo['id'])){
            $accPersonInfo = $accPersonModel->where('id',$personInfo['id'])->find();

            if(empty($accPersonInfo)){
                return '常用人不存在';
            }

            if($accPersonInfo->data($personInfo)->save()){
                return '修改成功';
            }
        }else{
            if($accPersonModel->data($personInfo)->save()){
                return '修改成功';
            }
        }

        return '修改失败';

    }

    /**
     * @name 删除常用联系人
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function deletePersonInfo(Request $request)
    {
        $accId = $request->param('acc_id',0);
        $token = $request->param('token','');
        $perId = $request->param('person_id',0);

        if(empty($accId) || empty($token) || empty($perId)){
            return '数据不完整';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('id',$accId)->find();

        if(empty($accountInfo)){
            return '账号不存在';
        }

        if($accountInfo->access_token !== $token){
            return '身份验证失败';
        }

        $personModel = new TestAccountPerson();

        $personInfo = $personModel->where('id',$perId)->find();

        if(!empty($personInfo)){
            if($personInfo->delete()){
                return '删除成功';
            }
        }


        return '删除失败';

    }

    /**
     * @name 获得用户列表分页
     * @auth Sam
     * @param Request $request
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAccountList(Request $request)
    {
        $page = $request->param('page',0);
        $limit = $request->param('limit',5);

        $accountModel = new TestAccount();

        $accountList = $accountModel->field('id,user_name,nick_name,call_phone,register_type')->limit($page,$limit)->select()->toArray();

        if(empty($accountList)){
            return '没有用户信息';
        }

        $accQQModel = new TestAccountQqModel();
        $accWechatModel = new TestAccountWechat();

        foreach($accountList as $k=>$v){
            if($v['register_type'] == 'wechat'){
                $accWechatInfo = $accWechatModel->field('id as userw_id,nickname as other_nick_name,headimgurl as head_image')->where('account_id',$v['id'])->find();

                if(!empty($accWechatInfo)){
                    $accountList[$k]['other_nick_name'] = $accWechatInfo->other_nick_name;
                    $accountList[$k]['userw_id'] = $accWechatInfo->userw_id;
                    $accountList[$k]['head_image'] = $accWechatInfo->head_image;
                }else{
                    $accountList[$k]['other_nick_name'] = '';
                    $accountList[$k]['userw_id'] = 0;
                    $accountList[$k]['head_image'] = '';
                }
            }else if($v['register_type'] == 'qq'){
                $accQQInfo = $accQQModel->field('id as userq_id,nickname as other_nick_name,figureurl_2 as head_image')->where('account_id',$v['id'])->find();

                if(!empty($accQQInfo)){
                    $accountList[$k]['other_nick_name'] = $accQQInfo->other_nick_name;
                    $accountList[$k]['userq_id'] = $accQQInfo->userq_id;
                    $accountList[$k]['head_image'] = $accQQInfo->head_image;
                }else{
                    $accountList[$k]['other_nick_name'] = '';
                    $accountList[$k]['userw_id'] = 0;
                    $accountList[$k]['head_image'] = '';
                }

            }

            if(empty($v['register_type'])){
                $accountList[$k]['other_nick_name'] = '';
                $accountList[$k]['head_image'] = '';
            }

            $accWechatInfo = array();
            $accQQInfo = array();
        }

        return $accountList;

    }

    /**
     * @name 获取用户列表总页码数
     * @auth Sam
     * @return float
     */
    public function getAccountListTotalPage()
    {
        $accountModel = new TestAccount();

        $allAccountList = $accountModel->count();

        $totalPage = ceil($allAccountList / 5);

        return $totalPage;
    }



    /**
     * @name 获得用户名称
     * @auth Sam
     * @param Request $request
     * @return string
     */
    public function getUserName(Request $request)
    {
        $accountId = $request->param('account_id',0);
        $token = $request->param('token','');

        if(empty($accountId) || empty($token)){
            return '数据不完整';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('id',$accountId)->find();

        if(empty($accountInfo)){
            return '没有用户信息';
        }

        if($accountInfo->access_token !== $token){
            return '身份验证失败';
        }

        if($accountInfo->register_type == 'wechat'){
            $wechatModel = new TestAccountWechat();

            $wechatInfo = $wechatModel->where('account_id',$accountInfo->id)->find();

            if(!empty($wechatInfo)){
                $returnArr['user_name'] = $accountInfo->user_name;
                $returnArr['other_user_name'] = $wechatInfo->nickname;
                $returnArr['head_image'] = $wechatInfo->headimgurl;
            }else{
                $returnArr['user_name'] = $accountInfo->user_name;
                $returnArr['other_user_name'] = '';
                $returnArr['head_image'] = '';
            }

        }else if($accountInfo->register_type == 'qq'){
            $qqModel = new TestAccountQqModel();

            $qqInfo = $qqModel->where('account_id',$accountInfo->id)->find();

            if(!empty($qqInfo)){
                $returnArr['user_name'] = $accountInfo->user_name;
                $returnArr['other_user_name'] = $qqInfo->nickname;
                $returnArr['head_image'] = $qqInfo->figureurl_2;
            }else{
                $returnArr['user_name'] = $accountInfo->user_name;
                $returnArr['other_user_name'] = '';
                $returnArr['head_image'] = '';
            }

        }else{
            $returnArr['user_name'] = $accountInfo->user_name;
            $returnArr['other_user_name'] = '';
            $returnArr['head_image'] = '';
        }

        return $returnArr;
    }



}

?>
