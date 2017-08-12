<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/17
 * Time: 17:52
 */

namespace app\test\controller;


use app\components\Curl;
use app\components\wechat\BaseWechatEnterprise;

class SendMessageWechatEnterpriseController extends BaseWechatEnterprise
{
    public $userId = '@all';
    public $url;
    public $partyId = '@all';
    public $tagId = '@all';
    public $msgType = 'text';
    public $safe = 0;
    public $params = [];

    public function __construct()
    {
        $this->setUrl();
    }

    public function setUrl()
    {
        $accessToken = $this->getAccessToken();
        $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$accessToken;
        $this->url = $url;
    }

    public function formatParams()
    {
        $this->params['touser'] = $this->userId;
        $this->params['toparty'] = $this->partyId;
        $this->params['totag'] = $this->tagId;
        $this->params['msgtype'] = $this->msgType;
        $this->params['agentid'] = self::AGENTID;
        $this->params['safe'] = $this->safe;
        $this->params = json_encode($this->params, JSON_UNESCAPED_UNICODE);
    }

    public function sendTextMessage($message)
    {
        $this->params['text']['content'] = $message;
        $this->formatParams();
        $curl = new Curl();
        $curl->post($this->url,$this->params);
    }
}