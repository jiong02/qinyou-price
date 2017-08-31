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
    Route::any('customer/modifyFollowUpRecordByCustomerId','test/CustomTailor/modifyFollowUpRecordByCustomerId');
    Route::any('customer/modifyFollowUpEmployeeIdByCustomerId','test/CustomTailor/modifyFollowUpEmployeeIdByCustomerId');

   /**
    *  图片功能路由组合
    */
    Route::any('image/uploadImage','test/ImageSetting/uploadImage');
    Route::any('image/uploadImage2','test/ImageSetting/uploadImage2');
    Route::any('image/addCaseImageData','test/ImageSetting/addCaseImageData');
    Route::any('image/modifyCaseImageData','test/ImageSetting/modifyCaseImageData');
    Route::any('image/getCaseImageData','test/ImageSetting/getCaseImageData');

   /**
    * 后台功能组
    */
    Route::any('department/getAllDepartmentNameByDepartmentId','ims/Department/getAllDepartmentNameByDepartmentId');
    Route::any('employee/getAllEmployeeDataByDepartmentId','ims/Employee/getAllEmployeeDataByDepartmentId');

    /**
     * 微信支付功能组
     */
    Route::any('wechatPay/qrcodePay','test/AliPay/qrcodePay');

    /**
     * 企业微信登录功能
     */
    Route::any('wechatEnterpriseLogin','ims/EmployeeAccount/wechatEnterpriseLogin');
    Route::any('sendVerifyCode','ims/EmployeeAccount/sendVerifyCode');

    /**
     * 支付功能
     */
    Route::any('pay/qrcodePay','test/Pay/qrcodePay');
    Route::any('pay/query','test/Pay/query');
    Route::any('pay/updateOrderStatus','test/Pay/updateOrderStatus');
    /*
     * 测试
     */
     Route::any('alipayQrcodePay','test/Pay/alipayQrcodePay');
     Route::any('alipayQuery','test/Pay/alipayQuery');
     Route::any('alipayRefund','test/Pay/alipayRefund');
     Route::any('wechatpayQrcodePay','test/Pay/wechatpayQrcodePay');
     Route::any('wechatpayQuery','test/Pay/wechatpayQuery');
     Route::any('wechatpayRefund','test/Pay/wechatpayRefund');
});

















