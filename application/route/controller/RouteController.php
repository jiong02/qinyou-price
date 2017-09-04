<?php
namespace app\route\controller;

use app\route\model\RouteActivityModel;
use app\route\model\RouteHotelRoomModel;
use app\route\model\RouteVehicleModel;
use think\console\command\optimize\Route;
use think\Controller;
use app\route\model\RouteModel;
use app\ims\model\EmployeeAccountModel;
use app\ims\model\EmployeeModel;
use app\ims\model\TitleModel;
use think\Validate;
use app\ims\model\PlaceModel;
use app\ims\model\CountryModel;
use app\route\model\RouteExamineModel;
use app\ims\model\DepartmentModel;
use app\route\model\RoutePurchaseNotesModel;
use app\ims\model\ImageModel;

class RouteController extends Controller
{

    public function formateData($data)
    {
        if(empty($data)){
            return [];
        }

        return json_decode(json_encode($data),true);

    }

    /**
     * @name 所有线路接口
     * @auth Sam
     * @access public
     * @return mixed
     */
    public function getAllRouteList()
    {
        $request = $this->request;
        $routeModel = new RouteModel();
        $routeStatus = $request->param('route_status','');

        $routeList = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->join('ims_route_examine','ims_route.id = ims_route_examine.route_id');

        if(!empty($routeStatus) && is_numeric($routeStatus)){
            $routeList = $routeList->where('route_status',$routeStatus);
        }

        $routeList = $this->formateData($routeList->select());
//        halt($routeList);
//        $routeList = $routeList->toArray();

        if(!empty($routeList)){
            $accountModel = new EmployeeAccountModel();
            $routeCreator = '';
            $routePasser = '';
            foreach($routeList as $k=>$v){
                $routeCreator = $accountModel->field('id,account_name')->where('id',$v['route_creator_id'])->find();

                $routePasser = $accountModel->field('id,account_name')->where('id',$v['route_passer_id'])->find();

                $routeList[$k]['route_creator'] = $routeCreator['account_name'];
                $routeList[$k]['route_passer'] = $routePasser['account_name'];

                $routeCreator = '';
                $routePasser = '';
            }
        }
        return $routeList;
    }

    /**
     * @name 获取选择的线路列表
     * @auth Sam
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getChooseRouteList()
    {
        $request = $this->request;
        $placeId = $request->param('place_id',0);

        if(empty($placeId)){
            return '海岛不存在';
        }

        $routeModel = new RouteModel();

        $routeList = $routeModel->where(['destination_place_id'=>$placeId,'route_status'=>3])->select();

        if(!empty($routeList)){
            return $routeList;
        }

        return '没有海岛信息';
    }


    /**
     * @name 我的线路
     * @auth Sam
     * @access public
     * @param integer $creater_id 创建人
     * @param integer $route_status 路线状态
     * @return mixed
     */
    public function myRouteList()
    {
        $request = $this->request;
        $createrId = $request->param('creater_id','');
        $routeStatus = $request->param('route_status','');

        if(empty($createrId)){
            return '请登录系统';
        }

        $routeModel = new RouteModel();

        $routeList = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->join('ims_route_examine','ims_route.id = ims_route_examine.route_id');

        if(!empty($routeStatus) && is_numeric($routeStatus)){
            $routeList = $routeList->where('route_status',$routeStatus);
        }

        if(!empty($createrId) && is_numeric($createrId)){
            $routeList = $routeList->where('route_creator_id',$createrId);
        }

        $routeList = $routeList->select();

        if(!empty($routeList)){
            $accountModel = new EmployeeAccountModel();
            $routeCreator = '';
            $routePasser = '';
            foreach($routeList as $k=>$v){
                $routeCreator = $accountModel->field('id,account_name')->where('id',$v['route_creator_id'])->find();

                $routePasser = $accountModel->field('id,account_name')->where('id',$v['route_passer_id'])->find();


                $routeList[$k]['route_creator'] = $routeCreator['account_name'];
                $routeList[$k]['route_passer'] = $routePasser['account_name'];

                $routeCreator = '';
                $routePasser = '';
            }
        }

        return $routeList;
    }

    /**
     * @name 管理线路
     * @auth Sam
     * @access public
     * @param integer $creater_id 创建人ID
     * @param integer $route_status 订单状态
     * @return mixed
     */
    public function checkRouteList()
    {
        $request = $this->request;
        $createrId = $request->param('creater_id','');
        $routeStatus = $request->param('route_status','');

        if(empty($createrId)){
            return '请登录系统';
        }

        $accountInfo = $this->selectAccountInfo($createrId);

        if($accountInfo['is_charge'] == '否'){
            return '你不是主管级人物';
        }

        $routeModel = new RouteModel();

        $routeList = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->join('ims_route_examine','ims_route.id = ims_route_examine.route_id');

        if(empty($routeStatus)){
            $routeList = $routeList->where("route_status = 2 OR route_status = 4");
        }else{
            $routeList = $routeList->where("route_status",$routeStatus);
        }

        $routeList = $this->formateData($routeList->select());


        if(!empty($routeList)){
            $accountModel = new EmployeeAccountModel();
            $routeCreator = '';
            $routePasser = '';
            foreach($routeList as $k=>$v){
                $routeCreator = $accountModel->field('id,account_name')->where('id',$v['route_creator_id'])->find();

                $routePasser = $accountModel->field('id,account_name')->where('id',$v['route_passer_id'])->find();

                $routeList[$k]['route_creator'] = $routeCreator['account_name'];
                $routeList[$k]['route_passer'] = $routePasser['account_name'];

                $routeCreator = '';
                $routePasser = '';
            }
        }

        return $routeList;
    }

    /**
     * @name 删除订单
     * 删除订单数据 线路只有下线状态才能进行删除订单
     * @auth Sam
     * @access public
     * @param integer|string $route_id 订单ID
     * @return mixed
     */
    public function deleteRoute()
    {
        $request = $this->request;
        $routeId = $request->param('route_id',0);

        //验证~不是数字,则，返回错误
        if(empty($routeId) || !is_numeric($routeId)){
            return '订单不存在';
        }

        $routeModel = new RouteModel();

        $routeInfo = $routeModel->where('id',$routeId)->find();

        //判断订单是否存在
        if(empty($routeInfo)){
            return '订单已下线';
        }

        //订单未下线，返回错误
        if($routeInfo->route_status !== 1){
            return '订单未下线，不能做删除操作';
        }

        //删除成功
        if($routeInfo->delete()){
            return '删除成功';
        }else{
            //删除失败
            return '删除失败';
        }

    }

    /* @name 返回信息到前端
     * 返回成功或失败信息到前端的自定义函数
     * @auth Sam
     * @access public
     * @param mixed $msg 返回到前端的参数
     * @param integer|string $httpCode Http状态码
     * @param integer|string $insideCode 内部定义状态码
     * @return array
    */
    public function returnMessage($msg='',$httpCode=200,$insideCode=200)
    {
        if(empty($msg)){
            $httpCode = 404;
        }

        return ['msg'=>$msg,'http_code'=>$httpCode,'inside_code'=>$insideCode];

    }

    /**
     * @name 修改线路上下线状态
     * @auth Sam
     * @access public
     * @param string $action 上线或线下(up/down)
     * @param integer $accountId 账号ID
     * @return mixed
     */
    public function updateRouteStatus()
    {
        $request = $this->request;
        $action = $request->param('action','');
        $accountId = $request->param('account_id','');
        $routeId = $request->param('route_id',0);

        //判断是否登录
        if(empty($accountId) || !is_numeric($accountId)){
            return '请登录';
        }

        if(empty($routeId) || !is_numeric($routeId)){
            return '请输入线路';
        }

        $accInfo = $this->selectAccountInfo($accountId);

        //判断账号是否存在
        if(empty($accInfo)){
             return '账号不存在';
         }

        $routeModel = new RouteModel();
        $routeInfo = $routeModel->where('id',$routeId)->find();

        //判断线路是否存在
        if(empty($routeInfo)){
            return '线路不存在';
        }

        //上线操作
        if($action == 'up'){
            if($accInfo['is_charge'] == '是'){
                $routeInfo->route_status = 3;
                //返回前端修改结果
                if($routeInfo->save()){
                    return '上线成功';
                }else{
                    return '修改失败';
                }
            }else{
                $routeInfo->route_status = 2;
                //返回前端修改结果
                if($routeInfo->save()){
                    return '上线成功';
                }else{
                    return '修改失败,请找上级申请上线';
                }
            }
        }

        //下线操作
         if($action == 'down'){
            if($accInfo['is_charge'] == '是'){
                $routeInfo->route_status = 1;
                //返回前端修改结果
                if($routeInfo->save()){
                    return '修改成功';
                }else{
                    return '修改失败';
                }
            }else{
                $routeInfo->route_status = 4;
                //返回前端修改结果
                if($routeInfo->save()){
                    return '修改成功';
                }else{
                    return '修改失败';
                }
            }
         }

         return '没有操作';
    }

    /**
     * @name 审核线路 已上线/未上线
     * @access public
     * @auth Sam
     * @return string
     */
    public function checkRouteStatus()
    {
        $request = $this->request;
        $action = $request->param('action','');
        $accountId = $request->param('account_id',0);
        $routeId = $request->param('route_id',0);

        if(empty($action) || empty($accountId) || empty($routeId)){
            return '数据不完整';
        }

        $accountInfo = $this->publicSelectAccountInfo();

        if($accountInfo['is_charge'] == '否'){
            return '你不是主管无权进行操作';
        }

        $routeModel = new RouteModel();
        $routeInfo = $routeModel->where('id',$routeId)->find();

        if(empty($routeInfo)){
            return '线路不存在';
        }

        if($action == 'down' && $routeInfo->route_status == 4){
            $routeInfo->route_status = 3;

            if($routeInfo->save()){
                return '审核不通过';
            }

            return '审核失败';
        }

        if($action == 'up'){
            $routeInfo->route_status = 3;
//            halt($routeInfo);
            if($routeInfo->save()){
                return '审核通过';
            }else{
                return '审核失败';
            }

        }else if($action == 'down'){
            $routeInfo->route_status = 1;

            if($routeInfo->save()){
                return '审核不通过';
            }else{
                return '审核失败';
            }

        }

        return '审核失败3';

    }




    /**
     * @name 搜索线路
     * @auth Sam
     * @access public
     * @param string $search 搜索的字符串
     * @return array
     *
     */
    public function searchRoute()
    {
        $request = $this->request;
        $search = $request->param('search','');
        $routeType = $request->param('route_type',0);

        if(empty($search)){
            return '线路不存在';
        }

        $routeModel = new RouteModel();

        //所有线路
        $allRoute = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->join('ims_route_examine','ims_route.id = ims_route_examine.route_id')->where("route_name like '%$search%' OR route_code like '%$search%' OR destination_place_describe like '%$search%'")->select()->toArray();
//halt($allRoute);
        if(!empty($allRoute)){
            $allRoute = $this->routeAddName($allRoute);
        }else{
            $allRoute = [];
        }

        //未上线
        $noUpRoute = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->where('route_status',1)->join('ims_route_examine','ims_route.id = ims_route_examine.route_id')->where("route_name like '%$search%' OR route_code like '%$search%' OR destination_place_describe like '%$search%'")->select()->toArray();

        if(!empty($noUpRoute)){
            $noUpRoute = $this->routeAddName($noUpRoute);
        }else{
            $noUpRoute = [];
        }

        //申请上线
        $applyUpRoute = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->where('route_status',2)->join('ims_route_examine','ims_route.id = ims_route_examine.route_id')->where("route_name like '%$search%' OR route_code like '%$search%' OR destination_place_describe like '%$search%'")->select()->toArray();

        if(!empty($applyUpRoute)){
            $applyUpRoute = $this->routeAddName($applyUpRoute);
        }else{
            $applyUpRoute = [];
        }

        //已上线
        $upRoute = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->where('route_status',3)->join('ims_route_examine','ims_route.id = ims_route_examine.route_id')->where("route_name like '%$search%' OR route_code like '%$search%' OR destination_place_describe like '%$search%'")->select()->toArray();

        if(!empty($upRoute)){
            $upRoute = $this->routeAddName($upRoute);
        }else{
            $upRoute = [];
        }

        //申请下线
        $applyDownRoute = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->where('route_status',4)->join('ims_route_examine','ims_route.id = ims_route_examine.route_id')->where("route_name like '%$search%' OR route_code like '%$search%' OR destination_place_describe like '%$search%'")->select()->toArray();

        if(!empty($applyDownRoute)){
            $applyDownRoute = $this->routeAddName($applyDownRoute);
        }else{
            $applyDownRoute = [];
        }

        $returnInfo['all_route'] = $allRoute;
        $returnInfo['no_up_route'] = $noUpRoute;
        $returnInfo['apply_up_route'] = $applyUpRoute;
        $returnInfo['up_route'] = $upRoute;
        $returnInfo['apply_down_route'] = $applyDownRoute;

        return $returnInfo;
    }


    /**
     * @name 搜索我的线路
     * @auth Sam
     * @access public
     * @param string $search 搜索的字符串
     * @return array
     *
     */
    public function searchMyRoute()
    {
        $request = $this->request;
        $search = $request->param('search','');
        $routeType = $request->param('route_type',0);
        $accountId = $request->param('account_id',0);

        if(empty($search) || empty($accountId)){
            return '线路不存在';
        }

        $routeModel = new RouteModel();

        //所有线路
        $allRoute = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->join('ims_route_examine','ims_route.id = ims_route_examine.route_id')->where("route_name like '%$search%' OR route_code like '%$search%' OR destination_place_describe like '%$search%'")->where('route_creator_id = '.$accountId)->select()->toArray();
//halt($allRoute);
        if(!empty($allRoute)){
            $allRoute = $this->routeAddName($allRoute);
        }else{
            $allRoute = [];
        }

        //未上线
        $noUpRoute = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->where('route_status',1)->join('ims_route_examine','ims_route.id = ims_route_examine.route_id')->where("route_name like '%$search%' OR route_code like '%$search%' OR destination_place_describe like '%$search%'")->where('route_creator_id = '.$accountId)->select()->toArray();

        if(!empty($noUpRoute)){
            $noUpRoute = $this->routeAddName($noUpRoute);
        }else{
            $noUpRoute = [];
        }

        //申请上线
        $applyUpRoute = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->where('route_status',2)->join('ims_route_examine','ims_route.id = ims_route_examine.route_id')->where("route_name like '%$search%' OR route_code like '%$search%' OR destination_place_describe like '%$search%'")->where('route_creator_id = '.$accountId)->select()->toArray();

        if(!empty($applyUpRoute)){
            $applyUpRoute = $this->routeAddName($applyUpRoute);
        }else{
            $applyUpRoute = [];
        }

        //已上线
        $upRoute = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->where('route_status',3)->join('ims_route_examine','ims_route.id = ims_route_examine.route_id')->where("route_name like '%$search%' OR route_code like '%$search%' OR destination_place_describe like '%$search%'")->where('route_creator_id = '.$accountId)->select()->toArray();

        if(!empty($upRoute)){
            $upRoute = $this->routeAddName($upRoute);
        }else{
            $upRoute = [];
        }

        //申请下线
        $applyDownRoute = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->where('route_status',4)->join('ims_route_examine','ims_route.id = ims_route_examine.route_id')->where("route_name like '%$search%' OR route_code like '%$search%' OR destination_place_describe like '%$search%'")->where('route_creator_id = '.$accountId)->select()->toArray();

        if(!empty($applyDownRoute)){
            $applyDownRoute = $this->routeAddName($applyDownRoute);
        }else{
            $applyDownRoute = [];
        }

        $returnInfo['all_route'] = $allRoute;
        $returnInfo['no_up_route'] = $noUpRoute;
        $returnInfo['apply_up_route'] = $applyUpRoute;
        $returnInfo['up_route'] = $upRoute;
        $returnInfo['apply_down_route'] = $applyDownRoute;

        return $returnInfo;
    }

    /**
     * @name 审核线路
     * @auth Sam
     * @access public
     * @param string $search 搜索内容
     * @param integer $route_type 状态
     * @param integer $account_id 账号ID
     * @return mixed
     */
    public function examineRoute()
    {
        $request = $this->request;
        $search = $request->param('search','');
        $routeType = $request->param('route_type',0);
        $accountId = $request->param('account_id',0);

        if(empty($search) || empty($accountId)){
            return '线路不存在';
        }

        $accountInfo = $this->selectAccountInfo($accountId);

        if($accountInfo['is_charge'] == '否'){
            return '你不是主管级人物';
        }

        $routeModel = new RouteModel();

        //所有线路
        $allRoute = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->join('ims_route_examine','ims_route.id = ims_route_examine.route_id')->where("route_name like '%$search%' OR route_code like '%$search%' OR destination_place_describe like '%$search%' AND route_status = 2 AND route_status = 4")->select();

        if(!empty($allRoute)){
            $allRoute = $this->routeAddName($allRoute);
        }else{
            $allRoute = [];
        }

        //申请上线
        $applyUpRoute = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->where('route_status',2)->join('ims_route_examine','ims_route.id = ims_route_examine.route_id')->where("route_name like '%$search%' OR route_code like '%$search%' OR destination_place_describe like '%$search%' AND route_status = 2 ")->select();

        if(!empty($applyUpRoute)){
            $applyUpRoute = $this->routeAddName($applyUpRoute);
        }else{
            $applyUpRoute = [];
        }

        //申请下线
        $applyDownRoute = $routeModel->field('ims_route.*,ims_route_examine.*,ims_route_examine.id as examine_id,ims_route.id as route_id')->where('route_status',4)->join('ims_route_examine','ims_route.id = ims_route_examine.route_id')->where("route_name like '%$search%' OR route_code like '%$search%' OR destination_place_describe like '%$search%' AND route_status = 4")->select();

        if(!empty($applyDownRoute)){
            $applyDownRoute = $this->routeAddName($applyDownRoute);
        }else{
            $applyDownRoute = [];
        }

        $returnInfo['all_route'] = $allRoute;

        $returnInfo['apply_up_route'] = $applyUpRoute;

        $returnInfo['apply_down_route'] = $applyDownRoute;

        return $returnInfo;
    }



    /**
     * @name 线路数组添加创建人与审核人名称
     * @auth Sam
     * @access public
     * @param array $data 线路数组
     * @return array
     */
    protected function routeAddName($routeList)
    {
        if(empty($routeList)){
            return false;
        }


            $accountModel = new EmployeeAccountModel();
            $routeCreator = '';
            $routePasser = '';
            foreach($routeList as $k=>$v){
                $routeCreator = $accountModel->field('id,account_name')->where('id',$v['route_creator_id'])->find();

                $routePasser = $accountModel->field('id,account_name')->where('id',$v['route_passer_id'])->find();

                $routeList[$k]['route_creator'] = $routeCreator['account_name'];
                $routeList[$k]['route_passer'] = $routePasser['account_name'];

                $routeCreator = '';
                $routePasser = '';
            }

        return $routeList;
    }


    /**
     * @name 根据ID查询账户信息
     * @auth Sam
     * @access public
     * @param integer $accountId 账号ID
     * @return array
     */
    public function selectAccountInfo($accountId)
    {
        if(empty($accountId)){
            return false;
        }

        $accountModel = new EmployeeAccountModel();

        $empModel = new EmployeeModel();

        $titleModel = new TitleModel();

        $accountInfo = $this->formateData($accountModel->field('id as account_id,ims_employee_account.*')->where('id',$accountId)->find());

        if(empty($accountInfo)){
            return false;
        }

        unset($accountInfo['id']);

        $empInfo = $this->formateData($empModel->field('ims_employee.*,id as employee_id')->where('account_name',$accountInfo['account_name'])->find());
        unset($empInfo['id']);

        $accountInfo = array_merge($accountInfo,$empInfo);

        $titleInfo = $this->formateData($titleModel->field('ims_title.id as title_id,ims_title.*')->where('department_id',$accountInfo['department_id'])->find());
        unset($titleInfo['id']);

        $accountInfo = array_merge($accountInfo,$titleInfo);

        return $accountInfo;
    }

    /**
     * @name 根据ID查询账户信息
     * @auth Sam
     * @access public
     * @param integer $accountId 账号ID
     * @return array
     */
    public function publicSelectAccountInfo()
    {
        $request = $this->request;
        $accountId = $request->param('account_id',0);
        $accountName = $request->param('account_name','');

        if(empty($accountId)){
            return '账号不存在';
        }


        $accountModel = new EmployeeAccountModel();

        $empModel = new EmployeeModel();

        $titleModel = new TitleModel();

        $accountInfo = $this->formateData($accountModel->field('id as account_id,ims_employee_account.*')->where('id',$accountId)->find());

        if(empty($accountInfo)){
            return '账号不存在2';
        }

        unset($accountInfo['id']);

        $empInfo = $this->formateData($empModel->field('ims_employee.*,id as employee_id')->where('account_name',$accountInfo['account_name'])->find());
        unset($empInfo['id']);

        $accountInfo = array_merge($accountInfo,$empInfo);

        $titleInfo = $this->formateData($titleModel->field('ims_title.id as title_id,ims_title.*')->where('id',$accountInfo['title_id'])->find());
        unset($titleInfo['id']);

        $accountInfo = array_merge($accountInfo,$titleInfo);

        $departmentModel = new DepartmentModel();

        $departmentInfo = $this->formateData($departmentModel->where('id',$accountInfo['department_id'])->find());
        unset($departmentInfo['id']);

        $accountInfo = array_merge($accountInfo,$departmentInfo);

        $returnArray['account_name'] = $accountInfo['account_name'];
        $returnArray['title'] = $accountInfo['title'];
        $returnArray['department_name'] = $accountInfo['department_name'];
        $returnArray['is_charge'] = $accountInfo['is_charge'];


        return $returnArray;
    }


    /**
     * @name 创建线路信息
     * @auth Sam
     * @access public
     * @param mixed $routeInfo 线路数据
     * @return string
     */
    public function createRouteInfo()
    {
        $request = $this->request;
        $routeInfo = $request->param('route_info/a','');
        $routeCreatorId = $routeInfo['route_creator_id'];
        unset($routeInfo['route_creator_id']);
//halt($routeInfo);
        if(empty($routeCreatorId)){
            return '请登录系统';
        }

        if(empty($routeInfo) || !is_array($routeInfo)){
            return '请输入完整的数据';
        }

        //没有线路ID则新建线路ID
        if(empty($routeInfo['route_code'])){
            $routeType = $routeInfo['route_type'];
            $packageName = $routeInfo['package_name'];

            if(empty($routeType) || empty($packageName)){
                return '请输入完整的数据2';
            }

            $checkPack = $this->checkPackageName($packageName);
            if(empty($checkPack)){
                return '请输入正确的套餐名称';
            }

            $routeCode = $this->getPackageCode($routeType,$packageName);

            if(empty($routeCode)){
                return '请输入完整的数据3';
            }

            $routeInfo['route_code'] = $routeCode;
        }

        $routeModel = new RouteModel();

        //验证数据
        $validateController = new Validate($routeModel->rule);
        if(!$validateController->check($routeInfo)){
            return $validateController->getError();
        }


        if(!empty($routeInfo['id'])){
            $routeModel = $routeModel->where('id',$routeInfo['id'])->find();
        }

        if(empty($routeModel)){
            $routeModel = new RouteModel();
        }
//halt('bb');
        //线路添加成功后，新增审核信息
        if($routeModel->save($routeInfo)){
//            return $routeModel->id;
                        $routeId = $routeModel->id;
                        $examineModel = new RouteExamineModel();

                        $examineModel = $examineModel->where('route_id',$routeId)->find();

                        if(!empty($examineModel)){
                            return '修改成功';
                        }else{
                            $examineModel = new RouteExamineModel();
                        }

                        $passerInfo = $this->getSuperiorInfo($routeCreatorId);

                        if(empty($passerInfo)){
                            return '没有上级信息';
                        }

                        $examineModel->route_id = $routeId;
                        $examineModel->route_creator_id = $routeCreatorId;
                        $examineModel->route_passer_id = $passerInfo['account_id'];
                        $examineModel->route_create_time = date('Y-m-d',time());
/*halt('aa');*/
                        if($examineModel->save()){
//                            echo 'aa';
                            return (int)$routeModel->id;
                        }else{
                            $routeModel = $routeModel->where('id',$routeId)->find();
                            $routeModel->delete();

                            return '修改失败';
                        }

        }

        return '修改失败';

    }


    /**
     * @name 修改线路信息
     * @auth Sam
     * @access public
     * @param mixed $routeInfo 线路数据
     * @return string
     */
    public function updateRouteInfo()
    {
        $request = $this->request;
        $routeInfo = $request->param('route_info/a','');

        if(!empty($routeInfo['route_creator_id'])){
            unset($routeInfo['route_creator_id']);
        }

        if(empty($routeInfo) || !is_array($routeInfo)){
            return '请输入完整的数据';
        }

        //没有线路ID则新建线路ID
        if(empty($routeInfo['route_code'])){
            $routeType = $routeInfo['route_type'];
            $packageName = $routeInfo['package_name'];

            if(empty($routeType) || empty($packageName)){
                return '请输入完整的数据2';
            }

            $checkPack = $this->checkPackageName($packageName);
            if(empty($checkPack)){
                return '请输入正确的套餐名称';
            }

            $routeCode = $this->getPackageCode($routeType,$packageName);

            if(empty($routeCode)){
                return '请输入完整的数据3';
            }

            $routeInfo['route_code'] = $routeCode;
        }

        $routeModel = new RouteModel();

        //验证数据
        $validateController = new Validate($routeModel->rule);
        if(!$validateController->check($routeInfo)){
            return $validateController->getError();
        }


        if(!empty($routeInfo['id'])){
            $routeModel = $routeModel->where('id',$routeInfo['id'])->find();
        }

        if(empty($routeModel)){
            $routeModel = new RouteModel();
        }

        if(!empty($routeInfo['action_type'])){
            $actionType = $routeInfo['action_type'];
            unset($routeInfo['action_type']);
        }

        //线路添加成功后，新增审核信息
        if($routeModel->save($routeInfo)){
            if(!empty($actionType) && $actionType == 'delete_info'){
                $routeActivityModel = new RouteActivityModel();
                $routeActivityModel = $routeActivityModel->where('route_id',$routeModel->id)->delete();

/*                if(!empty($routeActivityModel)){
                    $routeActivityModel->delete();
                }*/


                $routeRoomModel = new RouteHotelRoomModel();
                $routeRoomModel = $routeRoomModel->where('route_id',$routeModel->id)->delete();

/*                if(!empty($routeRoomModel)){
                    $routeRoomModel->delete();
                }*/


                $routeVehicleModel = new RouteVehicleModel();
                $routeVehicleModel = $routeVehicleModel->where('route_id',$routeModel->id)->delete();

/*                if(!empty($routeVehicleModel)){
                    $routeVehicleModel->delete();
                }*/

            }


            return $routeModel->id;
        }

        return '修改失败';

    }

    /**
     * @name 通过ID获得上级的信息
     * @auth Sam
     * @access public
     * @param $userId
     * @return bool|mixed
     *
     */
    public function getSuperiorInfo($userId)
    {
        if(empty($userId)){
            return false;
        }

        $empAccountModel = new EmployeeAccountModel();
        $empAccountInfo = $empAccountModel->where('id',$userId)->find();
        $empAccountInfo = $empAccountInfo->toArray();
//        halt($empAccountInfo);

        $employeeModel = new EmployeeModel();
        $exmInfo = $this->formateData($employeeModel->where('account_name',$empAccountInfo['account_name'])->find());
//halt($exmInfo);
        if(empty($exmInfo)){
            return false;
        }

        //查看部门主管
        $titleModel = new TitleModel();
        $titleList = $this->formateData($titleModel->where('is_charge','是')->select());

        if(empty($titleList)){
            return false;
        }

        $titleId = 0;
        foreach($titleList as $k=>$v){
            if($v['department_id'] == $exmInfo['department_id']){
                $titleId = $v['id'];
                break;
            }
        }
//var_dump($titleList);
        $upInfo = $this->formateData($employeeModel->where('title_id',$titleId)->find());
        if(empty($upInfo)){
            return false;
        }

        $empAccountInfo = $empAccountModel->where('account_name',$exmInfo['account_name'])->find();

        $upInfo['account_id'] = $empAccountInfo->id;

        return $upInfo;

    }


    /**
     * @name 检测套餐名称是否正确
     * @auth Sam
     * @param $packName
     * @return bool
     */
    public function checkPackageName($packName)
    {
        if(empty($packName)){
            return false;
        }

        $strOne = substr($packName,0,1);
        $strTwo = substr($packName,1,1);
        $strThree = substr($packName,2,1);
        $strFour = substr($packName,3,1);

        $ok = 0;

        if(!empty($strOne) && is_numeric($strOne)){
            $ok = $ok + 1;
        }

        if(!empty($strTwo) && $strTwo == 'D'){
            $ok = $ok + 1;
        }

        if(!empty($strThree) && is_numeric($strThree)){
            $ok = $ok + 1;
        }

        if(!empty($strFour) && $strFour == 'N'){
            $ok = $ok + 1;
        }

        if($ok == 4){
            return true;
        }
        return false;

    }


    /**
     * @name 获得线路编码
     * @auth Sam
     * @access public
     * @param string $packageName 套餐名称 3D2N
     * @param integer $type 状态 1=A 2=B 3=
     * @return bool|string
     */
    public function getPackageCode($type,$packageName,$returnCode='')
    {

        if(!empty($returnCode)){
            return $returnCode;
        }

        $day = substr($packageName,0,1);

        $night = substr($packageName,2,1);

        //数据库查询线路编码
        $likePackCode = '';

        switch($type){
            case '1':
                $likePackCode = 'A'.'0'.$day.'0'.$night;
            break;

            case '2':
                $likePackCode = 'B'.'0'.$day.'0'.$night;
            break;

            case '3':
                $likePackCode = 'C'.'0'.$day.'0'.$night;
            break;
        }

        if(empty($likePackCode)){
            return false;
        }

        $routeModel = new RouteModel();

        $routeInfo = $routeModel->where("route_code like '%$likePackCode%'")->order('route_code desc')->find();

        if(empty($routeInfo)){
            return $likePackCode.'0001';
        }

        $num = substr($routeInfo['route_code'],5);

        $num = (int)$num + 1;

        $string = (string)$num;

        $count = 4;

        $strLength = strlen($string);
        $zero = '';

        for($strLength;$strLength<$count;$strLength++){
            $zero .= '0';
        }

        return $likePackCode.$zero.$string;

    }

    /**
     * @name 获得海岛列表与国家信息
     * @auth Sam
     * @access public
     * @param string $action 海岛或国家
     * @param integer $countryId 国家ID
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getCountryPlaceInfo()
    {
        $request = $this->request;
        $action = $request->param('action','');
        $countryId = $request->param('country_id',0);

        if($action == 'place'){
            $placeModel = new PlaceModel();

            $placeList = $placeModel->field('id,place_name as value,country_id')->select();

            if(!empty($placeList)){
                return $placeList;
            }

        }

        if($action == 'country'){
            if(empty($countryId)){
                return '缺少参数';
            }

            $countryModel = new CountryModel();

            $countryInfo = $countryModel->field('id,country_name')->where('id',$countryId)->find();

            if(!empty($countryInfo)){
                return $countryInfo;
            }

        }

        return '没有信息';

    }

    /**
     * @name 获得线路基本信息
     * @auth Sam
     * @access public
     * @param integer $routeId 线路ID
     * @return array|mixed|string
     */
    public function getRouteInfo()
    {
        $request = $this->request;
        $routeId = $request->param('route_id',0);

        if(empty($routeId)){
            return '线路不存在';
        }

        $routeModel = new RouteModel();
        $routeInfo = $this->formateData($routeModel->where('id',$routeId)->find());

        if(empty($routeInfo)){
            return '线路不存在';
        }

        $imageInfo = $this->getImageInfo($routeInfo['image_uniqid']);

        $routeInfo['image_info'] = $imageInfo;

        return $routeInfo;

    }

    /**
     * @name 购买须知
     * @auth Sam
     * @access public
     * @param mixed $routeId 线路ID
     * @param mixed $hint 重要提示
     * @param mixed $agreement 退改协议
     * @return mixed|string
     */
    public function updateBuyKnow()
    {
        $request = $this->request;
        $notes = $request->param('notes_info/a',array());

        if(empty($notes) || !is_array($notes)){
            return '请输入完整的数据';
        }

        $notesModel = new RoutePurchaseNotesModel();

        $validateController = new Validate($notesModel->rule);

        if(!$validateController->check($notes)){
            return $validateController->getError();
        }

        if(!empty($notes['id'])){
            if($notesModel->update($notes)){
                return $notes['id'];
            }
        }else{
            if($notesModel->save($notes)){
                return $notesModel->id;
            }
        }
        return '修改失败';
    }


    /**
     * @name 查看购买须知
     * @auth Sam
     * @return array|string
     */
    public function selectBuyKnow()
    {
        $request = $this->request;
        $routeId = $request->param('route_id',0);

        if(empty($routeId)){
            return '线路不存在';
        }

        $notesModel = new RoutePurchaseNotesModel();

        //购买须知信息
        $notesInfo = $this->formateData($notesModel->where('route_id',$routeId)->find());

        //线路信息
        $routeInfo = $this->getRouteInfo();

        if(empty($routeInfo) || $routeInfo == '线路不存在'){
            return '线路不存在';
        }

        $return['route_id'] = $routeInfo['id'];
        $return['departure_place_describe'] = $routeInfo['departure_place_describe'];
        $return['destination_place_describe'] = $routeInfo['destination_place_describe'];
        $return['route_type'] = $routeInfo['route_type'];
        $return['route_code'] = $routeInfo['route_code'];
        $return['package_name'] = $routeInfo['package_name'];
        $return['max_passengers'] = $routeInfo['max_passengers'];
        $return['min_passengers'] = $routeInfo['min_passengers'];
        $return['destination_place_id'] = $routeInfo['destination_place_id'];


        if(!empty($notesInfo)){
            $notesInfo['note_id'] = $notesInfo['id'];
            unset($notesInfo['id']);
            unset($notesInfo['create_time']);
            unset($notesInfo['modify_time']);
        }else{
            $notesInfo['agreement'] = '';
            $notesInfo['hint'] = '';
            $notesInfo['cost_includes'] = '';
            $notesInfo['free_item'] = '';
            $notesInfo['cost_except'] = '';
        }

        $newReturn = array_merge($return,$notesInfo);

        $newReturn['country_name'] = $routeInfo['country_name'];

        return $newReturn;
    }

    /**
     * @name 通过uniqid 查询图片信息
     * @param string $imageUniqid
     * @return array|mixed
     */
    public function getImageInfo($imageUniqid='')
    {
        $imageId = 0;
        $request = $this->request;
        $reqImageUniqid = $request->param('image_uniqid','');

        if(empty($imageUniqid)){
            $imageId = $reqImageUniqid;
        }

        if(empty($reqImageUniqid)){
            $imageId = $imageUniqid;
        }

        if(empty($imageId)){
            return array();
        }

        $imageModel = new ImageModel();

        $imageInfo = $this->formateData($imageModel->field('id,image_uniqid,image_category,image_path')->where('image_uniqid',$imageId)->select());

        if(empty($imageInfo)){
            return array();
        }

        return $imageInfo;

    }

    /**
     * @name 通过ID集删除图片
     * @auth Sam
     * @access public
     * @param mixed $uniqidList ID集合(1,2,5,7)
     * @return string
     */
    public function deleteImageList()
    {
        $request = $this->request;
        $uniqidList = $request->param('image_uniqid_list','');

        $uniqidList = trim($uniqidList,',');

        if(empty($uniqidList)){
            return '图片删除失败';
        }

        $imageModel = new ImageModel();

        if($imageModel->where("id in ($uniqidList)")->delete()){
            return '删除成功';
        }


        return '删除失败';
    }

    /**
     * @name 通过线路ID获取图片信息
     * @auth Sam
     * @return array|bool|mixed
     */
    public function getRouteImage()
    {
        $request = $this->request;
        $routeId = $request->param('route_id',0);

        if(empty($routeId)){
            return false;
        }

        $routeModel = new RouteModel();

        $imageModel = new ImageModel();

        $routeInfo = $this->formateData($routeModel->where('id',$routeId)->find());

        if(empty($routeInfo)){
            return false;
        }

        $imageInfo = $this->formateData($imageModel->field('id,image_uniqid,image_path')->where('image_uniqid',$routeInfo['image_uniqid'])->find());

        return $imageInfo;


    }





}





?>