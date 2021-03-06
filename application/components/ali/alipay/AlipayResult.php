<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/27
 * Time: 23:58
 */

namespace app\components\ali\alipay;


class AlipayResult
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