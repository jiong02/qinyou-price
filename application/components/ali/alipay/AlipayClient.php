<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/26
 * Time: 21:25
 */

namespace app\components\ali\alipay;


use app\components\Curl;
use app\components\Data;
use think\Exception;

class AlipayClient
{
    private $gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    private $alipayPublicKey;
    private $merchantPrivateKey;

    private $appId;
    private $format = 'JSON';
    private $charset = 'utf-8';
    private $signType = 'RSA2';


    /**
     * @param mixed $gatewayUrl
     */
    public function setGatewayUrl($gatewayUrl)
    {
        $this->gatewayUrl = $gatewayUrl;
    }

    /**
     * @param mixed $alipayPublicKey
     */
    public function setAlipayPublicKey($alipayPublicKey)
    {
        $this->alipayPublicKey = $alipayPublicKey;
    }

    /**
     * @param mixed $merchantPrivateKey
     */
    public function setMerchantPrivateKey($merchantPrivateKey)
    {
        $this->merchantPrivateKey = $merchantPrivateKey;
    }

    /**
     * @param mixed $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @param mixed $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @param mixed $charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * @param mixed $signType
     */
    public function setSignType($signType)
    {
        $this->signType = $signType;
    }

    public function getResponseName($method)
    {
       return str_replace('.','_', $method) . '_response';
    }

    public function execute($request)
    {
        //组装参数
        $systemParams['version'] = $request->getVersion();
        $systemParams['method'] = $request->getMethod();
        $systemParams['app_id'] =$this->appId;
        $systemParams['format'] = $this->format;
        $systemParams['charset'] = $this->charset;
        $systemParams['sign_type'] = $this->signType;
        $timestamp = date("Y-m-d H:i:s");
        $systemParams['timestamp'] = $timestamp;
        $requestApiParams = $request->getRequestParams();
        $totalParams = array_merge($systemParams,$requestApiParams);
        $systemParams['sign'] = $this->generateSign($totalParams);
        $url =  $this->gatewayUrl . '?' . http_build_query($systemParams);
        $curl = new Curl();
        $result = $curl->post($url,$requestApiParams);
        $result = json_decode($result);

        //验签
        $responseName = $this->getResponseName($request->getMethod());
        $resultSign = $result->sign;
        $resultSignSourceData = $result->$responseName;
        $signData = json_encode($resultSignSourceData,JSON_UNESCAPED_UNICODE);
        $verifyResult = $this->verifySign($signData,$resultSign);
        if(!$verifyResult) {
            throw new Exception('签名校验失败');
        }
        return $resultSignSourceData;
    }

    private function generateSign($data)
    {
        unset($data['sign']);
        ksort($data);
        $data = Data::toUrlParams($data);
        $sign = $this->sign($data);
        return $sign;
    }

    protected function sign($data) {
        $priKey = $this->merchantPrivateKey;
        $result = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($priKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        if (!$result){
            throw new Exception('您使用的私钥格式错误，请检查RSA私钥配置');
        }
        openssl_sign($data, $sign, $result, OPENSSL_ALGO_SHA256);
        $sign = base64_encode($sign);
        return $sign;
    }

    public function verifySign($data, $sign) {
        $pubKey = $this->alipayPublicKey;
        $res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        if(!$res){
            throw new Exception('支付宝RSA公钥错误。请检查公钥文件格式是否正确');
        }
        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);

        return $result;
    }


}