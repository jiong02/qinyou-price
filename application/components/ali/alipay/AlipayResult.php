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
    public $errorCode;
    public $errorMessage;
    public $outTradeNo;
    public $sign;
    public $qrCode;
    public $status;
    public $response;

    const PRE_CREATE = 'alipay_trade_precreate_response';
    const ERR_CHECK_SIGN = 40001;
    const SUCCESS = 10000;
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAIL = 'FAIL';

    public function setResponse($response, $tradeType)
    {
        $this->sign = $response['sign'];
        $alipay = new Alipay();
        $signData = $alipay->formatSignData($response[$tradeType]);
        $result = $alipay->verifySign($signData, $this->sign);
        $this->response = json_decode($signData);
        if ($result){
            $this->status = self::STATUS_SUCCESS;
            $this->errorCode = self::ERR_CHECK_SIGN;
            $this->outTradeNo = $this->response->out_trade_no;
            $this->errorMessage = 'SUCCESS';
            if ($tradeType == self::PRE_CREATE){
                $this->setPreCreateResponse();
            }
        }else{
            $this->status = self::STATUS_FAIL;
            $this->errorCode = self::ERR_CHECK_SIGN;
            $this->errorMessage = '签名检验失败';
        }
    }

    public function setPreCreateResponse()
    {
        $this->qrCode = $this->response->qr_code;
    }
    /**
     * public function setResponse($response, $tradeType)
    {
    $this->sign = $response['sign'];
    $alipay = new Alipay();
    $signData = $alipay->formatSignData($response[$tradeType]);
    $result = $alipay->verifySign($signData, $this->sign);
    $this->response = json_decode($signData);
    if ($result){
    $this->status = self::STATUS_SUCCESS;
    $this->errorCode = self::ERR_CHECK_SIGN;
    $this->outTradeNo = $this->response->out_trade_no;
    $this->errorMessage = 'SUCCESS';
    if ($tradeType == self::PRE_CREATE){
    $this->setPreCreateResponse();
    }
    }else{
    $this->status = self::STATUS_FAIL;
    $this->errorCode = self::ERR_CHECK_SIGN;
    $this->errorMessage = '签名检验失败';
    }
    }

    public function setPreCreateResponse()
    {
    $this->qrCode = $this->response->qr_code;
    }
     */
}