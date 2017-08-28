<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/27
 * Time: 15:36
 */

namespace app\components\ali\alipay;


use think\Exception;

class AlipayRequest
{
    private $method;
    private $version = '1.0';
    private $notifyUrl;
    private $bizContent;

    private $requestParams = array();

    const METHOD_PAY = 'alipay.trade.pay';
    const METHOD_CREATE = 'alipay.trade.create';
    const METHOD_PRECREATE = 'alipay.trade.precreate';
    const METHOD_QUERY = 'alipay.trade.query';
    const METHOD_REFUND = 'alipay.trade.refund';
    const METHOD_REFUND_QUERY = 'alipay.trade.fastpay.refund.query';
    const METHOD_CLOSE = 'alipay.trade.close';

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $methodList = [
            self::METHOD_PAY,
            self::METHOD_CREATE,
            self::METHOD_PRECREATE,
            self::METHOD_QUERY,
            self::METHOD_REFUND,
            self::METHOD_REFUND_QUERY,
            self::METHOD_CLOSE,
        ];
        if (array_search($method,$methodList)){
            $this->method = $method;
        }else{
            throw new Exception('方法设置错误');
        }
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    /**
     * @param mixed $notifyUrl
     */
    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
    }

    /**
     * @return mixed
     */
    public function getBizContent()
    {
        return $this->bizContent;
    }

    /**
     * @param mixed $bizContent
     */
    public function setBizContent($bizContent)
    {
        $this->bizContent = $bizContent;
        $this->requestParams['biz_content'] = $bizContent;
    }

    /**
     * @return array
     */
    public function getRequestParams()
    {
        return $this->requestParams;
    }


}