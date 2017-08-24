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
use think\Exception;

class AlipayQuery extends Alipay
{
    private $method = 'alipay.trade.query';
    private $responseType = 'alipay_trade_query_response';

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
        if($alipayResult->getStatus() == 'SUCCESS'){

        }else{
            return getError($alipayResult->getErrorMessage());
        }
    }

    public function loopQuery($outTradeNo)
    {
        $alipayResult = new AlipayResult();
        for ($i = 0; $i < $this->maxQueryRetry; $i++){
            try{
                sleep($this->queryDuration);
            }catch (Exception $e){
                $alipayResult->setStatus($alipayResult::STATUS_FAIL);
                $alipayResult->setErrorCode($e->getCode());
                $alipayResult->setErrorMessage($e->getMessage());
            }
            $this->buildQueryContent($outTradeNo);
            $this->setMethod($this->method);
            $this->execute();
            $alipayResult->setResponse($this->result,$this->responseType);
            if($alipayResult->stopQuery()){
                return $this->loopQueryResult($alipayResult);
            }
        }
        return $this->loopQueryResult($alipayResult);
    }

    public function loopQueryResult($alipayResult)
    {
        if($alipayResult->getStatus() == 'SUCCESS'){

        }else{
            return getError($alipayResult->getErrorMessage());
        }
    }
}