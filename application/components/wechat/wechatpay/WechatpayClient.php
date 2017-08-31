<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/28
 * Time: 14:08
 */

namespace app\components\wechat\wechatpay;


use app\components\Curl;
use app\components\Data;
use think\Exception;

class WechatpayClient
{
    private $appId;
    private $appSecret;
    private $merchantId;
    private $key;

    /**
     * @param mixed $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @param mixed $appSecret
     */
    public function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;
    }

    /**
     * @param mixed $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }


    public function MakeSign($signData)
    {
        //签名步骤一：按字典序排序参数
        ksort($signData);
        $string = Data::ToUrlParams($signData);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $this->key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    public function verifySign($signData, $resultSign)
    {
        $sign = $this->MakeSign($signData);
        if ($resultSign == $sign){
            return true;
        }else{
            return false;
        }
    }

    public function execute($request, $useCert = false)
    {
        $bizContent = $request->getBizContent();
        $systemParams['appid'] = $this->appId;
        $systemParams['mch_id'] = $this->merchantId;
        $systemParams['nonce_str'] = Data::generateNonceString();
        $totalParams = array_merge($systemParams, $bizContent);
        $totalParams['sign'] = $this->makeSign($totalParams);
        $xmlApiParams = Data::formatArraytoXml($totalParams);
        $curl = new Curl();
        if ($useCert){
            $curl->setSslCertPath($request->getSslCertPath());
            $curl->setSslKeyPath($request->getSslKeyPath());
        }
        $result = $curl->post($request->getUrl(),$xmlApiParams, $useCert);
        $result = Data::formatXmlToArray($result);
        if (array_key_exists('sign',$result)){
            $resultSign = $result['sign'];
            unset($result['sign']);
            $verifyResult = $this->verifySign($result,$resultSign);
            if(!$verifyResult) {
                throw new Exception('签名校验失败');
            }
            return $result;
        }else{
            throw new Exception($result['return_msg']);
        }

    }
}