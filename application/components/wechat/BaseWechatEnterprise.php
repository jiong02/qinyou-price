<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/17
 * Time: 10:45
 */

namespace app\components\wechat;


use app\components\Curl;
use think\Cache;

class BaseWechatEnterprise
{
    const CORPID ='wx8765c2c8e7cb87b5';
    const SECRET = 'yjodPBYc8xv3rB43txmWxwDVU7paNh7MgAgruLuz2Hc';
    protected $agentId = '1000002';

    public function getAccessToken()
    {
        $accessToken = Cache::get(self::CORPID . '_access_token');
        if (!$accessToken) {
            $accessToken = $this->generateAccessToken();
        }
        return $accessToken;
    }

    public function generateAccessToken()
    {
        $params['corpid'] = self::CORPID;
        $params['corpsecret'] = self::SECRET;
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken';
        $curl = new Curl();
        $result = $curl->get($url, $params);
        if ($result && $result['errcode'] === 0 && $result['errmsg'] === 'ok'){
            Cache::set(self::CORPID . '_access_token', $result['access_token'], $result['expires_in']);
            return $result['access_token'];
        }
        return false;
    }
}