<?php
/**
 * Created by PhpStorm.
 * User: ZYone
 * Date: 2017/8/14
 * Time: 17:21
 */

namespace app\components\wechat;


class QrcodePay
{
    const TRADE_TYPE = 'NATIVE';
    public static function init($outTradeNo, $body, $fee, $productId)
    {
        $UnifiedOrder = new UnifiedOrder();
        $UnifiedOrder->SetOut_trade_no($outTradeNo);
        $UnifiedOrder->SetProduct_id($productId);
        $UnifiedOrder->SetBody($body);
        $UnifiedOrder->SetTotal_fee($fee);
        $UnifiedOrder->SetTrade_type(self::TRADE_TYPE);
        $result = $UnifiedOrder->UniFiedOrder();
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
            return $result['code_url'];
        }else{
            return '二维码生成失败!';
        }
    }
}