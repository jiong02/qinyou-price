<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/15
 * Time: 14:51
 */

namespace app\components\ali;


class QrcodePay extends PayContentBuilder
{
    private $method = 'alipay.trade.precreate';

    public function qrcodePay($outTradeNo, $body, $fee)
    {
        $this->setOutTradeNo($outTradeNo);
        $this->setTotalAmount($fee);
        $this->setSubject($body);
        $this->params['method'] = $this->method;
        $bizContent['bizContent'] = $this->getBizContent();
        //检测必填参数
        if(!$this->getOutTradeNo()) {
            echo "缺少统一支付接口必填参数out_trade_no！";
            exit;
        }else if(!$this->getSubject()){
            echo "缺少统一支付接口必填参数body！";
            exit;
        }else if(!$this->getTotalAmount()) {
            echo "缺少统一支付接口必填参数total_fee！";
            exit;
        }
        $this->params['sign'] = $this->generateSign(array_merge($this->params,$bizContent));
        $url = self::GATEWAY_URL . '?';
        $url = $this->toStrParams($this->params, $url);
        dump($url);
        halt($bizContent);
        halt($this->params);
    }
}