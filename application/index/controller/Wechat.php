<?php
namespace app\index\controller;
use think\Loader;
use think\Route;
use app\index\model\WechatLogin;
use \think\Request;
use \think\Cache;
use app\index\model\TestAccountQqModel;
use app\index\model\TestAccount;
use app\index\model\TestAccountPerson;

class Wechat extends BaseController
{
    //微信登录二维码页面
    public function weLoginView()
    {
//        dump(Route::get('/'));
        return $this->redirect("https://open.weixin.qq.com/connect/qrconnect?appid=wxecfc3c0d1425ca34&redirect_uri=http%3A%2F%2Fprice.cheeruislands.com%2Fwechat%2FweLogin&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect",0);
//        return $this->fetch('weLogin');
    }

    //微信二维码登录操作
    public function weLogin()
    {
        $request = $this->request;
        $codeInfo = $request->param();

        echo '<pre>';
        var_dump($codeInfo);
        $str ="https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxecfc3c0d1425ca34&secret=1b4f34c083410ff6213c48f72e705420&code=$codeInfo[code]&grant_type=authorization_code";

        echo $str;
        echo '<br>';

        $accessToken = $this->curlGet($str);
        $accessToken = json_decode($accessToken,true);
        $str = '';

        var_dump($accessToken);

        $str ='https://api.weixin.qq.com/sns/userinfo?access_token='.$accessToken['access_token'].'&openid='.$accessToken['openid'];

        $userInfo = $this->curlGet($str);
        $userInfo = json_decode($userInfo,true);

        var_dump($userInfo);
        $mysqlResult = '';

        $login = new WechatLogin();

        $result = $login->where('openid',$userInfo['openid'])->select();
        var_dump($result);
        if(!empty($result)){
            $userInfo['update_time'] = date('Y-m-d H:i:s',time());
//            $mysqlResult = $login->save($userInfo,['openid'=>$userInfo['openid']]);
            $mysqlResult = WechatLogin::where('openid', $userInfo['openid'])
                ->update($userInfo);
        }else{
            $mysqlResult = $login->data($userInfo)->save();
        }

        var_dump($mysqlResult);
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


    public function wechatLoginView()
    {
        return $this->redirect("https://open.weixin.qq.com/connect/qrconnect?appid=wxecfc3c0d1425ca34&redirect_uri=http%3A%2F%2Fprice.cheeruislands.com%2Fwechat%2FwechatLogin&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect",0);



    }


    public function wechatLogin(Request $request)
    {
        $codeInfo = $request->param();

        echo '<pre>';

        $accTocken = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxecfc3c0d1425ca34&secret=1b4f34c083410ff6213c48f72e705420&code=$codeInfo[code]&grant_type=authorization_code");

        echo "https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxecfc3c0d1425ca34&secret=1b4f34c083410ff6213c48f72e705420&code=$codeInfo[code]&grant_type=authorization_code";

        echo '<br>';

		var_dump($accTocken);

		$accTocken = json_decode($accTocken,true);

		$userInfo = file_get_contents("https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=wxecfc3c0d1425ca34&grant_type=refresh_token&refresh_token=$accTocken[access_token]");

		echo "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=wxecfc3c0d1425ca34&grant_type=refresh_token&refresh_token=$accTocken[access_token]";

		echo '<br>';

        var_dump(json_decode($userInfo,true));

    }


    public function qqLoginView()
    {
        return $this->redirect("https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101412324&redirect_uri=http%3A%2F%2Fprice.cheeruislands.com%2Fwechat%2FqqLogin");

    }

    public function qqLogin()
    {
        $request = $this->request;
        $codeInfo = $request->param();

        if(empty($codeInfo['code']) || !is_array($codeInfo)){
            return $this->error('登录失败，请重新登录',"https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101412324&redirect_uri=http%3A%2F%2Fprice.cheeruislands.com%2Fwechat%2FqqLogin");
        }

        /*echo '<br>';
        var_dump($codeInfo);*/

        //获得accessTocken
        $accToken = $this->curlGet("https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=101412324&client_secret=42ae4f4baca8c1848ff3d9c221623300&code=$codeInfo[code]&redirect_uri=http%3A%2F%2Fprice.cheeruislands.com%2Fwechat%2FqqLogin");

        $accToken = $this->formateUrlParam($accToken);

        if(empty($accToken['access_token'])){
            return $this->error('登录失败，请重新登录2',"https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101412324&redirect_uri=http%3A%2F%2Fprice.cheeruislands.com%2Fwechat%2FqqLogin");
        }

        /*echo '<br>';
        var_dump($accToken);*/

        //获得用户openID
        $openId = $this->curlGet("https://graph.qq.com/oauth2.0/me?access_token=$accToken[access_token]");
//        echo ($openId);

        $openId = trim($openId,"callback( ");

        $openId = explode(')',$openId);
        $openId = trim($openId[0],' ');

        /*echo '<br>';
        var_dump($openId);*/

        $openId = json_decode($openId,true);

        /*echo '<br>';
        var_dump($openId);exit;*/

        if(empty($openId['openid'])){
            return $this->error('登录失败，请重新登录3',"https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101412324&redirect_uri=http%3A%2F%2Fprice.cheeruislands.com%2Fwechat%2FqqLogin");
        }

        $userInfo = $this->curlGet("https://graph.qq.com/user/get_user_info?access_token=$accToken[access_token]&oauth_consumer_key=101412324&openid=$openId[openid]");

        $userInfo = json_decode($userInfo,true);

        /*var_dump($userInfo);
        halt($openId);*/


        $userInfo['openid'] = $openId['openid'];

        $accountModel = new TestAccount();
        $accountQQModel = new TestAccountQqModel();

        $qqInfo = $accountQQModel->where('openid',$userInfo['openid'])->find();

        if(!empty($qqInfo)){
            $accountInfo = $accountModel->where('id',$qqInfo['account_id'])->find();

            if(!empty($accountInfo)){
                $accountInfo->login_number = $accountInfo->login_number + 1;
                $accountInfo->last_login_time = time();
                $accountInfo->last_login_ip = $request->ip();
                $accountInfo->last_login_type = 'qq';

                $accountInfo->save();

                $returnArray['login_type'] = 'qq';
                $returnArray['user_id'] = $accountInfo->id;
                $returnArray['user_name'] = $qqInfo->nickname;
                $returnArray['userq_id'] = $qqInfo->id;

                return json_encode($returnArray);

            }else{
                $qqInfo = array();
            }


        }

        if(empty($qqInfo)){
            $accountModel->login_number = 1;
            $accountModel->last_login_time = time();
            $accountModel->last_login_ip = $request->ip();
            $accountModel->last_login_type = 'qq';
            $accountModel->register_type = 'qq';

            if(empty($accountModel->save())){
                return $this->error('登录失败，请重新登录4',"https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101412324&redirect_uri=http%3A%2F%2Fprice.cheeruislands.com%2Fwechat%2FqqLogin");
            }

            $accountId = $accountModel->id;

            $userInfo['account_id'] = $accountId;
// halt($userInfo);
            if(empty($accountQQModel->save($userInfo))){
                $accountModel->delete($accountId);
            }

            $qqId = $accountQQModel->id;

            $returnArray['login_type'] = 'qq';
            $returnArray['user_id'] = $accountId;
            $returnArray['user_name'] = $accountQQModel->nickname;
            $returnArray['userq_id'] = $qqId;

            return json_encode($returnArray);

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

    public function formateData($data)
    {
        if(empty($data) || !is_array($data)){
            return false;
        }

        return json_decode(json_encode($data),true);

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
            return $accPersonList;
        }

        return '没有常用人信息';

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
        $personInfo = $request->param('person_info',array());

        if(empty($personInfo)){
            return '没有常用人ID';
        }

        $accPersonModel = new TestAccountPerson();

        $accPersonInfo = $accPersonModel->where('id',$personInfo['id'])->find();

        if(empty($accPersonInfo)){
            return '常用人不存在';
        }

        if($accPersonInfo->data($personInfo)->update()){
            return $accPersonModel->id;
        }

        return '修改常用人失败';

    }







}

































