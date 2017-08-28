<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/22
 * Time: 15:52
 */

namespace app\components\ali\alipay;


class AlipayResult
{
    private $errorCode;
    private $errorMessage;
    private $responseType;
    private $sign;
    private $status;
    private $result;
    private $curlResponse;

    private $outTradeNo;
    private $qrCode;
    private $tradeStatus;

    const CODE_SUCCESS = 10000;
    const ERR_CHECK_SIGN_CODE = 40001;
    const ERR_CHECK_SIGN_MESSAGE = '签名检验失败';

    const RESPONSE_QUERY = 'alipay_trade_precreate_response';
    const RESPONSE_PRE_CREATE = 'alipay_trade_precreate_response';
    const RESPONSE_REFUND = 'alipay_trade_precreate_response';

    const TRADE_FINISHED = 'TRADE_FINISHED';
    const TRADE_SUCCESS = 'TRADE_SUCCESS';
    const TRADE_CLOSED = 'TRADE_CLOSED';

    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAIL = 'FAIL';

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
        $result = $this->curlResponse['result'][$this->getResponseType()];
        $this->setSign($this->curlResponse['result']['sign']);
        $this->setResult($result);
        $this->setErrorCode($result['code']);
        $this->setErrorMessage($result['msg']);
    }

    public function setResponse($response, $responseType)
    {
        if($this->judgeResponse($response)){
            $this->setResponseType($responseType);
            $this->setCommonResponse();
            $alipay = new Alipay();
            $signData = $alipay->formatSignData($this->getResult());
            $result = $alipay->verifySign($signData, $this->getSign());
            if ($result){
                if ($this->getErrorCode() == self::CODE_SUCCESS) {
                    $this->setSuccessResponse();
                }else{
                    $this->setStatus(self::STATUS_FAIL);
                }
            }else{
                $this->setFailResponse(self::ERR_CHECK_SIGN_CODE,self::ERR_CHECK_SIGN_MESSAGE);
            }
        }
    }

    public function judgeResponse($response)
    {
        $this->setCurlResponse($response);
        if ($response['status'] == self::STATUS_SUCCESS){
            return true;
        }else{
            $this->setFailResponse($response['error_code'],$response['error_message']);
        }
    }

    public function setSuccessResponse()
    {
        $this->setStatus(self::STATUS_SUCCESS);
        $this->setOutTradeNo($this->result['out_trade_no']);
        if ($this->getResponseType() == self::RESPONSE_PRE_CREATE) {
            $this->setPreCreateResponse();
        }elseif($this->getResponseType() == self::RESPONSE_QUERY){
            $this->setQueryResponse();
        }elseif($this->getResponseType() == self::RESPONSE_REFUND){

        }
    }
    public function setFailResponse($errorCode, $errorMessage)
    {
        $this->setStatus(self::STATUS_FAIL);
        $this->setErrorCode($errorCode);
        $this->setErrorMessage($errorMessage);
    }

    public function setPreCreateResponse()
    {
        $this->setQrCode($this->result['qr_code']);
    }

    public function setQueryResponse()
    {
        $this->SetTradeStatus($this->result['trade_status']);
    }

    // 判断是否停止查询
    public function stopQuery(){
        if($this->errorCode == self::CODE_SUCCESS){
            if($this->getTradeStatus() == self::TRADE_FINISHED ||
                $this->getTradeStatus() == self::TRADE_SUCCESS ||
                $this->getTradeStatus() == self::TRADE_CLOSED){
                return true;
            }
        }
        return false;
    }
}