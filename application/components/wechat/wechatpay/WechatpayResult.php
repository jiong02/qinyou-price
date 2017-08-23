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
    public $outTradeNo;
    public $tradeType;
    public $sign;
    public $status;
    public $result;
    public $subCode;
    public $subMessage;

    public $qrCode;

    const CODE_SUCCESS = 10000;

    const NATIVE = 'NATIVE';
    const SUCCESS = 'SUCCESS';
    const FAIL = 'FAIL';
    const ERR_CHECK_SIGN_CODE = 'SIGNERROR';
    const ERR_CHECK_SIGN_MESSAGE = '签名检验失败';

    public function setCommonResponse($response)
    {
        $this->sign = $response['sign'];
        unset($response['sign']);
        $this->result = $response;
        $this->tradeType = $response['trade_type'];
        $this->errorCode = $response['return_code'];
        $this->errorMessage = $response['return_msg'];
    }

    public function setResponse($response)
    {
        if ($response['return_code'] == self::SUCCESS){
            $this->setCommonResponse($response);
            $wechatpay = new Wechatpay();
            $result = $wechatpay->verifySign($this->result, $this->sign);
            if ($result){
                if ($response['result_code'] == self::SUCCESS){
                    $this->setSuccessResponse();
                }else{
                    $this->setFailResponse();
                }
            }else{
                $this->setFailResponse(self::ERR_CHECK_SIGN_CODE, self::ERR_CHECK_SIGN_MESSAGE);
            }
        }else{
            $this->setFailResponse($response['return_code'], $response['return_msg']);
        }

    }

    public function setSuccessResponse()
    {
        $this->status = self::SUCCESS;
        if ($this->tradeType == self::NATIVE){
            $this->setNativeResponse();
        }
    }

    public function setFailResponse($errorCode = '', $errorMessage = '')
    {
        $this->status = self::FAIL;
        $this->errorCode = $errorCode ? $errorCode : $this->result['err_code'];
        $this->errorMessage = $errorMessage ? $errorMessage : $this->result['err_code_des'];
    }

    public function setNativeResponse()
    {
        $this->qrCode = $this->result['code_url'];
    }
}