<?php
namespace app\ims\controller;


use app\components\ali\alipay\AliPayQrcodePay;
use app\ims\model\EmployeeModel;
use Endroid\QrCode\QrCode;

class IndexController extends PrivilegeController
{
    public function index()
    {
        return AliPayQrcodePay::pay(123123,'hehda',20000);
    }
}