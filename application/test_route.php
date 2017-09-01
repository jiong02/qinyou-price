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
    //添加PV数据（测试数据）
    Route::any('/Flux/addTestPvFlux','test/TemplateFlux/addTestPvFlux');
    //添加PV UV数据
    Route::any('/Flux/addPvUvFlux','test/TemplateFlux/addPvUvFlux');
    //获得板块流量数据
    Route::any('/Flux/getTempInfo','test/TemplateFlux/getTempInfo');
    //获取线路分析数据
    Route::any('/Flux/getRouteAnalyzeInfo','test/TemplateFlux/getRouteAnalyzeInfo');
    //获得海岛分页信息
    Route::any('/Search/getPlacePage','test/Search/getPlacePage');
    //前端页面搜索海岛线路信息
    Route::any('/Search/webSearchRoute','test/Search/webSearchRoute');
    //修改订单
    Route::any('/Order/updateOrder','test/Order/updateOrder');
    //获取订单（联系人）
    Route::any('/Order/getOrderLinkmanInfo','test/Order/getOrderLinkmanInfo');
    //修改订单支付状态
    Route::any('/Order/updatePayStatus','test/Order/updatePayStatus');
    //修改联系人信息
    Route::any('/Order/updateLinkmanInfo','test/Order/updateLinkmanInfo');
    //获取客户信息
    Route::any('/Order/getTripPersonList','test/Order/getTripPersonList');
    //修改客户信息
    Route::any('/Order/updateCustomerInfo','test/Order/updateCustomerInfo');
    //修改订单状态
    Route::any('/Order/updateOrderStatus','test/Order/updateOrderStatus');
    //后台获取订单列表
    Route::any('/Order/getBackOrderList','test/Order/getBackOrderList');
    //后台获取我的订单列表
    Route::any('/Order/getMyBackOrderList','test/Order/getMyBackOrderList');
    //后台获取房间数量
    Route::any('/Order/getOrderRoomInt','test/order/getOrderRoomNumber');
    //后台获取订单信息
    Route::any('/Order/getBackOrderInfo','test/Order/getBackOrderInfo');
    //订单信息Excel文件导出
    Route::any('/Order/outputOrderInfo','test/Order/outputOrderInfo');
    //修改订单价格
    Route::any('/Order/updateOrderPrice','test/Order/updateOrderPrice');
    //导出订单word文档
    Route::any('/OrderWord/outputOrderWord','test/OrderWord/outputOrderWord');
    //修改订单支付ID
    Route::any('/Order/updateOrderPay','test/Order/updateOrderPay');




    //HTML 访问模块
    Route::get('product',function(){
        return view('views/product');
    });

    Route::get('group',function(){
        return view('views/group');
    });

    Route::get('personalaccount',function(){
        return view('views/personalaccount');
    });


    Route::get('casedetail',function(){
        return view('views/casedetail');
    });

    Route::get('collect',function(){
        return view('views/collect');
    });

    Route::get('commoninfo',function(){
        return view('views/commoninfo');
    });
    Route::get('destinationIndex',function(){
        return view('views/destinationIndex');
    });

    Route::get('homepage',function(){
        return view('views/homepage');
    });

    Route::get('index',function(){
        return view('views/index');
    });
    Route::get('messageList',function(){
        return view('views/messageList');
    });

    Route::get('my',function(){
        return view('views/my');
    });

    Route::get('orderinfo',function(){
        return view('views/orderinfo');
    });
    Route::get('paystep',function(){
        return view('views/paystep');
    });

    Route::get('personalaccount',function(){
        return view('views/personalaccount');
    });

    Route::get('personalaccount',function(){
        return view('views/personalaccount');
    });
    Route::get('preview',function(){
        return view('views/preview');
    });

    Route::get('product',function(){
        return view('views/product');
    });

    Route::get('personalaccount',function(){
        return view('views/personalaccount');
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
    Route::get('yudao',function(){
        return view('views/yudao');
    });
});