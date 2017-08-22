<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/15
 * Time: 14:51
 */

namespace app\components\ali\alipay;

use Endroid\QrCode\QrCode;

class AliPayQrcodePay extends PayContentBuilder
{
    protected $method = 'alipay.trade.precreate';
    protected $resultType =  'alipay_trade_precreate_response';
    public function qrcodePay($outTradeNo, $body, $fee)
    {
        $this->init();
        $this->setMethod();
        $this->setOutTradeNo($outTradeNo);
        $this->setTotalAmount($fee);
        $this->setSubject($body);
        $result = $this->execute();
        $aliPayResult = new AliPayResult();
        $aliPayResult->setResponse($result,$this->resultType);
        if($aliPayResult->status = 'SUCCESS'){

        }else{

        }
//        halt($systemParams);
//        //检测必填参数

//        $this->params['sign'] = $this->generateSign(array_merge($this->params,$bizContent));
//        $url = self::GATEWAY_URL . '?';
//        $url = $this->toStrParams($this->params, $url);
//        dump($url);
//        halt($bizContent);
//        halt($this->params);
    }

    public function testQrcodePay()
    {
        $this->qrcodePay(123123,123123,1);
    }
}