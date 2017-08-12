<?php

namespace app\components;


class Response
{
    static public function Success($message = '请求成功', $data = [], $code = 200)
    {
        $return = self::generateData($data, $message, $code);
        return $return;
    }

    static public function Error($message = '请求失败', $data = [], $code = 400)
    {
        $return = self::generateData($data, $message, $code);
        return $return;
    }

    static public function encryptSuccess($message = '请求成功', $data = [], $code = 200)
    {
        $return = self::generateEncryptData($data, $message, $code);
        return $return;
    }

    static public function encryptError($message = '请求失败', $data = [], $code = 400)
    {
        $return = self::generateEncryptData($data, $message, $code);
        return $return;
    }

    static public function generateData($data, $message, $code)
    {
        $return = [
            'data'=> $data,
            'code'=> $code,
            'message'=> $message,
            'timestamp'=> time(),
        ];
        return $return;
    }

    static public function generateEncryptData($data, $message, $code)
    {
        $return = [
            'data'=> $data,
            'code'=> $code,
            'message'=> $message,
            'timestamp'=> time(),
        ];
        $encrypt = new Encrypt();
        $return['sign'] = $encrypt->makeSign($return);
        return $return;
    }
}