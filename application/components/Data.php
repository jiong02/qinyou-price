<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017-07-11
 * Time: 14:23
 */

namespace app\components;


use think\Exception;

class Data
{
    /**
     * @param int $length
     * @param string $string
     * @return string
     * 获取指定长度的随机字符串
     */
    static public function generateNonceString($length = 8, $string = "")
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=`~";
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $string;
    }

    /**
     * @param $data
     * @param string $buff
     * @return string
     * 格式化参数格式化成url参数
     */
    public static function toUrlParams($data, $buff = "")
    {
        foreach ($data as $k => $v)
        {
            if($v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * @param int $length
     * @return int
     * 获取指定长度的随机数字
     */
    public static function generateRandomNumber($length = 4)
    {
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return mt_rand($min, $max);
    }

    /**
     * 将数组成XML数据
     * @param array $data
     */
    public static function formatArraytoXml($data)
    {
        if (!is_array($data) || count($data) <= 0) {
            throw new Exception('数组数据异常');
        }

        $xml = "<xml>"."\n";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">"."\n";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">"."\n";
            }
        }
        $xml .= "</xml>"."\n";
        return $xml;
    }

    /**
     * 将xml转为array
     * @param string $xml
     */
    public static function formatXmlToArray($xml)
    {
        $json = self::formatXmlToJson($xml);
        return json_decode($json,true);
    }

    /**
     * @param $xml
     * @return mixed
     * @throws Exception
     * 将xml转成json
     */
    public static function formatXmlToJson($xml)
    {
        $obj = self::formatXmlToObj($xml);
        return json_encode($obj);
    }

    public static function formatXmlToObj($xml)
    {
        if (!$xml) {
            throw new Exception("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    }

    /**
     * 获取毫秒级别的时间戳
     */
    public static function getMillisecond()
    {
        //获取毫秒的时间戳
        $time = explode ( " ", microtime () );
        $time = $time[1] . ($time[0] * 1000);
        $time2 = explode( ".", $time );
        $time = $time2[0];
        return $time;
    }

    /**
     * 获取32位唯一字符串
     */
    public static function getUniqueString()
    {
        return md5(uniqid(mt_rand(), true));
    }
}