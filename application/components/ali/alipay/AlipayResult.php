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
    public $tradeType;
    public $sign;
    public $status;
    public $result;
    public $subCode;
    public $subMessage;

    public $qrCode;

    const CODE_SUCCESS = 10000;
    const ERR_CHECK_SIGN = 40001;
    const PRE_CREATE = 'alipay_trade_precreate_response';
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAIL = 'FAIL';

    public function setCommonResponse($response)
    {
        $result = $response[$this->tradeType];
        $this->sign = $response['sign'];
        $this->result = $result;
        $this->errorCode = $result['code'];
        $this->errorMessage = $result['msg'];
    }

    public function setResponse($response, $tradeType)
    {
        $this->tradeType = $tradeType;
        $this->setCommonResponse($response);
        $alipay = new Alipay();
        $signData = $alipay->formatSignData($this->result);
        $result = $alipay->verifySign($signData, $this->sign);
        if ($result){
            if ($this->errorCode == self::CODE_SUCCESS) {
                $this->setSuccessResponse();
            }else{
                $this->setFailResponse();
            }
        }else{
            $this->status = self::STATUS_FAIL;
            $this->subCode = self::ERR_CHECK_SIGN;
            $this->subMessage = '签名校验失败';
        }
    }

    public function setSuccessResponse()
    {
        $this->status = self::STATUS_SUCCESS;
        $this->outTradeNo = $this->result['out_trade_no'];
        if ($this->tradeType == self::PRE_CREATE) {
            $this->setPreCreateResponse();
        }
    }
    public function setFailResponse()
    {
        $this->status = self::STATUS_FAIL;
        $this->subCode = $this->result['sub_code'];
        $this->subMessage = $this->result['sub_msg'];
    }

    public function setPreCreateResponse()
    {
        $this->qrCode = $this->result['qr_code'];
    }
}