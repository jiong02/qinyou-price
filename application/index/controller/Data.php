<?php
namespace app\index\controller;

class Data
{
    public static function zeroFill($str, $length = 2, $pad = 0, $type = 'LEFT')
    {
        $type = strtoupper($type);
        switch ($type) {
            case 'LEFT':
                $type = STR_PAD_LEFT;
                break;
            case 'RIGHT':
                $type = STR_PAD_RIGHT;
                break;
            case 'BOTH':
                $type = STR_PAD_BOTH;
                break;
        }
        return str_pad($str,$length,$pad,$type);
    }

    //驼峰命名法转下划线风格
    public static function toUnderScore($str, $return = '')
    {

        for($i=0;$i<strlen($str);$i++){
            if($str[$i] == strtolower($str[$i])){
                $return .= $str[$i];
            }else{
                if($i>0){
                    $return .= '_';
                }
                $return .= strtolower($str[$i]);
            }
        }

        return $return;
    }

    public static function getNeedBehind($str, $need){
        $start = stripos($str, $need);
        if ($start === false) {

            return false;

        }
        return substr($str, $start + strlen($need));
    }



    /**
     * 为指定数字补零
     * @param int  $number 被填充的数字
     * @param int  $length 填充长度
     * @param mixd $padStr 填充字符,默认为0
     * @param const $direct 常量,默认为STR_PAD_LEFT
     * @return stirng 填充完成的数字
     */
    public static function padZero($number ,$length = 5 ,$padStr = 0 ,$direct = STR_PAD_LEFT)
    {
        return str_pad($number,$length,$padStr,$direct);
    }
    /**
     * 获取随机字符串
     * @param int 字符串长度
     */
   public function getNonceStr($length = 32)
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取随机数字
     * @param int 数字长度
     */
   public function getNonceNum($length = 4)
    {

        return rand(pow(10,($length-1)), pow(10,$length)-1);
    }

    /**
     * 将数组成XML数据
     * @param array $data
     */
   public function toXml($data)
    {
        if (!is_array($data) || count($data) <= 0) {
            return retErr('数组数据异常');
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
   public function fromXml($xml)
    {
        if (!$xml) {
            return retErr("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
}

