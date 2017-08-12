<?php
namespace app\index\controller;
use think\Controller;
use app\index\model\TestAccount;
use app\index\model\TestAccountWechat;
use think\Session;

class TestWeb extends Controller
{
    /**
     * @auth Sam
     * @name 微信登录页面
     * @return html
     */
    public function wechatLoginView()
    {
        return $this->redirect("https://open.weixin.qq.com/connect/qrconnect?appid=wxecfc3c0d1425ca34&redirect_uri=http%3A%2F%2Fprice.cheeruislands.com%2FTestWeb%2FwechatLogin&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect",0);

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

            $accountInfo->login_number = $accountInfo->login_number + 1;
            $accountInfo->last_login_time = time();
            $accountInfo->last_login_ip = $request->ip();
            $accountInfo->last_login_type = 'wechat';

            $accountInfo->save();

            $returnArray['login_type'] = 'wechat';
            $returnArray['user_id'] = $accountInfo->id;
            $returnArray['user_name'] = $wechatInfo->nickname;
            $returnArray['userw_id'] = $wechatInfo->id;

            return json_encode($returnArray);

        }else{
            $accountModel = new TestAccount();
            $accountModel->login_number = 1;
            $accountModel->last_login_time = time();
            $accountModel->last_login_ip = $request->ip();
            $accountModel->last_login_type = 'wechat';
            $accountModel->register_type = 'wechat';

            if(empty($accountModel->save())){
                return '注册失败，请重新扫码登录';
            }

            $accountId = $accountModel->id;

            $userInfo['account_id'] = $accountId;

            $wechatModel = new TestAccountWechat();

            if(!$wechatModel->save($userInfo)){
                return '注册失败，请重新扫码登录2';
            }

            $returnArray['login_type'] = 'wechat';
            $returnArray['user_id'] = $accountId;
            $returnArray['user_name'] = $wechatModel->nickname;
            $returnArray['userw_id'] = $wechatModel->id;

            return json_encode($returnArray);
        }



    }

    /**
     * @name 发送SMS短信
     * @param integer $mobile 电话号码
     * @param string $content 验证码内容
     * @return bool
     */
    public function sendSMS($mobile,$content)
    {
        include APP_PATH."common/alidayu/TopSdk.php";
        include APP_PATH."common/alidayu/top/request/AlibabaAliqinFcSmsNumSendRequest.php";
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

        return true;
/*//         var_dump($this->formateData($resp));
        if($resp->result->success)
        {
            return true;
        }
        else
        {
            return false;
        }*/
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


    public function register(Request $request)
    {
        $mobile = $request->param('mobile',0);

        if(empty($mobile)){
            return '请输入手机号码';
        }

        $accountModel = new TestAccount();

        $accountInfo = $this->formateData($accountModel->where('call_phone',$mobile)->find());

        //如果有账号信息，则，判断是否频繁注册，否 报错，是 发送
        if(!empty($accountInfo)){
            $result = $this->sendVerifyCode($accountInfo);

            //重新发送信息
            if($result == 'ok'){

            }else{
                return $result;
            }
        }

        //如果没有账号信息，则新注册一个用户
        if(empty($accountInfo)){

        }


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
        $time = time();

        if(empty($registerTime)){
            return 'ok';
        }

        if($registerTime[0] <= $time && $time >= $registerTime[1]){
            return '短信已发送，请注意查收';
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

        if($codeTime[0] <= $time && $time >= $codeTime[1]){
            if($accountInfo->phone_code == $codeNumber){
                $accountInfo->allow_update_password = 1;

                if($accountInfo->save()){
                    return '验证通过';
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
            return null;
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

        if(empty($accountName) || empty($password)){
            return '请输入完整数据';
        }

        $token = stubstr(uniqid(),-6);

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('call_phone',$callPhone)->find();

        if(empty($accountInfo)){
            return '手机号码不存在';
        }

        $password = $this->passwordAddSalt($password);

        if($accountInfo->password !== $password['new_password']){
            return '密码错误';
        }

        $accountInfo->access_token = $token;

        if($accountInfo->save()){
            $returnArr['acc_id'] = $accountInfo->id;
            $returnArr['user_name'] = $accountInfo->user_name;
            $returnArr['token'] = $accountInfo->access_token;

            return json_encode($returnArr);
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
        $callPhone = $this->param('class_phone',0);
        $type = $this->param('type','register');//register 或 forget_passport

        if(empty($callPhone)){
            return '请输入手机号码';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('call_phone',$callPhone)->find();

        if(!empty($accountInfo)){
            if($type == 'register'){
                return '手机号码已存在，请登录系统';
            }

            if($result = $this->sendVerifyCode($accountInfo) == 'ok'){
                $codeArr = $this->getTime();

                $accountInfo->phone_code = $codeArr['rand_number'];
                $accountInfo->code_time = $codeArr['code_time'];
                $accountInfo->register_verify_time = $codeArr['register_verify_time'];

                if($accountInfo->save()){
                    $this->sendSMS($accountInfo->call_phone,$codeArr['rand_number']);
                    return '短信已发送，请注意查收';
                }

                return '验证码错误，发送短信失败';

            }
            return $result;
        }

        if(empty($accountInfo)){
            if($type == 'forget_passport'){
                return '手机号码不存在，请进行注册';
            }

            $codeArr = $this->getTime();

            $accountModel->phone_code = $codeArr['rand_number'];
            $accountModel->code_time = $codeArr['code_time'];
            $accountModel->register_verify_time = $codeArr['register_verify_time'];
            $accountModel->call_phone = $callPhone;
            $accountModel->register_time = time();

            if($accountModel->save()){
                $this->sendSMS($callPhone,$codeArr['rand_number']);
                return '短信已发送2，请注意查收';
            }

            return '验证码错误2，发送短信失败';
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
        $password = $request->param('password','');
        $requestPassword = $request->param('request_password','');

        if(empty($callPhone) || empty($password) || empty($requestPassword)){
            return '数据不完整';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('call_phone',$callPhone)->find();

        if(empty($accountInfo)){
            return '手机号码不存在';
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
        $randNumber = rand(1000,9999);

        $codeTime = strtotime('+30 minute',time());

        $registerVerifyTime = strtotime('+ 1 minute',time());

        $returnArr['rand_number'] = $randNumber;
        $returnArr['code_time'] = $codeTime;
        $returnArr['register_verify_time'] = $registerVerifyTime;

        return $returnArr;
    }

    /**
     * @name 密码加盐并散列
     * @auth Sam
     * @param $password
     * @return bool/array
     */
    public function passwordAddSalt($password)
    {
        if(empty($password)){
            return false;
        }

        $salt = substr(uniqid(rand()),-6);

        if(empty($salt)){
            $salt = substr(uniqid(),-6);
        }

        $newPassowrd = md5($password.$salt);

        $returnArray['old_password'] = $password;
        $returnArray['new_password'] = $newPassowrd;
        $returnArray['salt'] = $salt;

        return $returnArray;

    }

    /**
     * @name 修改账号用户名
     * @param Request $request
     * @return string
     */
    public function updateAccountUserName(Request $request)
    {
        $callPhone = $request->param('call_phone',0);
        $token = $request->param('token','');
        $userName = $request->param('user_name','');

        if(empty($callPhone) || empty($token) || empty($userName)){
            return '数据不完整';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('call_phone',$callPhone)->find();

        if(empty($accountInfo)){
            return '没有手机号码';
        }

        if($accountInfo->access_token !== $token){
            return '账号错误';
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
     * @name 修改手机号码
     * @param Request $request
     * @return string
     */
    public function updateAccountPhone(Request $request)
    {
        $callPhone = $request->param('call_phone',0);
        $token = $request->param('token','');
        $newCallPhone = $request->param('new_call_phone','');

        if(empty($callPhone) || empty($token) || empty($newCallPhone)){
            return '数据不完整';
        }

        $accountModel = new TestAccount();

        $accountInfo = $accountModel->where('call_phone',$callPhone)->find();

        if(empty($accountInfo)){
            return '手机号码不存在';
        }

        if($accountInfo->allow_update_password == 0){
            return '没有权限修改手机号码';
        }

        $accountInfo->call_phone = $newCallPhone;

        if($accountInfo->save()){
            return '修改成功';
        }

        return '修改失败';

    }




}

?>