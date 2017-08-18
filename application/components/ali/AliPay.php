<?php

/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/14
 * Time: 17:03
 */

namespace app\components\ali;

class AliPay
{
    //应用ID
    const APP_ID = "2016071901636648";
    //支付宝网关
    const GATEWAY_URL = "https://openapi.alipay.com/gateway.do";
    //支付宝公钥
    const ALIPAY_PUBLIC_KEY = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAiQbzUm+N5yAV3VgzLUJAB3swqAuuuT8P98r0+gwUnbsUMFRnk3XoS/GONVfz1RDMMIynImVkjGAZNELiICcqoKfje/p0IcQoDEZi+c3VttbD4cUoT9gpIEZ+jWXwqt58FIdUf50qnEhzWxIz8xyUIbBOdnqRKX57Vl8E17C0yIaNW/dwox7RE2WbDdzSDNv7Gk0p1/MUw/9tnE7i9PAcrlC6JUUzOJDx6utc/XAjf3podU8CUJvKTBRNHCBu20EzTh4KvNqkdA1LyD9RIbn/auQB7EtJORlx9OmeY8KLNGQ7vzZVlJ5E0aNdeh8666bYTM4Hx6I2mZXLOO4OzN4w4QIDAQAB";
    //商户私钥
    const MERCHANT_PRIVATE_KEY = "MIIEowIBAAKCAQEAu82zX8BJIcPSuktP9EwW5tUa93kvnzv/uR2T7yMiyYT6Gt9JJYPMAUHao7VzXDCj+f2+pm4cEL6aEbS6ZmMTaMOH8/oPm7nyOPDR+COblkN8gr0fPMc+Y2UU+Xh5hNbxjv4GrGvR76tgmaI92lQApGNY4jUO8lo0lA+w+ZHkXAf5tZDl94276tq7vDyZjdiwOadSTcP+PpFvnARQyGcXkOIY5yz6vCj4T/kyn0nCkwxUlrf7tw/ztk5Gic3w6phxI92A4U7sJDOohECSACet8374zGXXGyp9cNXfjZgNYz+5ebkc+/o9srh2aNuq3jfYYWPpe5jMZH4FSWxSLGpQJQIDAQABAoIBAGdeSSSiyZ30EsDHQzLLzq8vDLC52yRh+dcCGLK/PB5/OsofrDsh19+5R4ZkESLlAtxOdelVIc11m4ezWgWQ8tXvCZ2YPY8RQellY6yYrMKAUsADKHZjlEtRD8JgNUKQrFRwLWwpzFuGkJz/V9wb8F6K8BlR6vAqBlaYbGhxjKe6KaRvtY3kMiAZr4Uu5X1xG5ZWfsMOACyJ3/rE8nM1R5WP+NPmIu7awdtVYUA/Wh0kLKGSCcc0QaHa24FucVfZUCQS6C0YJ+1MZTnCcuJRtEF6CXA2KUYBcxpTZaXstfYUAqhdTqC0J5zWEIf9k7iHlJmNpXWuXG7ppGXi+X0YuAECgYEA8J6p31b3zTETvq+b1Iu9f5DIHlmU5b3UebfKWzA9yx3EFERc95YfK29DiRKtHM/NQ8w5KQVW8bK42QDZZt9CPgtCNegxmUbqrBYITF2n0APHEk37aBHqTaqUn6WZFJCx8B8SbgfB78PKum+BiABM7bS27BwRL/isFTrG3No2NmUCgYEAx87JxcAG28n87YA73F5u0Ba3pq+QyH0IfxWki+CqVEO2hzEZUUr1abRuJk0wh89ojkfO2jyNZgI++k6wncHoSFlAiSRf/8Nr8Rasr24evATZ1NIa/pdll/y2jDPx1EvP3ijcAKr1wvC0hf6WvvSpP30jiyyhxPplh7Udtn+qNsECgYEA0vUoVek3pKysdPgdlUFWyKq06Pb9NlcyG+zo+v3Wj2fvax1srJzvgvMvsNOw9puxiQlZ6/8EdS+OJKM795cxypewWvbR1WJ5iJpgeCN8Z0GInSHFkz5xv9oYJ8fV6FPbzXxQeitO+tkbukzcsdIhoB5aabNJ1lcc+BfqFeMyuIkCgYByfzIqqp6Dhlz08D3dSxPvFIWK9CJgcR3UTV+sdELG5MKM9/rNFcpKF4XjVupPePAuUEHd10MjyHe0UjFtRXfJNbQAoqKMWrzZO6gbI1xjW9hD1152s+UY0kz9TKrwf70PTpS7oTwRyIN6IWja5jKyWhBrKVlOGjriKExtjvzIQQKBgAdYzNjzmw+PcxY/a5jEUn9adSgxkmaKEG3/DSWL4g38FlvFdwmyEuQL9orCW5DtHh7q5vqUh8xzXYXRhY8QMO7dKM+z1SrhLe1o5IpQs6qDdzoLW0PZq/R4fSqFiZLyt/2Ycjv4O08qWc6yB21X2qOHFZOzeEPsc95m8RsH3WkE";
    //最大查询重试次数
    protected $maxQueryRetry = "10";
    //查询间隔
    protected $queryDuration = "3";
    //版本
    protected $version = '1.0';
    //签名算法类型
    protected $signType = 'RSA2';
    //返回数据格式
    protected $format = "json";
    //编码格式
    protected $charset = "UTF-8";
    //异步通知地址,只有扫码支付预下单可用
    protected $notifyUrl = "http://www.baidu.com";

    protected $params = array();

    public function __construct()
    {
        $this->params['app_id'] = self::APP_ID;
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