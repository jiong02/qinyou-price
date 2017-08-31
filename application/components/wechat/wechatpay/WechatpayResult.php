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
    private $errorCode;
    private $errorMessage;
    private $responseType;
    private $sign;
    private $status;
    private $result;
    private $curlResponse;

    private $outTradeNo;
    private $tradeStatus;
    private $qrCode;

    const NATIVE = 'NATIVE';
    const QUERY = 'QUERY';

    const TRADE_SUCCESS = 'SUCCESS';
    const TRADE_REFUND = 'REFUND';
    const TRADE_NOTPAY = 'NOTPAY';
    const TRADE_CLOSED = 'CLOSED';
    const TRADE_REVOKED = 'REVOKED';
    const TRADE_USERPAYING = 'USERPAYING';
    const TRADE_PAYERROR = 'PAYERROR';

    const SUCCESS = 'SUCCESS';
    const FAIL = 'FAIL';

    const ERR_CURL_ERROR_CODE = 'CURLERROR';
    const ERR_CURL_ERROR_MESSAGE = 'CURL出错';
    const ERR_CHECK_SIGN_CODE = 'SIGNERROR';
    const ERR_CHECK_SIGN_MESSAGE = '签名检验失败';

    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function setOutTradeNo($outTradeNo)
    {
        $this->outTradeNo = $outTradeNo;
    }

    public function getOutTradeNo()
    {
        return $this->outTradeNo;
    }

    public function setResponseType($responseType)
    {
        $this->responseType = $responseType;
    }

    public function getResponseType()
    {
        return $this->responseType;
    }

    public function setTradeStatus($tradeStatus)
    {
        $this->tradeStatus = $tradeStatus;
    }

    public function getTradeStatus()
    {
        return $this->tradeStatus;
    }

    public function setSign($sign)
    {
        $this->sign = $sign;
    }

    public function getSign()
    {
        return $this->sign;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setQrCode($qrCode)
    {
        $this->qrCode = $qrCode;
    }

    public function getQrCode()
    {
        return $this->qrCode;
    }

    /**
     * @return mixed
     */
    public function getCurlResponse()
    {
        return $this->curlResponse;
    }

    /**
     * @param mixed $curlResponse
     */
    public function setCurlResponse($curlResponse)
    {
        $this->curlResponse = $curlResponse;
    }

    public function setCommonResponse()
    {
        $this->setSign($this->curlResponse['result']['sign']);
        unset($this->curlResponse['result']['sign']);
        $this->setResult($this->curlResponse['result']);
        if (array_key_exists('trade_type',$this->result)){
            $this->setResponseType($this->result['trade_type']);
        }else{
            $this->setResponseType(self::QUERY);
        }
        $this->setErrorCode($this->result['return_code']);
        $this->setErrorMessage($this->result['return_msg']);
    }

    public function setResponse($response)
    {
        if($this->judgeResponse($response)){
            if ($response['return_code'] == self::SUCCESS){
                $this->setCommonResponse();
                $wechatpay = new WechatpayService();
                $result = $wechatpay->verifySign($this->result, $this->sign);
                if ($result){
                    if ($this->result['result_code'] == self::SUCCESS){
                        $this->setSuccessResponse();
                    }else{
                        $this->setFailResponse();
                    }
                }else{
                    $this->setFailResponse(self::ERR_CHECK_SIGN_CODE, self::ERR_CHECK_SIGN_MESSAGE);
                }
            }else{
                $this->setFailResponse($this->result['return_code'], $this->result['return_msg']);
            }
        }
    }

    public function judgeResponse($response)
    {
        $this->setCurlResponse($response);
        if ($response['status'] == self::SUCCESS){
            return true;
        }else{
            $this->setFailResponse($response['error_code'],$response['error_message']);
        }
    }

    public function setSuccessResponse()
    {
        $this->setStatus(self::SUCCESS);
        if ($this->getResponseType() == self::NATIVE){
            $this->setNativeResponse();
        }elseif($this->getResponseType() == self::QUERY){
            $this->setQueryResponse();
        }
    }

    public function setFailResponse($errorCode = '', $errorMessage = '')
    {
        $errorCode =  $errorCode ? $errorCode : $this->result['err_code'];
        $errorMessage = $errorMessage ? $errorMessage : $this->result['err_code_des'];
        $this->setStatus(self::FAIL);
        $this->setErrorCode($errorCode);
        $this->setErrorMessage($errorMessage);
    }

    public function setNativeResponse()
    {
        $this->setQrCode($this->result['code_url']);
    }

    public function setQueryResponse()
    {
        if ($this->result['trade_state'] != self::SUCCESS){
            $this->setStatus(self::FAIL);
        }
        $this->setErrorCode($this->result['trade_state']);
        $this->setErrorMessage($this->result['trade_state_desc']);
        $this->setOutTradeNo($this->result['out_trade_no']);
        $this->setTradeStatus($this->result['trade_state']);
    }

    // 判断是否停止查询
    public function stopQuery(){
        if($this->getResult()['result_code'] == self::SUCCESS){
            if($this->getTradeStatus() == self::TRADE_NOTPAY || $this->getTradeStatus() == self::TRADE_USERPAYING){
                return false;
            }
        }
        return true;
    }
}
