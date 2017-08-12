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
Route::domain('price', function(){
    Route::get('/',function(){
        return view('views2/index');
    });
    //房型功能路由组
    Route::post('room/query','ims/Room/query');
    Route::get('room/exportExcel/:roomId/:year','ims/Room/exportExcel');
    Route::get('room/getAllYear/:roomId','ims/Room/getAllYear');
    //命令行功能路由组
    Route::get('console/create/:type/:name/[:module]','ims/console/create');
    //员工功能路由组
    Route::get('employee/getAllDepartmentName','ims/department/getAllDepartmentName');
    Route::get('employee/getAllTitle/:departmentId','ims/title/getAllTitle');
    Route::get('employee/getAllEmployeeData','ims/employee/getAllEmployeeData');
    Route::get('employee/getEmployeeData/:employeeId','ims/employee/getEmployeeData');
    //导出当前酒店的计价excel
    Route::get('exportPricingExcel/:hotel_id/[:departure_date]/[:itinerary_days]','ims/pricing/exportPricingExcel');
    //线路功能组
    Route::post('route/addRouteDate','route/RouteFare/addRouteDate');//增加线路日期
    Route::post('route/modifyRouteFare','route/RouteFare/modifyRouteFare');//修改新增或修改线路价格
    Route::post('route/getRoutePlainFare','route/RouteFare/getRoutePlainFare');//初始化获取所有线路价格
    Route::post('route/getRouteFareByCheckInDate','route/RouteFare/getRouteFareByCheckInDate');//获取入住日期的价格详细
    //登录功能组
    Route::any('login','ims/EmployeeAccount/login');
    //图片功能组
    Route::any('image/multipleImageUpload','ims/Image/multipleImageUpload');//多图上传
    Route::any('image/deleteImageData','ims/Image/deleteImageData');//图片删除
    Route::any('vehicle/queryFareInfo','ims/VehicleBasic/queryFareInfo');
    Route::any('room/importExcel','ims/Room/importExcel');
    Route::any('employee/addEmployeeData','ims/employee/addEmployeeData');
    Route::any('employee/deleteEmployeeData','ims/employee/deleteEmployeeData');
    Route::any('employee/getAllEmployeeDataByDepartmentName','ims/employee/getAllEmployeeDataByDepartmentName');
    Route::any('employee/modifyEmployeeData','ims/employee/modifyEmployeeData');
    //默认交通功能组
    Route::any('defaultVehicle/addVehicleData','ims/HotelDefaultVehicle/addVehicleData');//新增默认交通信息
    Route::any('defaultVehicle/modifyVehicleData','ims/HotelDefaultVehicle/modifyVehicleData');//修改默认交通信息
    Route::any('defaultVehicle/getVehicleData','ims/HotelDefaultVehicle/getVehicleData');//获取默认交通信息
    Route::any('exportSeasonFare','ims/pricing/pricingSeasonFare');
});