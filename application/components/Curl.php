<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/17
 * Time: 11:44
 */

namespace app\components;


use think\Exception;

class Curl
{
    private $timeout = 30;
    private $sslCertPath;
    private $sslKeyPath;
    private $errorCode;
    private $result;

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @param mixed $sslCertPath
     */
    public function setSslCertPath($sslCertPath)
    {
        $this->sslCertPath = $sslCertPath;
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
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }


    public function get($url, $params = array())
    {
        $url = $url . '?' . $params;
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
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
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($useCert == true){
            if ($this->sslCertPath && $this->sslKeyPath){
                //使用证书：cert 与 key 分别属于两个.pem文件
                curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
                curl_setopt($ch,CURLOPT_SSLCERT, $this->sslCertPath);
                curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
                curl_setopt($ch,CURLOPT_SSLKEY, $this->sslKeyPath);
            }else{
                throw new Exception('请设置证书路径');
            }
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        //运行curl
        $result = curl_exec($ch);
        //返回结果
        $errorNo = curl_errno($ch);
        curl_close($ch);
        if ($errorNo !== 0){
            throw new Exception("CURL出错，错误码" . $errorNo);
        }
        return $result;
    }


}