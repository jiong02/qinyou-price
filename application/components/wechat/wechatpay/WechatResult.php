<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/28
 * Time: 16:10
 */

namespace app\components\wechat\wechatpay;


class WechatResult
{
    private $tradeStatus;
    private $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function setTradeStatus($tradeStatus)
    {
        $this->tradeStatus = $tradeStatus;
    }

    public function getTradeStatus()
    {
        return $this->tradeStatus;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}