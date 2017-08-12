<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
/**
 * 案例功能块路由组
 */
Route::domain('test', function(){
    Route::get('/',function(){
        return view('views/homepage');
    });
    Route::get('homepage',function(){
       return view('views/homepage');
    });
    Route::get('login',function(){
        return view('views/login');
    });
    Route::get('collect',function(){
        return view('views/collect');
    });
    Route::get('destinationIndex',function(){
        return view('views/destinationIndex');
    });
    Route::get('messageList',function(){
        return view('views/messageList');
    });
    Route::get('my',function(){
        return view('views/my');
    });
    Route::get('service',function(){
        return view('views/service');
    });
    Route::get('setting',function(){
        return view('views/setting');
    });
    Route::get('shoppingCart',function(){
        return view('views/shoppingCart');
    });
    Route::any('case/addCaseData','test/Case/addCaseData');
    Route::any('case/modifyCaseData','test/Case/modifyCaseData');
    Route::any('case/modifyCaseOrder','test/Case/modifyCaseOrder');
    Route::any('case/deleteCaseData','test/Case/deleteCaseData');
    Route::any('case/getCaseDataByCaseId','test/Case/getCaseDataByCaseId');
    Route::any('case/getPartOfCaseData','test/Case/getPartOfCaseData');
    Route::any('case/getAllCaseData','test/Case/getAllCaseData');
    /**
     * 定制信息功能路由组
     */
    Route::any('customer/addCustomerData','test/CustomTailor/addCustomerData');
    Route::any('customer/getCustomerData','test/CustomTailor/getCustomerData');
    Route::any('customer/getCustomerDataByCustomerId','test/CustomTailor/getCustomerDataByCustomerId');


   /**
    *  图片功能路由组合
    */
    Route::any('image/uploadImage','test/ImageSetting/uploadImage');
    Route::any('image/uploadImage2','test/ImageSetting/uploadImage2');
    Route::any('image/addCaseImageData','test/ImageSetting/addCaseImageData');
    Route::any('image/modifyCaseImageData','test/ImageSetting/modifyCaseImageData');
    Route::any('image/getCaseImageData','test/ImageSetting/getCaseImageData');


   /**
    * 微信功能组合
    */
    Route::any('wechat/WechatLoginEnterprise','WechatLoginEnterprise/testCurl');
    Route::any('wechat/WechatLoginEnterprise','SendMessageWechatEnterprise/testCurl');

   /**
    * 后台功能组
    */
    Route::any('Admin/login','test/WechatLoginEnterprise/login');
    Route::any('Admin/sendMessage','test/WechatLoginEnterprise/sendMessage');
});

















