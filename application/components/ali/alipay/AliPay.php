<?php

/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/14
 * Time: 17:03
 */

namespace app\components\ali\alipay;

use think\Config;

class AliPay
{
    //应用ID
    private $appId;
    //支付宝网关
    private $gatewayUrl;
    //支付宝公钥
    private $alipayPublicKey;
    //商户私钥
    private $merchantPrivateKey;
    //最大查询重试次数
    protected $maxQueryRetry;
    //查询间隔
    protected $queryDuration;
    //版本
    protected $version;
    //签名算法类型
    protected $signType;
    //返回数据格式
    protected $format;
    //编码格式
    protected $charset;
    //异步通知地址,只有扫码支付预下单可用
    protected $notifyUrl;

    protected $params = array();

    public function __construct($config)
    {

        $this->params['app_id'] = $this->appId;
        $this->params['format'] = $this->format;
        $this->params['charset'] = $this->charset;
        $this->params['sign_type'] = $this->signType;
        $this->params['timestamp'] = date("Y-m-d H:i:s");
        $this->params['version'] = $this->version;
        $this->params['notify_url'] = urlencode($this->notifyUrl);

        if(empty(self::APP_ID) || trim(self::APP_ID)==""){
            echo "appid should not be NULL!";
            exit;
        }
        if(empty(self::MERCHANT_PRIVATE_KEY) || trim(self::MERCHANT_PRIVATE_KEY)==""){
            echo "private_key should not be NULL!";
            exit;
        }
        if(empty(self::ALIPAY_PUBLIC_KEY) || trim(self::ALIPAY_PUBLIC_KEY)==""){
            echo "alipay_public_key should not be NULL!";
            exit;
        }
        if(empty($this->charset) || trim($this->charset)==""){
            echo "charset should not be NULL!";
            exit;
        }
        if(empty($this->queryDuration) || trim($this->queryDuration)==""){
            echo "QueryDuration should not be NULL!";
            exit;
        }
        if(empty(self::GATEWAY_URL) || trim(self::GATEWAY_URL)==""){
            echo "gateway_url should not be NULL!";
            exit;
        }
        if(empty($this->maxQueryRetry) || trim($this->maxQueryRetry)==""){
            echo "MaxQueryRetry should not be NULL!";
            exit;
        }
        if(empty($this->signType) || trim($this->signType)==""){
            echo "sign_type should not be NULL";
            exit;
        }
    }

    /**
     * 支付宝签名制作
     * @param array 支付参数
     */
    protected function generateSign($data)
    {
        ksort($data);
        $data = $this->toStrParams($data);
        $priKey = file_get_contents('/data/wwwroot/price.cheeruislands.com/cacert/rsa_private_key.pem');
        $res = openssl_get_privatekey($priKey);

        if (!$res) {
            echo '您使用的私钥格式错误，请检查RSA私钥配置';
            exit;
        }
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 将数组格式化为字符串
     * @param array $data
     */
    protected function toStrParams($data,$url = '',$encode = false)
    {
        foreach ($data as $k => $v) {
            $v = $encode ? urlencode($v) :$v;
            $url .= $k . "=" . $v . "&";
        }
        unset ($k, $v);
        $url = trim($url, "&");
        return $url;
    }

    protected function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset) {

        if (!empty($data)) {
            $fileType = "UTF-8";
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
                //				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }


        return $data;
    }
}