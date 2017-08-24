<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/23
 * Time: 20:24
 */

namespace app\components\ali\alipay;

/**
 * Class AlipayQuery
 * @package app\components\ali\alipay
 * 支付宝查询类
 */
class AlipayQuery extends Alipay
{
    public $method = 'alipay.trade.query';
    public $responseType = 'alipay_trade_query_response';
    public $bizContent;
    public $result;

    public function query($outTradeNo)
    {
        $this->buildQueryContent($outTradeNo);
        $this->setMethod($this->method);
        $this->execute();
        return $this->queryResult();
    }

    public function buildQueryContent($outTradeNo)
    {
        $alipayContentBuilder = new AlipayContentBuilder();
        $alipayContentBuilder->setOutTradeNo($outTradeNo);
        $bizContent = $alipayContentBuilder->getBizContent();
        $this->setBizContent($bizContent);
    }

    public function queryResult()
    {
        //接收并分析返回结果
        $alipayResult = new AlipayResult();
        $alipayResult->setResponse($this->result,$this->responseType);
        if($alipayResult->status == 'SUCCESS'){

        }else{
            return getError($alipayResult->errorMessage);
        }
    }
}