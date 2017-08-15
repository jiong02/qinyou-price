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

Route::domain('test', function(){
    //检测验证码是否正确
    Route::any('/TestAcc/checkVerifyCode','test/TestAccount/checkVerifyCode');
    //登录接口
    Route::any('/TestAcc/accountLogin','test/TestAccount/accountLogin');
    //发送验证码接口
    Route::any('/TestAcc/accountSendCode','test/TestAccount/accountSendCode');
    //修改密码
    Route::any('/TestAcc/updateAccountPassport','test/TestAccount/updateAccountPassport');
    //修改呢称
    Route::any('/TestAcc/updateAccountUserName','test/TestAccount/updateAccountUserName');
    //修改手机号码
    Route::any('/TestAcc/updateAccountPhone','test/TestAccount/updateAccountPhone');
    //个人中心发送验证码
    Route::any('/TestAcc/phoneSendCode','test/TestAccount/phoneSendCode');
    //后台发送验证码
    Route::any('/TestAcc/backSendCode','test/TestAccount/backSendCode');
    //后台检测验证码是否成功
    Route::any('/TestAcc/backCheckCode','test/TestAccount/backCheckCode');
    //后台修改密码
    Route::any('/TestAcc/backUpdatePassword','test/TestAccount/backUpdatePassword');
    //修改手机号码
    Route::any('/TestAcc/backUpdatePhone','test/TestAccount/backUpdatePhone');
    //获得常用人信息
    Route::any('/TestAcc/getPersonList','test/TestAccount/getPersonList');
    //修改常用人信息
    Route::any('/TestAcc/updatePersonInfo','test/TestAccount/updatePersonInfo');
    //获得常用人信息
    Route::any('/TestAcc/getPersonInfo','test/TestAccount/getPersonInfo');
    //删除常用人信息
    Route::any('/TestAcc/deletePersonInfo','test/TestAccount/deletePersonInfo');
    //获得用户名称
    Route::any('/TestAcc/getUserName','test/TestAccount/getUserName');
    //微信登录逻辑
    Route::any('/TestAcc/wechatLogin','test/TestAccount/wechatLogin');
    //微信登录页面
    Route::any('/TestAcc/wechatLoginView','test/TestAccount/wechatLoginView');
    //QQ登录页面
    Route::any('/TestAcc/qqLoginView','test/TestAccount/qqLoginView');
    //QQ登录逻辑
    Route::any('/TestAcc/qqLogin','test/TestAccount/qqLogin');
    //获得用户列表分页
    Route::any('/TestAcc/getAccountList','test/TestAccount/getAccountList');
    //获得用户列表总分页
    Route::any('/TestAcc/getAccountListTotalPage','test/TestAccount/getAccountListTotalPage');
    //index
    Route::any('/Index/index','test/Index/index');
    //login
    Route::any('/Index/login','test/Index/Login');
    //填充回访量数据
    Route::any('/TestAcc/addVisitTable','test/TestAccount/addVisitTable');
    //获得新增统计数据
    Route::any('/DataTotal/getAddAccountDataTotal','test/DataStatistics/getAddAccountDataTotal');
    //获得回访量统计数据
    Route::any('/DataTotal/getBackVisitDataTotal','test/DataStatistics/getBackVisitDataTotal');
    //获得总用户统计数据
    Route::any('/DataTotal/getAllAccountDataTotal','test/DataStatistics/getAllAccountDataTotal');

    // +--------------------------------+
    // |   二期                         |
    // +--------------------------------+

    //获取banner列表
    Route::any('/Banner/getBannerList','test/Banner/getBannerList');
    //获取所有Banner信息
    Route::any('/Banner/getAllBannerInfo','test/Banner/getAllBannerInfo');
    //获得banner信息
    Route::any('/Banner/getBannerInfo','test/Banner/getBannerInfo');
    //修改Banner信息
    Route::any('/Banner/updateBannerInfo','test/Banner/updateBannerInfo');
    //删除banner信息
    Route::any('/Banner/deleteBannerInfo','test/Banner/deleteBannerInfo');
    //获得模板列表
    Route::any('/Temp/getTemplateList','test/Template/getTemplateList');
    //删除模板
    Route::any('/Temp/deleteTempInfo','test/Template/deleteTempInfo');
    //新建模板
    Route::any('/Temp/createTemplate','test/Template/createTemplate');
    //修改模板线路
    Route::any('/Temp/updateTempRoute','test/Template/updateTempRoute');
    //删除模板线路
    Route::any('/Temp/deleteTempRoute','test/Template/deleteTempRoute');
    //修改模板
    Route::any('/Temp/updateTempInfo','test/Template/updateTempInfo');
    //获取选中的模板线路列表
    Route::any('/Temp/getChooseTempRouteInfo','test/Template/getChooseTempRouteInfo');
    //前端页面获取板块与线路
    Route::any('/Temp/webTempRoute','test/Template/webTempRoute');
    //获得国家列表（含排序）
    Route::any('/Sort/getSortCountryList','test/Sort/getSortCountryList');
    //修改国家排序
    Route::any('/Sort/updateCountrySort','test/Sort/updateCountrySort');
    //获得海岛列表（含排序）
    Route::any('/Sort/getSortPlaceList','test/Sort/getSortPlaceList');
    //修改单一海岛排序
    Route::any('/Sort/updateSinglePlaceSort','test/Sort/updateSinglePlaceSort');
    //搜索国家排序信息
    Route::any('/Sort/searchCountrySortInfo','test/Sort/searchCountrySortInfo');
    //修改海岛排序
    Route::any('/Sort/updatePlaceSort','test/Sort/updatePlaceSort');
    //添加PV数据
    Route::any('/Flux/addPvFlux','test/TemplateFlux/addPvFlux');
    //获得板块流量数据
    Route::any('/Flux/getTempInfo','test/TemplateFlux/getTempInfo');
    //获得海岛分页信息
    Route::any('/Search/getPlacePage','test/Search/getPlacePage');
    //前端页面搜索海岛线路信息
    Route::any('/Search/webSearchRoute','test/Search/webSearchRoute');
    //修改订单（联系人）
    Route::any('/Order/updateOrder','test/Order/updateOrder');
    //获取订单（联系人）
    Route::any('/Order/getOrderLinkmanInfo','test/Order/getOrderLinkmanInfo');




});