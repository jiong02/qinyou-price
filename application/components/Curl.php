<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/17
 * Time: 11:44
 */

namespace app\components;


class Curl
{
    private $timeout = 30;
    private $url = '';
    private $params;
    private $sslCertPath;
    private $sslKeyPath;

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function SetSslCertPath($sslCertPath)
    {
        $this->sslCertPath = $sslCertPath;
    }

    public function SetSslKeyPath($sslCertPath)
    {
        $this->sslCertPath = $sslCertPath;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function toUrlParams($params, $buff = "")
    {
        foreach ($params as $k => $v)
        {
            if($v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        $this->url = $this->url . '?' . $buff;
    }

    public function get($url, $params)
    {
        $this->setUrl($url);
        $this->toUrlParams($params);
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以json形式返回
        $result = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($result,true);
        return $data;
    }

    public function post($url, $params, $useCert = false)
    {
        $this->setUrl($url);
        $this->setParams($params);
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch,CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($useCert == true){
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $this->sslCertPath);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $this->sslKeyPath);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
        //运行curl，结果以json形式返回
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    protected static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            //"curl出错，错误码:$error"
        }
    }
}