<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/7/14
 * Time: 11:05
 */

namespace app\test\controller;

use app\components\wechat\QrcodePay;
use Endroid\QrCode\QrCode;

class WechatPayController extends BaseController
{
    public function qrpay()
    {
        $url = QrcodePay::init('10247681','验孕棒',99.00,'000011');
        $qrCode = new QrCode($url);
        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
        exit;
    }
}