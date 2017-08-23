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
class AlipayQuery
{
    public static $method = 'alipay.trade.query';
    public static $responseType = 'alipay_trade_query_response';
    public static $bizContent;
    public static $result;

    public static function query($outTradeNo)
    {
        self::buildQueryContent($outTradeNo);
        self::execute();
        return self::queryResult();
    }

    public static function buildQueryContent($outTradeNo)
    {
        $alipayContentBuilder = new AlipayContentBuilder();
        $alipayContentBuilder->setOutTradeNo($outTradeNo);
        $bizContent = $alipayContentBuilder->getBizContent();
        self::$bizContent = $bizContent;
    }

    public static function execute()
    {
        //集成支付信息并发送支付请求
        $alipay = new Alipay();
        $alipay->setMethod(self::$method);
        $alipay->setBizContent(self::$bizContent);
        $result = $alipay->execute();
        self::$result = $result;
    }

    public static function queryResult()
    {
        //接收并分析返回结果
        $alipayResult = new AlipayResult();
        $alipayResult->setResponse(self::$result,self::$responseType);
        if($alipayResult->status == 'SUCCESS'){

        }else{
            return getError($alipayResult->errorMessage);
        }
    }
}