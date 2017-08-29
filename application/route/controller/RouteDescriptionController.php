<?php
namespace app\route\controller;

use app\route\model\RouteActivityModel;
use think\Controller;
use think\Request;
use app\route\model\RouteHotelRoomModel;
use app\route\model\RouteVehicleModel;
use app\route\model\RouteDescriptionModel;
use app\route\model\RouteDescriptionVehicleModel;
use app\route\model\RouteDescriptionHotelModel;
use app\route\model\RouteDescriptionOtherInfoModel;
use app\route\model\RouteDescriptionActivityModel;
use think\Validate;
use app\ims\model\HotelModel;
use app\ims\model\ImageModel;
use app\route\model\RouteModel;
use app\route\controller\RouteController;
use app\route\model\RoutePurchaseNotesModel;
use app\ims\model\HotelFacilityModel;


class RouteDescriptionController extends Controller
{
    public $routeId;
    public $routeHotelId;
    public $routeVehicleId;
    public $routeActivityId;
    public $hotelId;
    public $activityId;
    public $vehicleId;
    public $descriptionId;
    public $descVehicleId;
    public $descOtherInfoId;
    public $descActivityId;

    /**
     * @name 获得重要变量
     * @auth Sam
     * @access public
     * RouteDescriptionController constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->routeId = $request->param('route_id',0);
        $this->routeHotelId = $request->param('route_hotel_id',0);
        $this->routeVehicleId = $request->param('route_vehicle_id',0);
        $this->routeActivityId = $request->param('route_activity_id',0);
        $this->hotelId = $request->param('hotel_id',0);
        $this->activityId = $request->param('activity_id',0);
        $this->vehicleId = $request->param('vehicle_id',0);
        $this->descriptionId = $request->param('description_id',0);
        $this->descVehicleId = $request->param('desc_vehicle_id',0);
        $this->descOtherInfoId = $request->param('desc_other_id',0);
        $this->descActivityId = $request->param('desc_activity_id',0);
    }

    /**
     * @param $data
     * @return bool|mixed
     */
    public function formateData($data)
    {
        if(empty($data)){
            return false;
        }

        $data = json_decode(json_encode($data),true);

        return $data;
    }

    /**
     * @name 获得酒店房型信息
     * @auth Sam
     * @access public
     * @param number $this->routeId 路线ID
     * @return bool|mixed|string
     */
    public function getRouteHotelList()
    {
        if(empty($this->routeId)){
            return '线路不存在';
        }

        $routeHotelRoomModel = new RouteHotelRoomModel();

        $roomList = $this->formateData($routeHotelRoomModel->field('id,route_id,hotel_id,hotel_name,room_id,route_room_name,check_in_night_amount')->where('route_id',$this->routeId)->select());

        if(!empty($roomList)){
            return $roomList;
        }

        return '没有酒店信息';
    }

    /**
     * @name 获得线路交通列表
     * @auth Sam
     * @access public
     * @param mixed $this->routeId 线路ID
     * @return bool|mixed|string
     */
    public function getRouteVehicleList()
    {
        if(empty($this->routeId)){
            return '线路不存在';
        }

        $vehicleModel = new RouteVehicleModel();

        $vehicleList = $this->formateData($vehicleModel->where('route_id',$this->routeId)->select());

        if(!empty($vehicleList)){
            return $vehicleList;
        }

        return '线路不存在';
    }

    /**
     * @name 获得线路活动列表
     * @auth Sam
     * @access public
     * @param mixed $this->routeId 线路ID
     * @return bool|mixed|string
     */
    public function getActivityList()
    {
        if(empty($this->routeId)){
            return '线路不存在';
        }

        $activityModel = new RouteActivityModel();

        $activityList = $this->formateData($activityModel->where('route_id',$this->routeId)->select());

        if(!empty($activityList)){
            return $activityList;
        }

        return '线路不存在';

    }

    /**
     * @name 修改描述页面数据
     * @access public
     * @auth Sam
     * @param array $descInfo 描述数据
     * @return string
     */
    public function updateDescription(Request $request)
    {
        $descInfo = $request->param('desc_info/a',array());
        $returnError = '';

        if(empty($descInfo) || !is_array($descInfo)){
            return '请填写完整数据';
        }

        $descModel = new RouteDescriptionModel();
        $descActivityModel = new RouteDescriptionActivityModel();
        $descHotelModel = new RouteDescriptionHotelModel();
        $descVehicleModel = new RouteDescriptionVehicleModel();
        $descOtherInfoModel = new RouteDescriptionOtherInfoModel();

        $mysqlDescInfo['route_id'] = $descInfo['route_id'];
        $mysqlDescInfo['departure_place_name'] = $descInfo['departure_place_name'];
        $mysqlDescInfo['package_day'] = $descInfo['package_day'];
        $mysqlDescInfo['package_name'] = $descInfo['package_name'];
        if(!empty($descInfo['id'])){
            $mysqlDescInfo['id'] = $descInfo['id'];
        }


        //验证描述数据
        if(! ($vaData = $this->checkMysqlData($mysqlDescInfo,$descModel->rule,1))){
            return $vaData;
        }

        //验证交通数据
        if(!empty($descInfo['desc_vehicle_info'])){
            if(!($vaData = $this->checkMysqlData($descInfo['desc_vehicle_info'],$descModel->rule,count($descInfo['desc_vehicle_info'])))){
                return $vaData;
            }
        }


        //验证酒店数据
        if(!empty($descInfo['desc_room_info'])){
            if(! ($vaData = $this->checkMysqlData($descInfo['desc_room_info'],$descModel->rule,count($descInfo['desc_room_info'])))){
                return $vaData;
            }
        }


        //验证活动数据
        if(!empty($descInfo['desc_activity_info'])){
            if(! ($vaData = $this->checkMysqlData($descInfo['desc_activity_info'],$descModel->rule,count($descInfo['desc_activity_info'])))){
                return $vaData;
            }
        }


        $descId = $this->updateDesc($mysqlDescInfo,$descModel);

        if(!empty($descInfo['id'])){
            $descId = $descInfo['id'];
        }

        if(empty($descId)){
            return '修改描述失败';
        }


        //修改描述交通信息
        if(!empty($descInfo['desc_vehicle_info'])){
            $vehicleResult = $this->updateDescVehicle($descInfo['desc_vehicle_info'],$descVehicleModel,$descId,$descInfo['route_id']);

            if(empty($vehicleResult)){
                $returnError = '交通没有修改,';
            }
        }


        //修改描述活动信息
        if(!empty($descInfo['desc_activity_info'])){
            $activityResult = $this->updateDescActivity($descInfo['desc_activity_info'],$descActivityModel,$descId,$descInfo['route_id']);

            if(empty($activityResult)){
                $returnError .= '活动没有修改,';
            }
        }


        //修改描述酒店信息
        if(!empty($descInfo['desc_room_info'])){
            $hotelRoomResult = $this->updateDescHotel($descInfo['desc_room_info'],$descHotelModel,$descId,$descInfo['route_id']);

            if(empty($hotelRoomResult)){
                $returnError .= '酒店没有修改,';
            }
        }


        //修改描述其他信息
        if(!empty($descInfo['desc_other_info'])){
            $descOtherResult = $this->updateOtherInfo($descInfo['desc_other_info'],$descOtherInfoModel,$descId,$descInfo['route_id']);

            if(empty($descOtherResult)){
                $returnError .= '其他信息没有修改';
            }
        }

        if(!empty($returnError)){
            return $returnError;
        }

        return '修改成功';
    }

    /**
     * @name 修改描述其他数据
     * @param $data
     * @param $model
     * @param $descId
     * @param $routeId
     * @return bool
     */
    public function updateOtherInfo($data,$model,$descId,$routeId)
    {
        if(empty($data) || empty($model) || empty($descId) || empty($routeId)){
            return false;
        }

        $start = true;
        foreach($data as $k=>$v){
            if(!empty($v['id'])){
                $model = $model->where('id',$v['id'])->find();
            }

            if(empty($model)){
                $model = new RouteDescriptionOtherInfoModel();
            }

            $model->description_id = $descId;
            $model->image_uniqid = $v['image_uniqid'];
            $model->other_info_description = $v['other_info_description'];

            if(!empty($v['id'])){
                $result = $model->save();
            }else{
                $result = $model->save();
            }

            if(empty($result)){
                $start = false;
            }
            $model = new RouteDescriptionOtherInfoModel();
        }

        return $start;


    }


    /**
     * @name 修改描述信息
     * @auth Sam
     * @Access public
     * @param $data 描述数据
     * @param $model 描述模型
     * @return bool|mixed
     */
    public function updateDesc($data,$model)
    {
        if(!empty($data['id'])){
            $descriptionModel = $model->where('id',$data['id'])->find();
        }

        if(empty($descriptionModel)){
            $descriptionModel = new RouteDescriptionModel();
        }

        $descriptionModel->route_id = $data['route_id'];
        $descriptionModel->departure_place_name = $data['departure_place_name'];
        $descriptionModel->package_day = $data['package_day'];
        $descriptionModel->package_name = $data['package_name'];
//halt($descriptionModel);
        if(!empty($data['id'])){
            $result = $descriptionModel->save();
        }else{
            $result = $descriptionModel->save();
        }

        if(!empty($result)){
            return $descriptionModel->id;
        }

        return false;

    }

    /**
     * @name 修改描述交通信息
     * @param $data 交通数据
     * @param $model 交通模型
     * @param $descId 描述ID
     * @param $routeId 线路ID
     * @return bool|true
     */
    public function updateDescVehicle($data,$model,$descId,$routeId)
    {
        if(empty($data) || empty($descId) || empty($model) || empty($routeId)){
            return false;
        }

        $start = true;
        foreach($data as $k=>$v){
            if(!empty($v['id'])){
                $model = $model->where('id',$v['id'])->find();
            }else{
                $model = new RouteDescriptionVehicleModel();
            }

            if(empty($model)){
                $model = new RouteDescriptionVehicleModel();
            }

            $model->description_id = $descId;
            $model->route_vehicle_id = $v['route_vehicle_id'];
            $model->vehicle_name = $v['vehicle_name'];
            $model->description_start_time = $v['description_start_time'];
            $model->vehicle_description = $v['vehicle_description'];
            $model->vehicle_id = $v['vehicle_id'];

            if(!empty($v['id'])){
                $result = $model->save();
            }else{
                $result = $model->save();
            }



            if(!empty($result)){
                $routeVehicleModel = RouteVehicleModel::get(['id'=>$v['route_vehicle_id']]);

                if(!empty($routeVehicleModel)){
                    $routeVehicleModel->is_show = 0;
                    $routeVehicleModel->update();
                }
            }else{
                $start = false;
            }
            $model = new RouteDescriptionVehicleModel();
        }

        return $start;

    }

    /**
     * @name 修改描述酒店信息
     * @param $data
     * @param $model
     * @param $descId
     * @param $routeId
     * @return bool
     */
    public function updateDescHotel($data,$model,$descId,$routeId)
    {
        if(empty($data) || empty($descId) || empty($model) || empty($routeId)){
            return false;
        }

        $start = true;
        foreach($data as $k=>$v){
            if(!empty($v['id'])){
                $model = $model->where('id',$v['id'])->find();
            }else{
                $model = new RouteDescriptionHotelModel();
            }

            if(empty($model)){
                $model = new RouteDescriptionHotelModel();
            }

            if(empty($model)){
                $oldHotelRoomId = $model->room_id;
            }

            $newHotelRoomId = $v['room_id'];

            $model->description_id = $descId;
            $model->route_hotel_id = $v['route_hotel_id'];
            $model->hotel_id = $v['hotel_id'];
            $model->hotel_name = $v['hotel_name'];
            $model->image_show_status = $v['image_show_status'];
            $model->image_uniqid = $v['image_uniqid'];
            $model->hotel_description = $v['hotel_description'];
            $model->hotel_breakfast = $v['hotel_breakfast'];
            $model->hotel_lunch = $v['hotel_lunch'];
            $model->hotel_dinner = $v['hotel_dinner'];
            $model->room_id = $v['room_id'];
            $model->room_name = $v['room_name'];
            $model->meal_description = $v['meal_description'];

            if(!empty($v['id'])){
                $result = $model->save();
            }else{
                $result = $model->save();
            }

            if(!empty($result)){
                //酒店旧ID为空，则只修改新酒店晚数
                if(empty($oldhotelRoomId)){
                    $routeHotelModel = RouteHotelRoomModel::get(['route_id'=>$routeId,'hotel_id'=>$newHotelRoomId,'room_id'=>$newHotelRoomId]);

                    if(!empty($routeHotelModel)){
                        $newRoomNight = $routeHotelModel->check_in_night_amount - 1;

                        if($newRoomNight < 0){
                            $newRoomNight = 0;
                        }

                        $routeHotelModel->check_in_night_amount = $newRoomNight;
                        $routeHotelModel->update();
                    }
                //旧ID与新ID不同，则加上旧酒店晚数，减去新酒店晚数
                }else if(!empty($oldHotelRoomId) && $oldHotelRoomId != $newHotelRoomId){
                    $routeInfo = RouteModel::get(['id'=>$routeId]);
                    $routeOldRoomModel = RouteHotelRoomModel::get(['route_id'=>$routeId,'hotel_id'=>$oldHotelRoomId,'room_id'=>$oldHotelRoomId]);

                    $routeNewRoomModel = RouteHotelRoomModel::get(['route_id'=>$routeId,'hotel_id'=>$newHotelRoomId,'room_id'=>$newHotelRoomId]);

                    if(!empty($routeOldRoomModel) && !empty($routeNewRoomModel) && !empty($routeInfo)){
                        $packageNight = substr($routeInfo->package_name,2,1);

                        $newRoomNight = $routeNewRoomModel->check_in_night_amount - 1;

                        if($newRoomNight < 0){
                            $newRoomNight = 0;
                        }

                        $routeNewRoomModel->check_in_night_amount = $newRoomNight;
                        $routeNewRoomModel->update();

                        $oldRoomNight = $routeNewRoomModel->check_in_night_amount + 1;

                        if($oldRoomNight > $packageNight){
                            $oldRoomNight = $packageNight;
                        }

                        $routeOldRoomModel->check_in_night_amount = $oldRoomNight;
                        $routeOldRoomModel->update();

                    }
                }
            }else{
                $start = false;
            }
            $model = new RouteDescriptionHotelModel();
        }

        return $start;
    }



    /**
     * @name 修改描述活动
     * @param $data 描述活动数据
     * @param $model 描述活动模型
     * @param $descId 描述ID
     * @param $routeId 线路ID
     * @return bool|true
     */
    public function updateDescActivity($data,$model,$descId,$routeId)
    {
        if(empty($data) || empty($descId) || empty($model) || empty($routeId)){
            return false;
        }

        $start = true;
        foreach($data as $k=>$v){

            if(!empty($v['id'])){
                $model = $model->where('id',$v['id'])->find();
            }else{
                $model = new RouteDescriptionActivityModel();
            }

            if(empty($model)){
                $model = new RouteDescriptionActivityModel();
            }

            $model->description_id = $descId;
            $model->activity_id = $v['activity_id'];
            $model->route_activity_id = $v['route_activity_id'];
            $model->activity_name = $v['activity_name'];
            $model->activity_image_status = $v['activity_image_status'];
            $model->image_uniqid = $v['image_uniqid'];
            $model->activity_description = $v['activity_description'];

            if(!empty($v['id'])){
                $result = $model->save();
            }else{
                $result = $model->save();
            }

            if(!empty($result)){
                $routeActivityModel = RouteActivityModel::get(['id'=>$v['route_activity_id']]);

                if(!empty($routeActivityModel)){
                    $routeActivityModel->is_show = 0;
                    $routeActivityModel->update();
                }

            }else{
                $start = false;
            }

            $model = new RouteDescriptionActivityModel();
        }

        return $start;
    }



    /**
     * @name 通过酒店ID获得图片列表
     * @param integer $hotelId 酒店ID
     * @return array|bool|mixed
     */
    public function getHotelImage($hotelId='')
    {
        if(empty($hotelId)){
            $hotelId = $this->hotelId;
        }

        if(empty($hotelId)){
            return [];
        }

        $hotelModel = new HotelModel();

        $hotelInfo = $this->formateData($hotelModel->where('id',$hotelId)->find());

        if(empty($hotelInfo)){
            return [];
        }

        $imageModel = new ImageModel();

        $imageInfo = $this->formateData($imageModel->field('id,image_uniqid,image_name,image_category,image_path')->where('image_uniqid',$hotelInfo['image_uniqid'])->select());

        if(!empty($imageInfo)){
            return $imageInfo;
        }

        return [];
    }

    /**
     * @name 获得活动图片
     * @return array|bool|mixed
     */
    public function getActivityImage()
    {
        if(empty($this->activityId)){
            return [];
        }

        $activityModel = new HotelFacilityModel();

        $activityInfo = $this->formateData($activityModel->where('id',$this->activityId)->find());

        if(empty($activityInfo)){
            return [];
        }

        $imageModel = new ImageModel();

        $imageInfo = $this->formateData($imageModel->field('id,image_uniqid,image_name,image_category,image_path')->where('image_uniqid',$activityInfo['image_uniqid'])->select());

        if(!empty($imageInfo)){
            return $imageInfo;
        }

        return [];
    }



    /**
     * @name 检验数据是否符合数据表规则
     * @auth Sam
     * @access public
     * @param $data 被检验的数据
     * @param $rule 检验的规则
     * @param $number 层数 1=单层验证 2=循环验证
     * @return array|bool|string
     */
    public function checkMysqlData($data,$rule,$number)
    {

        if(empty($rule) || !is_array($rule)){
            return '数据不完整';
        }

        $validate = new Validate($rule);

        if($number == 1){
            if($validate->check($data)){
                return true;
            }
                return $validate->getError();
        }

        if($number > 1 && is_numeric($number)){
            foreach($data as $k=>$v){
                if(!$validate->check($data[$k])){
                    return $validate->getError();
                }

                return true;
            }
        }

        return '数据不完整2';
    }

    /**
     * @name 删除线路交通
     * @access public
     * @auth Sam
     * @return string
     */
    public function deleteDescVehicle()
    {
        if(empty($this->routeVehicleId)){
            return '删除失败';
        }

        $routeVehicleModel = RouteVehicleModel::get(['id'=>$this->routeVehicleId]);

        if(empty($routeVehicleModel)){
            return '交通不存在';
        }

        if($routeVehicleModel->delete()){
            return '删除成功';
        }

        return '删除失败';
    }


    /**
     * @name 获得线路所有信息
     * @access public
     * @auth Sam
     * @return array|string
     */
    public function getDescAllInfo()
    {
        if(empty($this->routeId)){
            return '线路不存在';
        }

        //获得线路信息
        $routeInfo = $this->formateData(RouteModel::get($this->routeId));

        if(!empty($routeInfo) && !empty($routeInfo['image_uniqid'])){
            $routeClass = new RouteController();
            $routeImage = $routeClass->getImageInfo($routeInfo['image_uniqid']);
            $routeInfo['route_image'] = $routeImage;
        }else{
            $routeInfo = !empty($routeInfo)?$routeInfo:array();
        }

        //获得描述信息
        $routeDescModel = new RouteDescriptionModel();
        $descInfo = $this->formateData($routeDescModel->where('route_id',$routeInfo['id'])->order('package_day asc')->select());

        if(!empty($descInfo)){
            $merge = array();
            foreach($descInfo as $k=>$v){
                //获得描述酒店信息
                $mergeHotelInfo = $this->formateData(RouteDescriptionHotelModel::all(['description_id'=>$v['id']]));
                //获得图片信息
                if(!empty($mergeHotelInfo)){
                    $merge[$k]['hotel_info'] = $this->foreachGetImage($mergeHotelInfo);
                }else{
                    $merge[$k]['hotel_info'] = array();
                }

                //获得描述交通信息
                $mergeVehicleInfo = $this->formateData(RouteDescriptionVehicleModel::all(['description_id'=>$v['id']]));
                //添加到合并数组中
                if(!empty($mergeVehicleInfo)){
                    $merge[$k]['vehicle_info'] = $mergeVehicleInfo;
                }else{
                    $merge[$k]['vehicle_info'] = array();
                }

                //获得描述其他信息
                $mergeOtherInfo = $this->formateData(RouteDescriptionOtherInfoModel::all(['description_id'=>$v['id']]));
                //获得图片信息
                if(!empty($mergeOtherInfo)){
                    $merge[$k]['other_info'] = $this->foreachGetImage($mergeOtherInfo);
                }else{
                    $merge[$k]['other_info'] = array();
                }

                //获得描述活动信息
                $mergeActivityInfo = $this->formateData(RouteDescriptionActivityModel::all(['description_id'=>$v['id']]));

                if(!empty($mergeActivityInfo)){
                    $merge[$k]['activity_info'] = $this->foreachGetImage($mergeActivityInfo);
                }else{
                    $merge[$k]['activity_info'] = array();
                }

            }
        }else{

            $routeInfo['route_image'] = array();
            $descInfo = array();
            $merge = array();

            $returnArray['route_info'] = $routeInfo;
            $returnArray['desc_info'] = $descInfo;
            $returnArray['note_info'] = array();
            return $returnArray;
        }

        $noteModel = new RoutePurchaseNotesModel();
        $noteInfo = $this->formateData($noteModel->where('route_id',$this->routeId)->find());

        if(!empty($noteInfo)){
            $returnArray['note_info'] = $noteInfo;
        }else{
            $returnArray['note_info'] = array();
        }

        $returnArray['route_info'] = $routeInfo;
        $returnArray['desc_info'] = $descInfo;
        $returnArray = array_merge($returnArray,$merge);

        return $returnArray;

    }

    /**
     * @name 获得线路所有信息2
     * @access public
     * @auth Sam
     * @return array|string
     */
    public function getDescAllInfo2()
    {
        if(empty($this->routeId)){
            return '线路不存在';
        }

        //获得线路信息
        $routeModel = new RouteModel();
        $routeInfo = $this->formateData($routeModel->where('id',$this->routeId)->find());

        if(!empty($routeInfo) && !empty($routeInfo['image_uniqid'])){
            $routeController = new RouteController();
            $routeImage = $routeController->getImageInfo($routeInfo['image_uniqid']);
            $routeInfo['route_image'] = $routeImage;
        }else{
            $routeInfo = !empty($routeInfo)?$routeInfo:array();
        }

        //获得描述信息
        $routeDescModel = new RouteDescriptionModel();
        $descInfo = $this->formateData($routeDescModel->where('route_id',$routeInfo['id'])->order('package_day asc')->select());

        if(!empty($descInfo)){
            $merge = array();
            foreach($descInfo as $k=>$v){
                //获得描述酒店信息
                $mergeHotelInfo = $this->formateData(RouteDescriptionHotelModel::all(['description_id'=>$v['id']]));
                //获得图片信息
                if(!empty($mergeHotelInfo)){
                    $merge[$k]['hotel_info'] = $this->foreachGetImage($mergeHotelInfo);

                }

                //获得描述交通信息
                $mergeVehicleInfo = $this->formateData(RouteDescriptionVehicleModel::all(['description_id'=>$v['id']]));
                //添加到合并数组中
                if(!empty($mergeVehicleInfo)){
                    $merge[$k]['vehicle_info'] = $mergeVehicleInfo;
                }else{
                    $merge[$k]['vehicle_info'] = array();
                }

                //获得描述其他信息
                $mergeOtherInfo = $this->formateData(RouteDescriptionOtherInfoModel::all(['description_id'=>$v['id']]));
                //获得图片信息
                if(!empty($mergeOtherInfo)){
                    $merge[$k]['other_info'] = $this->foreachGetImage($mergeOtherInfo);
                }else{
                    $merge[$k]['other_info'] = array();
                }

                //获得描述活动信息
                $mergeActivityInfo = $this->formateData(RouteDescriptionActivityModel::all(['description_id'=>$v['id']]));

                if(!empty($mergeActivityInfo)){
                    $merge[$k]['activity_info'] = $this->foreachGetImage($mergeActivityInfo);
                }else{
                    $merge[$k]['activity_info'] = array();
                }

            }
        }else{
            $routeInfo['route_image'] = array();
            $descInfo = array();
            $merge = array();

            $returnArray['route_info'] = $routeInfo;
            $returnArray['desc_info'] = $descInfo;
            $returnArray['note_info'] = array();
            return $returnArray;

        }

        return $this->formateData($merge);
    }

    public function foreachGetImage2($data)
    {

        if(empty($data) || !is_array($data) || empty($data['image_uniqid'])){
            $data['image_info'] = array();

            return $data;
        }

        if(!empty($data['image_uniqid'])){
            $imageInfo = controller('RouteController')->getImageInfo($data['image_uniqid']);
        }

        if(empty($imageInfo)){
            $imageInfo = array();
        }

        $data['image_info'] = $imageInfo;
        $imageInfo = array();

        return $data;
    }


    /**
     * @name 遍历获得数据图片
     * @param $data
     * @return array
     */
    public function foreachGetImage($data)
    {

        if(empty($data) || !is_array($data)){
            $data['image_info'] = array();
            return $data;
        }

        foreach($data as $k=>$v){
            if(!empty($v['image_uniqid'])){
                $routeClass = new RouteController();
                $imageInfo = $routeClass->getImageInfo($v['image_uniqid']);
            }

            if(empty($imageInfo)){
                $imageInfo = array();
            }

            $data[$k]['image_info'] = $imageInfo;
            $imageInfo = array();
        }

        return $data;
    }

    /**
     * @name 删除描述活动
     * @auth Sam
     * @return string
     */
    public function deleteDescActivity()
    {
        if(empty($this->descActivityId)){
            return '请输入描述活动';
        }

        $descActivityModel = new RouteDescriptionActivityModel();

        $descActivityModel = $descActivityModel->where('id',$this->descActivityId)->find();

        if(empty($descActivityModel)){
            return '订单不存在';
        }

        if($descActivityModel->delete()){
            return '删除描述活动成功';
        }

        return '删除描述活动失败';
    }

    /**
     * @name 删除描述交通
     * @auth Sam
     * @return string
     */
    public function deleteDescriptionVehicle()
    {
        if(empty($this->descVehicleId)){
            return '描述交通不存在';
        }

        $descVehicleModel = RouteDescriptionVehicleModel::get($this->descVehicleId);

        if(empty($descVehicleModel)){
            return '描述交通不存在';
        }

        if($descVehicleModel->delete()){
            return '删除成功';
        }

        return '删除失败';
    }

    /**
     * @name 删除描述其他信息
     * @auth Sam
     * @return string
     */
    public function deleteDescOtherInfo()
    {
        if(empty($this->descOtherInfoId)){
            return '其他信息不存在';
        }

        $descOtherInfo = RouteDescriptionOtherInfoModel::get($this->descOtherInfoId);

        if(empty($descOtherInfo)){
            return '其他信息不存在';
        }

        if($descOtherInfo->delete()){
            return '删除成功';
        }

        return '删除失败';

    }


}

?>