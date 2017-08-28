<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/28
 * Time: 14:56
 */

namespace app\components\wechat\wechatpay;


class WechatpayRequest
{
    const URL_UNIFIEDORDER = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    const URL_QUERY = 'https://api.mch.weixin.qq.com/pay/orderquery';
    const URL_CLOSE = 'https://api.mch.weixin.qq.com/pay/closeorder';
    const URL_REFUND = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
    const URL_REFUND_QUERY = 'https://api.mch.weixin.qq.com/pay/refundquery';

    const TRADE_TYPE_NATIVE = 'NATIVE';
    const TRADE_TYPE_JSAPI = 'JSAPI';

    private $url;
    private $tradeType;
    private $sslCertPath;
    private $sslKeyPath;
    private $bizContent;
    private $spbillCreateIp;
    private $notifyUrl;
    
    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getTradeType()
    {
        return $this->tradeType;
    }

    /**
     * @param mixed $tradeType
     */
    public function setTradeType($tradeType)
    {
        $this->tradeType = $tradeType;
    }

    /**
     * @return mixed
     */
    public function getSslCertPath()
    {
        return $this->sslCertPath;
    }

    /**
     * @param mixed $sslCertPath
     */
    public function setSslCertPath($sslCertPath)
    {
        $this->sslCertPath = $sslCertPath;
    }

    /**
     * @return mixed
     */
    public function getSslKeyPath()
    {
        return $this->sslKeyPath;
    }

    /**
     * @param mixed $sslKeyPath
     */
    public function setSslKeyPath($sslKeyPath)
    {
        $this->sslKeyPath = $sslKeyPath;
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
    }

    /**
     * @return mixed
     */
    public function getSpbillCreateIp()
    {
        return $this->spbillCreateIp;
    }

    /**
     * @param mixed $spbillCreateIp
     */
    public function setSpbillCreateIp($spbillCreateIp)
    {
        $this->spbillCreateIp = $spbillCreateIp;
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



}