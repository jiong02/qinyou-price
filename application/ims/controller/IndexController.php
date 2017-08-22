<?php
namespace app\ims\controller;


use app\components\ali\alipay\AliPayQrcodePay;
use app\ims\model\EmployeeModel;
use Endroid\QrCode\QrCode;

class IndexController extends PrivilegeController
{
    public function index()
    {
        $qrcodePay = new AliPayQrcodePay();
        $qrcodePay->testQrcodePay();
    }
}