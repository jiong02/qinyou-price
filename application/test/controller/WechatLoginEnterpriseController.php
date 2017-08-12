<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/17
 * Time: 15:57
 */

namespace app\test\controller;


use app\components\Curl;
use app\components\Response;
use app\components\String;
use app\components\wechat\BaseWechatEnterprise;
use think\Cache;
use think\Request;

class WechatLoginEnterpriseController extends BaseWechatEnterprise
{
    public function sendMessage()
    {
        $sendMessageController = new SendMessageWechatEnterpriseController();
        $sendMessageController->sendTextMessage('欢迎各位大哥光临小店');
    }

    public function login(Request $request)
    {
        if ($request->has('code') && $request->has('state')){
            $code = $request->get('code');
            if (Cache::has($request->get('state')) && !Cache::has($code)){
                $userId = $this->getUserIdByCode($code);
                if ($userId === false){
                    return Response::Error('登录失败,详情请联系客服');
                }
            }
        }
        $url = $this->formatRedirectUrl();
        return redirect($url);

    }

    public function formatRedirectUrl()
    {
        $params['appid'] = self::CORPID;
        $params['agentid'] = self::AGENTID;
        $params['redirect_uri'] = urlencode('http://' . $_SERVER['HTTP_HOST'] . '/Admin/login');
        $params['state'] = $this->getUniqueState();
        $baseUrl = 'https://open.work.weixin.qq.com/wwopen/sso/qrConnect?';
        $url = String::toUrlParams($params, $baseUrl);
        return $url;
    }

    public function getUserIdByCode($code)
    {
        $params['access_token'] = $this->getAccessToken();
        $params['code'] = $code;
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo';
        $curl = new Curl();
        $result = $curl->get($url, $params);
        if ($result['errcode'] === 0 && $result['errmsg'] === 'ok'){
            Cache::set($code,1);
            return $result['UserId'];
        }
        return false;
    }

    public function getUniqueState()
    {
        do{
            $state = String::generateNonceStringWithNoCapital('16');
        }while(Cache::get($state));
        Cache::set($state, 1, 360);
        return $state;
    }
}