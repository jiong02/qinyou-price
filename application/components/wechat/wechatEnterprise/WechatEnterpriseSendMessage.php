<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/16
 * Time: 21:52
 */

namespace app\components\wechat\wechatEnterprise;


use app\components\Curl;

class WechatEnterpriseSendMessage extends WechatEnterprise
{
    private $userId;
    private $url;
    private $partyId;
    private $tagId;
    private $msgType;
    private $safe;
    private $textContent;
    private $params = [];
    public $invalidUser;
    public function __construct()
    {
        $this->init();
        $this->setUrl();
        $this->setAgentId();
    }

    public function setAgentId($agentId = '')
    {
        if (!$agentId){
            $agentId = $this->agentId;
        }
        $this->params['agentid'] = $agentId;
        $this->agentId = $agentId;
    }

    public function getAgentId()
    {
        return $this->agentId;
    }

    public function setUserId($userId)
    {
        if (is_array($userId)){
            $userId = $this->toString($userId);
        }
        $this->params['touser'] = $userId;
        $this->userId = $userId;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setPartyId($partyId)
    {
        $this->partyId = $partyId;
        $this->params['toparty'] = $this->partyId;
    }

    public function getPartyId()
    {
        return $this->partyId;
    }

    public function setTagId($tagId)
    {
        $this->params['totag'] = $this->tagId;
        $this->tagId = $tagId;
    }

    public function getTagId()
    {
        return $this->tagId;
    }

    public function setMessageType($msgType)
    {
        $this->msgType = $msgType;
        $this->params['msgtype'] = $this->msgType;
    }

    public function getMessageType()
    {
        return $this->msgType;
    }

    public function setSafe($safe)
    {
        $this->safe = $safe;
        $this->params['safe'] = $safe;
    }

    public function getSafe()
    {
        return $this->safe;
    }

    public function setTextContent($textContent)
    {
        $this->textContent = $textContent;
        $this->params['text']['content'] = $this->textContent;
    }

    public function getTextContent()
    {
        return $this->textContent;
    }

    public function setParams()
    {
        $this->params = json_encode($this->params, JSON_UNESCAPED_UNICODE);
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setUrl()
    {
        $accessToken = $this->getAccessToken();
        $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$accessToken;
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function toString($data)
    {
        $string = '';
        foreach ($data as $datum) {
            $string .= $datum . '|';
        }
        $string = trim($string,'|');
        return $string;
    }

    public function sendMessage()
    {
        if (!$this->getTextContent()){
            $this->status = self::FAIL;
            $this->errorMessage = '请设置发送内容';
            return false;
        }
        $this->setParams();
        $curl = new Curl();
        $result = $curl->post($this->url,$this->params);
        $result = json_decode($result,true);
        $result = $this->formatResult($result);
        if ($this->status == self::SUCCESS) {
            $this->invalidUser = $result['invaliduser'];
            return true;
        }else{
            return false;
        }
    }

    public function sendTextMessage($userId, $textContent)
    {
        $this->setUserId($userId);
        $this->setTextContent($textContent);
        $this->setMessageType('text');
        $result = $this->sendMessage();
        return $result;
    }
}