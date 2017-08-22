<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/22
 * Time: 15:52
 */

namespace app\components\ali\alipay;


class AliPayResult extends AliPay
{
    public $errorCode;
    public $errorMessage;
    public $outTradeNo;
    public $qrCode;
    public $status;
    public $response;

    const PRECREATE = 'alipay_trade_precreate_response';
    const ERR_CHECK_SIGN = 40001;
    const SUCCESS = 10000;
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAIL = 'FAIL';
    public function __construct()
    {
        $this->init();
    }

    public function setResponse($response, $type)
    {

        $this->sign = $response['sign'];
        $signData = $this->formatSignData($response[$type]);
        $result = $this->verifySign($signData, $this->sign);
        $this->response = json_decode($signData);
        if ($result){
            $this->status = self::STATUS_SUCCESS;
            $this->errorCode = self::ERR_CHECK_SIGN;
            $this->outTradeNo = $this->response->out_trade_no;
            $this->errorMessage = 'SUCCESS';
            if ($type == self::PRECREATE){
                $this->setPreCreateResponse();
            }
        }else{
            $this->status = self::STATUS_FAIL;
            $this->errorCode = self::ERR_CHECK_SIGN;
            $this->errorMessage = '签名检验失败';
        }
    }

    public function formatSignData($signData)
    {
        $signData = json_encode($signData, JSON_UNESCAPED_UNICODE);
        return $signData;
    }

    public function setPreCreateResponse()
    {
        $this->qrCode = $this->response->qr_code;
    }

    public function verifySign($data, $sign) {
        $pubKey = $this->getAlipayPublicKey();
        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        if(!$res){
            throw new \think\Exception('支付宝RSA公钥错误。请检查公钥文件格式是否正确');
        }
        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);

        return $result;
    }
}