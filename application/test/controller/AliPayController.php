<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/15
 * Time: 17:21
 */

namespace app\test\controller;


use app\components\ali\QrcodePay;

class AliPayController extends BaseController
{
    public static function qrcodePay()
    {
        $aliQrcodePay = new QrcodePay();
        $aliQrcodePay->qrcodePay('1024789','验孕棒','200.00');
    }
}