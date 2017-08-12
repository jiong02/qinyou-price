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
Route::domain('price',function(){
    //总登录
    Route::any('/empAccount/login','ims/EmployeeAccount/login');
    //获取国家列表
    Route::any('/country/getCountryList','ims/Country/getCountryList');
    //新增国家信息
    Route::any('/country/addCountry','ims/Country/addCountry');
    //修改国家信息
    Route::any('/country/updateCountry','ims/Country/updateCountry');
    //获取国家信息
    Route::any('/country/getCountryData','ims/Country/getCountryData');
    //单文件上传
    Route::any('/File/fileUpload','ims/Image/fileUpload');
    //图片上传
    Route::any('/File/fileUpload2','ims/Image/fileUpload2');
    //图片上传 有uniqid则上传 没有则不上传
    Route::any('/File/fileUpload3','ims/Image/fileUpload3');
    //获得海岛信息
    Route::any('/place/getPlaceData','ims/Place/getPlaceData');
    //获得海岛列表
    Route::any('/place/getPlaceList','ims/Place/getPlaceList');
    //修改海岛上下架信息
    Route::any('/place/updatePlaceStatus','ims/Place/updatePlaceStatus');
    //获得指定国家与国家下的海岛信息
    Route::any('/country/getCountryPlaceData','ims/Country/getCountryPlaceData');
    //获得指定海岛信息
    Route::any('/place/getPlaceInfo','ims/Place/getPlaceInfo');
    //添加海岛
    Route::any('/place/addPlaceInfo','ims/Place/addPlaceInfo');
    //修改海岛
    Route::any('/place/updatePlaceInfo','ims/Place/updatePlaceInfo');
    //通过获得酒店列表
    Route::any('/hotel/getHotelList','ims/Hotel/getHotelList');
    //添加酒店
    Route::any('/hotel/addHotel','ims/Hotel/addHotel');
    //修改酒店
    Route::any('/hotel/updateHotel','ims/Hotel/updateHotel');
    //获得海岛下酒店列表
    Route::any('/hotel/getPlaceHotelList','ims/Hotel/getPlaceHotelList');
    //获得酒店基本信息
    Route::any('/hotel/getHotelInfo','ims/Hotel/getHotelInfo');
    //获得酒店联系方式
    Route::any('/hotel/getHotelContactWay','ims/Hotel/getHotelContactWay');
    //修改酒店联系方式
    Route::any('/hotel/updateHotelContactWay','ims/Hotel/updateHotelContactWay');
    //获得酒店房型列表
    Route::any('/hotel/getHotelRoomList','ims/Hotel/getHotelRoomList');
    //获得酒店房间类型
    Route::any('/hotel/getHotelRoomInfo','ims/Hotel/getHotelRoomInfo');
    //修改酒店房型
    Route::any('/hotel/updateHotelRoom','ims/Hotel/updateHotelRoom');
    //删除酒店房型
    Route::any('/hotel/deleteHotelRoom','ims/Hotel/deleteHotelRoom');
    //添加酒店房型
    Route::any('/hotel/addHotelRoom','ims/Hotel/addHotelRoom');
    //查询酒店设施列表
    Route::any('/hotel/getFacilityList','ims/Hotel/getFacilityList');
    //添加酒店设施
    Route::any('/hotel/addFacility','ims/Hotel/addFacility');
    //修改酒店设施
    Route::any('/hotel/updateFacility','ims/Hotel/updateFacility');
    //获得酒店设施
    Route::any('/hotel/getFacilityInfo','ims/Hotel/getFacilityInfo');
    //删除酒店设施
    Route::any('/hotel/deleteFacility','ims/Hotel/deleteFacility');
    //获得酒店默认货币
    Route::any('/hotel/getHotelExchange','ims/Hotel/getHotelExchange');
    //获得酒店货币列表(带有酒店默认货币)
    Route::any('/hotel/getHotelExchangeList','ims/Hotel/getHotelExchangeList');
    //获得酒店年龄限制
    Route::any('/hotel/getHotelAgeLimit','ims/Hotel/getHotelAgeLimit');
    //修改酒店年龄限制
    Route::any('/hotel/updateHotelAgeLimit','ims/Hotel/updateHotelAgeLimit');
    //获得总汇率列表
    Route::any('/hotel/getExchangeList','ims/Hotel/getExchangeList');
    //添加总汇率列表
    Route::any('/hotel/addExchange','ims/Hotel/addExchange');
    //删除汇率
    Route::any('/hotel/deleteExchange','ims/Hotel/deleteExchange');
    //修改汇率列表
    Route::any('/hotel/updateExchange','ims/Hotel/updateExchange');
    //修改酒店货币
    Route::any('/hotel/updateHotelExc','ims/Hotel/updateHotelExc');
    //获得用户信息列表
    Route::any('/hotel/getUserList','ims/Hotel/getUserList');
    //添加酒店房型图片
    Route::any('/hotel/addHotelRoomImage','ims/Hotel/addHotelRoomImage');


    //【合同】
    //添加多个合同
    Route::any('/contract/addContractList','ims/Contract/addContractList');
    //修改合同信息
    Route::any('/contract/updateContractInfo','ims/Contract/updateContractInfo');
    //获得合同信息
    Route::any('/contract/getContractInfo','ims/Contract/getContractInfo');
    //获得该酒店合同列表
    Route::any('/contract/getContractList','ims/Contract/getContractList');
    //添加/修改合同季节
    Route::any('/contract/contractSeason','ims/Contract/contractSeason');
    //删除不可用日期
    Route::any('/contract/deleteNotWork','ims/Contract/deleteNotWork');
    //删除强制收费信息
    Route::any('/contract/deleteItem','ims/Contract/deleteItem');
    //删除某几天日期
    Route::any('/contract/deleteSomeDay','ims/Contract/deleteSomeDay');
    //获得价格季信息
    Route::any('/contract/getSeasonInfo','ims/Contract/getSeasonInfo');
    //修改价格季信息
    Route::any('/contract/updateSeasonInfo','ims/Contract/updateSeasonInfo');
    //删除价格季
    Route::any('/contract/deleteSeason','ims/Contract/deleteSeason');
    //获得套餐列表
    Route::any('/contractPackage/getPackageList','ims/ContractPackage/getPackageList');
    //获得套餐信息
    Route::any('/contractPackage/getPackageInfo','ims/ContractPackage/getPackageInfo');
    //添加多个套餐
    Route::any('/contractPackage/addPackageList','ims/ContractPackage/addPackageList');
    //删除套餐信息
    Route::any('/contract/deletePackage','ims/ContractPackage/deletePackage');
    //修改套餐信息
    Route::any('/contractPackage/updatePackageInfo','ims/ContractPackage/updatePackageInfo');
    //获得房型费用信息
    Route::any('/contractRoom/getRoomInfo','ims/ContractRoom/getRoomInfo');
    //获得费用信息列表
    Route::any('/contractRoom/getRoomList','ims/ContractRoom/getRoomList');
    //添加/修改房型费用信息
    Route::any('/contractRoom/updateRoomInfo','ims/ContractRoom/updateRoomInfo');
    //删除酒店房型
    Route::any('/contractRoom/deleteRoom','ims/ContractRoom/deleteRoom');
    //获得优惠信息列表
    Route::any('/discount/getDisList','ims/ContractDiscount/getDisList');
    //添加/修改优惠信息列表
    Route::any('/discount/updateDiscount','ims/ContractDiscount/updateDiscount');
    //获得所有酒店房型列表
    Route::any('/discount/getAllHotelRoom','ims/ContractDiscount/getAllHotelRoom');
    //获得指定酒店房型列表
    Route::any('/discount/getHotelRoomList','ims/ContractDiscount/getHotelRoomList');
    //通过优惠名称获得优惠信息列表
    Route::any('/discount/getDisInfo','ims/ContractDiscount/getDisInfo');
    //批量添加优惠列表
    Route::any('/discount/addDisList','ims/ContractDiscount/addDisList');
    //删除优惠
    Route::any('/discount/deleteDis','ims/ContractDiscount/deleteDis');
    //计算价格季内的日期
    Route::any('/contractRoom/countSeasonDate','ims/ContractRoom/countSeasonDate');
    //添加房型信息
    Route::any('/contractRoom/addRoomList','ims/ContractRoom/addRoomList');


    //【供应商】
    //新增/修改账号
    Route::any('/suppAccount/updateAccount','ims/SupplierAccount/updateAccount');
    //获得员工列表
    Route::any('/suppAccount/getEmpList','ims/SupplierAccount/getEmpList');
    //测试获得二进制图片信息
    Route::any('/suppAccount/getFileImage','ims/SupplierAccount/getFileImage');
    //搜索功能（查找目的地）
    Route::any('/suppAccount/searchBourn','ims/SupplierAccount/searchBourn');
    //获得渠道商列表
    Route::any('/suppAccount/getAccountList','ims/SupplierAccount/getAccountList');
    //获得渠道商账号信息
    Route::any('/suppAccount/getAccountInfo','ims/SupplierAccount/getAccountInfo');
    //获得渠道商等级
    Route::any('/suppAccount/getGradeList','ims/SupplierAccount/getGradeList');
    //账号登录
    Route::any('/suppAccount/accountLogin','ims/SupplierAccount/accountLogin');
    //获得订单支付页面信息
    Route::any('/suppOrderTrip/getPayInfo','ims/SupplierOrderTrip/getPayInfo');
    //提交订单支付方式
    Route::any('/suppOrderTrip/updateTripPayInfo','ims/SupplierOrderTrip/updateTripPayInfo');
    //删除订单资料图片
    Route::any('/suppOrderTrip/deleteImage','ims/SupplierOrderTrip/deleteImage');
    //上传图片接口
    Route::any('/suppOrderTrip/addRecordInfo','ims/SupplierOrderTrip/addRecordInfo');
    //获得护照信息
    Route::any('/suppOrderTrip/getPassportInfo','ims/SupplierOrderTrip/getPassportInfo');
    //修改护照信息
    Route::any('/suppOrderTrip/updatePassportInfo','ims/SupplierOrderTrip/updatePassportInfo');
    //获得航班信息
    Route::any('/SuppOrderTrip/getFlightInfo','ims/SupplierOrderTrip/getFlightInfo');
    //修改航班信息
    Route::any('/SuppOrderTrip/updateFlightInfo','ims/SupplierOrderTrip/updateFlightInfo');
    //获得指定用户所有订单
    Route::any('/SuppOrderTrip/getAllOrder','ims/SupplierOrderTrip/getAllOrder');
    //搜索订单
    Route::any('/SuppOrderTrip/searchOrder','ims/SupplierOrderTrip/searchOrder');
    //获得套餐列表
    Route::any('/SupplierOrder/getPackList','ims/SupplierOrder/getPackList');
    //获得指定套餐信息
    Route::any('/SuppOrder/getPackInfo','ims/SupplierOrder/getPackInfo');
    //获得套餐房型列表
    Route::any('/SuppOrder/getRoomList','ims/SupplierOrder/getRoomList');
    //确认订单资料
    Route::any('/SuppOrder/orderInfo','ims/SupplierOrder/orderInfo');
    //计算套餐成人与儿童费用
    Route::any('/SuppOrder/countPackCost','ims/SupplierOrder/countPackCost');
    Route::any('/SuppOrder/countPackCost2','ims/SupplierOrder/countPackCost2');
    //添加订单
    Route::any('/SuppOrder/addOrder','ims/SupplierOrder/addOrder');
    //查询目的地
    Route::any('/SuppOrderTrip/getBourn','ims/SupplierOrderTrip/getBourn');
    //后台检测支付信息页面
    Route::any('/SuppOrderTrip/checkPayView','ims/SupplierOrderTrip/checkPayView');
    //后台检测支付
    Route::any('/SuppOrderTrip/checkPay','ims/SupplierOrderTrip/checkPay');
    //后台检测护照信息页面
    Route::any('/SuppOrderTrip/checkPassportView','ims/SupplierOrderTrip/checkPassportView');
    //后台检测护照
    Route::any('/SuppOrderTrip/checkPassport','ims/SupplierOrderTrip/checkPassport');
    //后台获得订单列表
    Route::any('/SuppOrderTrip/getBgOrderList','ims/SupplierOrderTrip/getBgOrderList');
    //后台检测航班信息
    Route::any('/SuppOrderTrip/checkFlight','ims/SupplierOrderTrip/checkFlight');
    //删除订单
    Route::any('/SuppOrderTrip/cancelOrder','ims/SupplierOrderTrip/cancelOrder');

    // +----------------------------------------------------------------------
    // | 线路路由
    // +----------------------------------------------------------------------
    //获得所有线路
    Route::any('/route/getAllRouteList','route/Route/getAllRouteList');
    //获取选中的线路数据
    Route::any('/route/getChooseRouteList','route/Route/getChooseRouteList');
    //我的线路
    Route::any('/route/myRouteList','route/Route/myRouteList');
    //管理路线
    Route::any('/route/checkRouteList','route/Route/checkRouteList');
    //搜索路线
    Route::any('/route/searchRoute','route/Route/searchRoute');
    //搜索我的路线
    Route::any('/route/searchMyRoute','route/Route/searchMyRoute');
    //审核线路
    Route::any('/route/examineRoute','route/Route/examineRoute');
    //删除线路
    Route::any('/route/deleteRoute','route/Route/deleteRoute');
    //路线的上线与下线的操作
    Route::any('/route/updateRouteStatus','route/Route/updateRouteStatus');
    //审核线路
    Route::any('/route/checkRouteStatus','route/Route/checkRouteStatus');
    //通过线路ID获取图片信息
    Route::any('/route/getRouteImage','route/Route/getRouteImage');
    //创建线路信息
    Route::any('/route/createRouteInfo','route/Route/createRouteInfo');
    //修改线路信息
    Route::any('/route/updateRouteInfo','route/Route/updateRouteInfo');
    //获得海岛列表与国家信息
    Route::any('/route/getCountryPlaceInfo','route/Route/getCountryPlaceInfo');
    //获得线路酒店列表
    Route::any('/routeDetail/getHotelList','route/RouteDetail/getHotelList');
    //修改线路酒店信息
    Route::any('/routeDetail/routeAddHotelRoom','route/RouteDetail/routeAddHotelRoom');
    //获得线路酒店设施列表
    Route::any('/routeDetail/getRoomActivityList','route/RouteDetail/getRoomActivityList');
    //修改房型信息
    Route::any('/routeDetail/updateRouteRoomInfo','route/RouteDetail/updateRouteRoomInfo');
    //修改活动信息
    Route::any('/routeDetail/updateRouteActivityInfo','route/RouteDetail/updateRouteActivityInfo');
    //获得线路所有信息
    Route::any('/routeDetail/getRouteList','route/RouteDetail/getRouteList');
    //获得线路基本信息
    Route::any('/route/getRouteInfo','route/Route/getRouteInfo');
    //获得用户等级信息
    Route::any('/route/publicSelectAccountInfo','route/Route/publicSelectAccountInfo');
    //通过酒店ID获得交通列表
    Route::any('/routeDetail/getVehicleList','route/RouteDetail/getVehicleList');
    //修改线路交通信息
    Route::any('/routeDetail/updateVehicleInfo','route/RouteDetail/updateVehicleInfo');
    //购买须知
    Route::any('/route/updateBuyKnow','route/Route/updateBuyKnow');
    //查看购买须知
    Route::any('/route/selectBuyKnow','route/Route/SelectBuyKnow');
    //通过ID集合删除图片
    Route::any('/route/deleteImageList','route/Route/deleteImageList');
    //描述页面获得线路酒店房型信息
    Route::any('/routeDesc/getRouteHotelList','route/RouteDescription/getRouteHotelList');
    //描述页面获得线路交通信息
    Route::any('/routeDesc/getRouteVehicleList','route/RouteDescription/getRouteVehicleList');
    //通过酒店ID获得图片列表
    Route::any('/routeDesc/getHotelImage','route/RouteDescription/getHotelImage');
    //通过活动ID获得图片列表
    Route::any('/routeDesc/getActivityImage','route/RouteDescription/getActivityImage');
    //获得线路活动列表
    Route::any('/routeDesc/getActivityList','route/RouteDescription/getActivityList');
    //修改描述页面信息
    Route::any('/routeDesc/updateDescription','route/RouteDescription/updateDescription');
    //删除线路交通
    Route::any('/routeDesc/deleteDescVehicle','route/RouteDescription/deleteDescVehicle');
    //删除线路酒店房型
    Route::any('/routeDetail/deleteRouteHotelRoom','route/RouteDetail/deleteRouteHotelRoom');
    //删除线路活动失败
    Route::any('/routeDetail/deleteRouteActivity','route/RouteDetail/deleteRouteActivity');
    //获得线路所有描述信息
    Route::any('/routeDesc/getDescAllInfo','route/RouteDescription/getDescAllInfo');
    //获得线路所有描述信息（去除描述基本信息与线路信息）
    Route::any('/routeDesc/getDescAllInfo2','route/RouteDescription/getDescAllInfo2');
    //删除描述活动
    Route::any('/routeDesc/deleteDescActivity','route/RouteDescription/deleteDescActivity');
    //删除描述交通
    Route::any('/routeDesc/deleteDescriptionVehicle','route/RouteDescription/deleteDescriptionVehicle');
    //删除描述其他信息
    Route::any('/routeDesc/deleteDescOtherInfo','route/RouteDescription/deleteDescOtherInfo');

    //【活动】
    //批量添加活动
    Route::any('/hotel/addActivityList','ims/Hotel/addActivityList');
    //获得酒店活动列表
    Route::any('/hotel/getHotelActivity','ims/Hotel/getHotelActivity');
    //删除酒店活动
    Route::any('/hotel/deleteHotelActivity','ims/Hotel/deleteHotelActivity');
    //修改活动信息
    Route::any('/hotel/updateActivityInfo','ims/Hotel/updateActivityInfo');
    //获得活动信息
    Route::any('/hotel/getActivityInfo','ims/Hotel/getActivityInfo');
    //添加交通基本信息接口
    Route::any('/vehicle/addBaseData','ims/Vehicle/addBaseData');
    //获取酒店年龄定义接口
    Route::any('/Hotel/getAgeRangeByHotelId','ims/Hotel/getAgeRangeByHotelId');
    //添加接驳交通信息接口
    Route::any('/vehicle/addTransferData','ims/Vehicle/addTransferData');
    //添加联程接驳交通信息接口
    Route::any('/vehicle/addConnectTransferData','ims/VehicleConnect/addConnectTransferData');
    //添加定期交通接口
    Route::any('/vehicle/addFixedData','ims/Vehicle/addFixedData');
    //添加定期交通接口
    Route::any('/vehicle/deleteFixedData','ims/Vehicle/deleteFixedData');
    //更新交通信息
    Route::any('/vehicle/modifyBaseData','ims/Vehicle/modifyBaseData');
    //获取所有交通节点信息
    Route::any('/vehicle/getAllNodeInfo','ims/VehicleBasic/getAllNodeInfo');
    //获取单程交通接驳信息
    Route::any('/vehicle/getTransferData','ims/Vehicle/getTransferData');
    //修改单程交通接驳信息
    Route::any('/vehicle/modifyTransferData','ims/Vehicle/modifyTransferData');
    //删除单程交通接驳信息
    Route::any('/vehicle/deleteTransferData','ims/Vehicle/deleteTransferData');
    //获取联程信息接口
    Route::any('/vehicle/getAllConnectNodeInfo','ims/VehicleConnect/getConnectNodeInfo');
    //添加联程线路信息接口
    Route::any('/vehicle/addConnectData','ims/VehicleConnect/addConnectRouteData');
    //查询联程线路列表
    Route::any('/vehicle/queryConnectRoutList','ims/VehicleConnect/queryConnectRoutList');
    //查看联程线路信息
    Route::any('/vehicle/getConnectRouteInfo','ims/VehicleConnect/getConnectRouteInfo');
    //删除联程线路
    Route::any('/vehicle/deleteConnectRouteInfo','ims/VehicleConnect/deleteConnectRouteInfo');
    //获取联程接驳信息接口
    Route::any('/vehicle/getConnectTransferData','ims/VehicleConnect/getConnectTransferData');
    //修改联程接驳信息
    Route::any('/vehicle/modifyConnectTransferData','ims/VehicleConnect/modifyConnectTransferData');
    //修改联程接驳信息
    Route::any('/vehicle/deleteConnectTransferData','ims/VehicleConnect/deleteConnectTransferData');
    //修改单程定期信息
    Route::any('/vehicle/modifyFixedData','ims/Vehicle/modifyFixedData');
    //获取单程定期信息
    Route::any('/vehicle/getFixedData','ims/Vehicle/getFixedData');
    //获取单程交通信息
    Route::any('/vehicle/getSingleData','ims/Vehicle/getSingleData');
    //查看联程定期节点信息
    Route::any('/vehicle/getConnectFixedData','ims/VehicleConnect/getConnectFixedData');
    //修改联程定期信息
    Route::any('/vehicle/modifyConnectFixedData','ims/VehicleConnect/modifyConnectFixedData');
    //删除单程定期信息
    Route::any('/vehicle/deleteConnectFixedData','ims/VehicleConnect/deleteConnectFixedData');
    //添加联程定期交通信息接口
    Route::any('/vehicle/addConnectFixedData','ims/VehicleConnect/addConnectFixedData');
    //删除排期信息
    Route::any('/vehicle/deleteScheduleData','ims/VehicleBasic/deleteScheduleData');
    //删除班次信息
    Route::any('/vehicle/deleteShiftData','ims/VehicleBasic/deleteShiftData');
    //添加城市信息接口
    Route::any('/vehicle/addCityData','ims/VehicleCity/addCityData');
    //获取城市信息接口
    Route::any('/vehicle/getAllCityData','ims/VehicleCity/getAllCityData');
    //查询交通信息
    Route::any('/vehicle/queryInfo','ims/VehicleBasic/queryInfo');
    //查询往返交通信息
    Route::any('/vehicle/queryCityList','ims/VehicleCity/queryCityList');
    //新建往返城市交通集
    Route::any('/vehicle/addCityRouteData','ims/VehicleCity/addCityRouteData');
    //获取往返交通节点信息
    Route::any('/vehicle/getCityRouteInfo','ims/VehicleCity/getCityRouteInfo');
    //删除城市交通线路信息
    Route::any('/vehicle/deleteCityRouteInfo','ims/VehicleCity/deleteCityRouteInfo');



    // +----------------------------------------------------------------------
    // | 计价系统路由
    // +----------------------------------------------------------------------
    //获取当前系统所有汇率信息
    Route::any('/exchange/getAllExchangeData','ims/Hotel/getAllExchangeData');
    //获取当前系统所有国家id和名字
    Route::any('/country/getAllName','ims/Country/getAllName');
    //获取指定国家id下的所有目的地的id和名字
    Route::any('/place/getAllName','ims/Place/getAllName');
    //获取指定目的地id下的所有酒店的id和名字
    Route::any('/hotel/getAllName','ims/Hotel/getAllName');
    //获取指定酒店id下的酒店id和名字
    Route::any('/hotel/getName','ims/Hotel/getName');
    //新建订单
    Route::any('/order/createOrder','ims/Order/addOrderData');
    //获取订单信息
    Route::any('/order/getOrderInfo','ims/Order/getOrderData');
    //修改联系人信息
    Route::any('/order/modifyRepData','ims/Order/modifyRepData');
    //修改订单日期
    Route::any('/order/modifyOrderDate','ims/Order/modifyOrderDate');
    //修改订单客户人数
    Route::any('/order/modifyPassengerAmount','ims/Order/modifyPassengerAmount');
    //获取当前订单客户基本信息
    Route::any('/passenger/getBaseInfo','ims/Passenger/getAllPassengerBaseDataByOrderId');
    //获取单个客户基本信息
    Route::any('/passenger/getSinglePassengerData','ims/Passenger/getPassengerDataById');
    //新增或修改客户信息
    Route::any('/passenger/addOrUpdate','ims/Passenger/addOrUpdatePassengerData');
    //删除客户信息
    Route::any('/passenger/deletePassengerData','ims/Passenger/deletePassengerData');
    //获取获取线路所有客户信息
    Route::any('/passenger/getPassengerData','ims/Passenger/getAllPassengerDataByItineraryId');
    //获取该线路的可添加客户信息
    Route::any('/passenger/getAllFreePassenger','ims/Passenger/getAllPassengerDataByOrderId');
    //修改客户所属线路
    Route::any('/passenger/modifyPassengerItineraryInfo','ims/Passenger/modifyPassengerItineraryInfo');
    //重置客户所属线路
    Route::any('/passenger/resetPassengerItineraryInfo','ims/Passenger/resetPassengerItineraryInfo');
    //订单列表
    Route::any('/order/index','ims/Order/index');
    //获取联系人基本信息
    Route::any('/cust/getCustBaseInfo','ims/Passenger/getAllPassengerBaseDataByOrderId');
    //查询交通信息
    Route::any('/vehicle/query','ims/VehicleBasic/query');
    //查询往返交通信息
    Route::any('/vehicle/queryCityData','ims/VehicleCity/queryCityData');
    //删除联程交通节点信息
    Route::any('/vehicle/deleteConnectData','ims/VehicleConnect/deleteConnectData');



    Route::any('/test/upload','ims/Test/index');
    Route::any('/getOpenid' ,'index/Test/GetOpenid'); //微信扫码登录页面View
    Route::any('/trf/query','index/Trf/query');//查询交通信息!
    Route::any('/trf/add','index/Trf/add');
    Route::any('/testing' ,'index/Cust/testing');
    Route::any('/itin/query','index/OrderItin/query');
    Route::any('/itin/modifyDepartAndDest','index/OrderItin/modifyDepartAndDest');//修改出发地和目的地
    Route::any('/itin/getPriceInfo','index/Trf/getPriceInfo');
    Route::any('/order/modifyRepInfo','index/Order/modifyRepInfo');//修改联系人信息
    Route::any('/order/modifyCustNum','index/Order/modifyCustNum');//修改订单人数
    Route::any('/cust/getSingleInfo','index/Cust/getSingleInfo');//获取联系人信息
    Route::any('/cust/addOrUpdate','index/Cust/addOrUpdate');//添加或修改联系人信息
    Route::any('/cust/delete','index/Cust/delete');//删除联系人信息
    Route::any('/cust/checkCustInfo','index/Cust/checkCustInfo');//检查联系人信息
    Route::any('/cust/testing','index/Cust/testing');//检查联系人信息
    Route::any('/itin/getItinInfo','index/OrderItin/getItinInfo');//线路初始化接口
    Route::any('/trf/getPriceInfo','index/Trf/getPriceInfo');
    Route::any('/trf/modifyTrfSupply','index/Trf/modifyTrfSupply');//修改代订费用状态
    Route::any('/order/getStartCity','index/Order/getAllStartCity');//获取出发地
    Route::any('/order/getAllCustInfo','index/Order/getAllCustInfo');//获取所有订单用户
    Route::any('/Itin/getAllCustInfo','index/OrderItin/getAllCustInfo');//获取所有线路用户
    Route::any('/trf/getFreeCustInfo','index/Trf/getFreeCustInfo');//获取未参加交通的人员信息
    Route::any('/trf/modifyItinCustInfo','index/Trf/modifyItinCustInfo');//修改线路客户信息
    Route::any('/trf/addItinCustInfo','index/Trf/addItinCustInfo');//修改线路客户信息
    Route::any('/Itin/addStartCityAndCust','index/OrderItin/addStartCityAndCust');//添加开始城市及用户资料
    Route::any('/Itin/addOrderTrf','index/Trf/addOrderTrf');//添加仓位
    Route::any('/Itin/delOrderTrf','index/Trf/delOrderTrf');//删除仓位
    Route::any('/Order/priceOrderTrf','index/Trf/priceOrderTrf');//计算交通价格
    Route::any('/Room/modifyCheckTime','index/Room/modifyCheckTime');//修改或新增入住及离店时间
    Route::any('/Room/getBaseInfo','index/Room/getBaseInfo');//修改或新增入住及离店时间
    Route::any('/Room/addBaseInfo','index/Room/addBaseInfo');//新增房型数据
    Route::any('/Room/getPackage','index/Room/getPackage');//获取套餐信息
    Route::any('/Room/addPackageInfo','index/Room/addPackageInfo');//添加套餐套餐信息
    Route::any('/Other/getOtherInfo','index/Other/getOtherInfo');//查询其他费用信息
    Route::any('/Other/deleteOtherInfo','index/Other/deleteOtherInfo');//删除其他接口;//发送other_id;
    Route::any('/Other/addOrUpdateOtherInfo','index/Other/addOrUpdateOtherInfo');//新增或更新其他费用信息
    Route::any('/Room/getRoomInfo','index/Room/getRoomInfo');//获取房间信息
    Route::any('/Room/getFreeCustInfo','index/Room/getFreeCustInfo');//获取未添加客人信息
    Route::any('/Room/addItinCustInfo','index/Room/addItinCustInfo');//添加客人信息
    Route::any('/Room/getRoomCustInfo','index/Room/getRoomCustInfo');//获取已添加客人信息
    Route::any('/Room/modifyItinCustInfo','index/Room/modifyItinCustInfo');//修改已添加客人信息
    Route::any('/Room/addRoomCustInfo','index/Room/addRoomCustInfo');//添加客人添加套餐信息
    Route::any('/Room/delActivityDate','index/Room/delActivityDate');//删除日期接口;
    Route::any('/Room/addActivityDate','index/Room/addActivityDate');//添加日期接口;
    Route::any('/Room/getActivityDateInfo','index/Room/getActivityDateInfo');//获取活动日期信息接口;
    Route::any('/Room/updateActivityDate','index/Room/updateActivityDate');//修改活动日期信息接口;
    Route::any('/Room/getFreeActivityCustInfo','index/Room/getFreeActivityCustInfo');//获取未参加活动客户信息;
    Route::any('/Room/getModiedActivityCustInfo','index/Room/getModiedActivityCustInfo');//获取获取已添加活动客人信息
    Route::any('/Room/addActivityCustInfo','index/Room/addActivityCustInfo');//添加活动客户信息;
    Route::any('/Room/updateActivityCustInfo','index/Room/updateActivityCustInfo');//更新活动客户信息;
    Route::any('/Room/getActivityInfo','index/Room/getActivityBaseInfo');//获取基础信息活动信息;
    Route::any('/Room/getActInfo','index/Room/getActivityInfo');//获取基础信息活动信息;
    Route::any('/Room/modifyActivityStatus','index/Room/modifyActivityStatus');//获取基础信息活动信息;
    Route::any('/Calendar/getCaleInfo','index/Calendar/getCaleInfo'); //获得日历表信息
    Route::any('/Calendar/getNowCaleInfo','index/Calendar/getNowCaleInfo'); //获得选择日期的日历表
    Route::any('/Calendar/addCalendar','index/Calendar/addCalendar'); //日历表ajax添加节假日
    Route::any('/Calendar/showOrderInfo','index/Calendar/showOrderInfo'); //日历表ajax获得订单信息
    Route::any('/Calendar/deleteCalendar','index/Calendar/deleteCalendar'); //日历表删除节假日
    Route::any('/wechat/weLogin','index/wechat/weLogin');   //微信扫码登录逻辑处理
    Route::any('/wechat/weLoginView','index/wechat/weLoginView'); //微信扫码登录页面View
    Route::any('/wechat/wechatLoginView','index/wechat/wechatLoginView'); //测试微信登陆页面
    Route::any('/wechat/wechatLogin','index/wechat/wechatLogin'); //测试微信登陆逻辑
    Route::any('/wechat/qqLoginView','index/wechat/qqLoginView');  //QQ登录页面
    Route::any('/wechat/qqLogin','index/wechat/qqLogin'); //QQ登录逻辑
    Route::any('/Auth/addAuthRule','index/Auth/addAuthRule'); //auth权限控制·添加路由规则
    Route::any('/Auth/updateAuthRule','index/Auth/UpdateAuthRule'); //auth权限控制·修改路由规则
    Route::any('/Auth/authRuleView','index/Auth/authRuleView'); //auth权限控制·获得规则数据
    Route::any('/Auth/addAuthGroup','index/Auth/addAuthGroup'); //auth权限控制·添加用户组
    Route::any('/Auth/updateAuthGroup','index/Auth/updateAuthGroup'); //auth权限控制·修改用户组
    Route::any('/Auth/authGroupView','index/Auth/authGroupView'); //auth权限控制·获得用户组信息
    Route::any('/Auth/addAuthAccess','index/Auth/addAuthAccess'); //auth权限控制·添加用户关系
    Route::any('/Auth/updateAuthAccess','index/Auth/updateAuthAccess'); //auth权限控制·修改用户关系
    Route::any('/Auth/accessAuthView','index/Auth/accessAuthView'); //auth权限控制·获得用户组信息
    Route::any('/AuthRecord/showRuleRecord','index/AuthRecord/showRuleRecord'); //获得规则日志数据
    Route::any('/AuthRecord/showGruopRecord','index/AuthRecord/showGruopRecord'); //获得用户组日志数据
    Route::any('/AuthRecord/showAccessRecord','index/AuthRecord/showAccessRecord');

    Route::any('/TestWeb/wechatLoginView','index/TestWeb/wechatLoginView');  //官网扫码登录页面
    Route::any('/TestWeb/wechatLogin','index/TestWeb/wechatLogin');  //官网扫码登录
    Route::any('/TestWeb/getPersonList','index/TestWeb/getPersonList');//获得常用人列表
    Route::any('/TestWeb/updatePersonInfo','index/TestWeb/updatePersonInfo'); //修改常用人信息
    Route::any('/TestWeb/sendSMS','index/TestWeb/sendSMS'); //发送短信
});
