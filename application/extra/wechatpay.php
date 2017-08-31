<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/23
 * Time: 9:13
 */
return [
    //应用id
    'app_id' => 'wxce3fcd5f6c6c2a41',
    //应用密钥
    'app_secret' => '145670ad17a7d7747b1d147e222853e6',
    //商户id
    'merchant_id' => '1350957101',
    //商户密钥
    'key' => '7oXyBHsx1Kk2obXeapbJosHWQFZs93pv',
    //ssl证书路径
    'ssl_cert_path' => '/data/wwwroot/price.cheeruislands.com/application/components/cert/apiclient_cert.pem',
    //ssl证书密钥路径
    'ssl_key_path' => '/data/wwwroot/price.cheeruislands.com/application/components/cert/apiclient_key.pem',
    //回调通知接口
    'notify_url' => 'www.baidu.com',
    //订单失效时长(单位:秒)
    'timeout_express' => 7200,
    //最大查询重试次数
    'max_query_retry' => "10",
    //查询间隔
    'query_duration' => "2",
];