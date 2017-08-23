<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/23
 * Time: 14:59
 */

namespace app\components\wechat\wechatpay;


class WechatpayResult
{
    public $errorCode;
    public $errorMessage;
    public $sign;
    public $qrCode;
    public $status;
    public $response;

    const ERR_CHECK_SIGN = 40001;
    const SUCCESS = 10000;
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAIL = 'FAIL';
    const NATIVE = 'NATIVE';

    public function setResponse($response, $tradeType)
    {
        $this->response = $response;
        $this->sign = $response['sign'];
        unset($response['sign']);
        $wechatpay = new Wechatpay();
        $result = $wechatpay->verifySign($response, $this->sign);
        if ($result){
            $this->status = self::STATUS_SUCCESS;
            $this->errorCode = self::ERR_CHECK_SIGN;
            $this->errorMessage = 'SUCCESS';
            if ($tradeType == self::NATIVE){
                $this->setNativeResponse();
            }
        }else{
            $this->status = self::STATUS_FAIL;
            $this->errorCode = self::ERR_CHECK_SIGN;
            $this->errorMessage = '签名检验失败';
        }
    }

    public function setNativeResponse()
    {
        $this->qrCode = $this->response['code_url'];
    }
}