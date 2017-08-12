<?php
namespace app\route\controller;
use app\ims\model\ContractPackageModel;
use app\ims\model\HotelModel;
use app\ims\model\HotelRoomModel;
use app\ims\model\VehicleCityModel;
use app\route\model\RouteModel;
use think\Controller;
use think\Validate;
use app\ims\model\PlaceModel;
use app\ims\model\CountryModel;
use app\route\model\RouteHotelRoomModel;
use app\ims\model\HotelFacilityModel;
use app\route\model\RouteActivityModel;
use app\ims\model\VehicleModel;
use app\ims\model\VehicleBaseModel;
use think\Db;
use app\route\model\RouteVehicleModel;



class RouteDetailController extends Controller
{
    /**
     * @name 获得线路酒店信息
     * @auth Sam
     * @access public
     * @param integer $place_id 海岛ID
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getHotelList()
    {
        $request = $this->request;
        $placeId = $request->param('place_id',0);

        if(empty($placeId)){
            return '没有海岛信息';
        }

        $hotelModel = new HotelModel();
        $hotelList = $hotelModel->field('id,place_id,country_id,hotel_name,hotel_ename')->where('place_id',$placeId)->select();

        //如果是对象，则转为数组
        if(!is_object($hotelList) && !empty($hotelList)){
            $hotelList = json_decode(json_encode($hotelList),true);
        }

        if(empty($hotelList)){
            return '没有信息';
        }

        $hotelRoomModel = new HotelRoomModel();
        $hotelRoomList = array();
        $contractPackModel = new ContractPackageModel();
        $conPackInfo = array();

        foreach($hotelList as $k=>$v){
            if(!empty($v['id'])){
                $hotelRoomList = $hotelRoomModel->field('id,hotel_id,room_name,room_ename')->where('hotel_id',$v['id'])->select();

                if(!empty($hotelRoomList) && !is_object($hotelRoomList)){
                    $hotelRoomList = $this->formateData($hotelRoomList);

                    if(!empty($hotelRoomList)){
                        foreach($hotelRoomList as $m=>$n){
                            $conPackInfo = $this->formateData($contractPackModel
                                ->field('ims_contract_package.id as package_id,ims_contract_package.hotel_id,ims_contract_package.contract_id,
                                         ims_contract_package.season_unqid,ims_contract_package.package_unqid,
                                         ims_contract_package.package_name,ims_contract_package.package_type, 
                                         ims_contract_room.contract_id,ims_contract_room.season_unqid,
                                         ims_contract_room.package_unqid,ims_contract_room.room_id ')
                                ->where("hotel_id = ".$n['hotel_id']." AND package_type = '标准成人' AND room_id = ".$n['id'])
                                ->group('package_name')
                                ->join('ims_contract_room',"ims_contract_package.season_unqid = ims_contract_room.season_unqid AND 
                                        ims_contract_package.package_unqid = ims_contract_room.package_unqid")->select());

                            $status = $this->verdictRoomStatus($conPackInfo);

                            $hotelRoomList[$m]['route_room_status'] = $status;
                            $status = 0;
                            $conPackInfo = array();
                        }
                    }
                    $hotelList[$k]['room_list'] = $hotelRoomList;
                }
            }
        }
        return $hotelList;
    }

    /**
     * @name 修改线路酒店信息
     * @auth Sam
     * @access public
     * @param array $room_info 房型信息
     * @return array|string
     */
    public function routeAddHotelRoom()
    {
        $request = $this->request;
        $roomInfo = $request->param('room_info/a',array());
        $routeId = $roomInfo['route_id'];

        if(empty($roomInfo) || empty($routeId)){
            return '没有房型信息';
        }

        $routeRoomModel = new RouteHotelRoomModel();
        $validateController = new Validate($routeRoomModel->rule);

        if(!$validateController->check($roomInfo)){
            return $validateController->getError();
        }

        $nightAmount = $roomInfo['check_in_night_amount'];

        if(!$this->isExceedPackageRoomAmount($routeId,$nightAmount)){
            return '超出套餐晚数';
        }

        if($routeRoomModel->save($roomInfo)){
            return '修改成功';
        }

        return '修改失败';

    }

    /**
     * @name 获得房间设施列表
     * @auth Sam
     * @access public
     * @param integer $hotelId 酒店ID
     * @return bool|mixed|string
     */
    public function getRoomActivityList()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_ids','');
        $hotelId = trim($hotelId,',');

        if(empty($hotelId)){
            return '没有信息';
        }

        $activityModel = new HotelFacilityModel();

        $activityInfo = $this->formateData($activityModel->field('id,hotel_id,activity_name,activity_ename,activity_type,is_charge,charge_mode,pricing_mode,standard_passengers,minimum_passengers,max_passengers')->where("hotel_id in ('$hotelId') AND activity_type = '活动'")->group('activity_name')->select());
//halt($activityInfo);
        if(empty($activityInfo)){
            return [];
        }

        foreach($activityInfo as $k=>$v){

            $activityStatus = $this->isPackageActivity($activityInfo[$k]);

            $activityInfo[$k]['activity_status'] = $activityStatus;
        }
        return $activityInfo;
    }

    /**
     * @name 判断设施是否存在于套餐中
     * @auth Sam
     * @access public
     * @param array $activityArray 设施一维数组
     * @return integer
     */
    public function isPackageActivity($activityArray)
    {
        if(empty($activityArray)){
            return 0;
        }

        $activityModel = new ContractPackageModel();

        $activityInfo = $this->formateData($activityModel
            ->field('id,hotel_id,contract_id,season_unqid,package_unqid,package_name,package_type,include_activity,include_facility')
            ->where("include_activity like '%".$activityArray['activity_name']."%'")->select());
//halt($activityInfo);
        if(empty($activityInfo)){
            return 0;
        }

        $one = 0;
        $two = 0;
        $three = 0;

        foreach($activityInfo as $k=>$v){
            if($v['package_name'] !== '基础套餐'){
                $one = 1;
            }

            if($v['package_name'] == '基础套餐'){
                $two = 2;
            }

        }

        return $three = $one + $two;
    }


    /**
     * @name 套餐酒店入住晚数是否超过新增入住的晚数
     * @auth Sam
     * @access public
     * @param integer $routeId 线路ID
     * @param integer $nightAmount 新增入住的晚数
     * @return bool
     */
    public function isExceedPackageRoomAmount($routeId,$nightAmount)
    {
        if(empty($routeId) || empty($nightAmount)){
            return false;
        }

        $routeModel = new RouteModel();
        $routeInfo = $this->formateData($routeModel->where('id',$routeId)->find());

        if(empty($routeInfo)){
            return false;
        }

        //获得套餐晚数
        $night = substr($routeInfo['package_name'],2,1);

        if(!is_numeric($night)){
            $night = substr($routeInfo['package_name'],3,1);
        }

        if(!is_numeric($night)){
            return false;
        }

        //获得数据库中已经有的晚数
        $routeRoomModel = new RouteHotelRoomModel();
        $routeRoomInfo = $this->formateData($routeRoomModel->where('route_id',$routeId)->select());

        $allNightAmount = 0;
        if(!empty($routeRoomInfo)){
            foreach($routeRoomInfo as $k=>$v){
                $allNightAmount += $v['check_in_night_amount'];
            }
        }

        $nightAmount = $nightAmount + $allNightAmount;

        //判断新增的晚数是否大于数据库中的晚数
        if($night >= $nightAmount){
            return true;
        }

        return false;
    }



    /**
     * @name 通过查询出来的数据判断 套餐状态
     * @auth Sam
     * @access public
     * @param $packageArray
     * @return bool|array
     */
    public function verdictRoomStatus($packageArray)
    {
        if(empty($packageArray)){
            return 0;
        }

        $one = 0;
        $two = 0;
        $three = 0;

        foreach($packageArray as $k=>$v){
            if(!empty($v['package_name']) && $v['package_name'] !== '基础套餐'){
                $one =  1;
            }

            if(!empty($v['package_name']) && $v['package_name'] == '基础套餐'){
                $two = 2;
            }
        }

        return $three = $one + $two;
    }


    /**
     * @name object对象转化为数组
     * @auth Sam
     * @access public
     * @param $data
     * @return bool|mixed
     */

    public function formateData($data)
    {
        if(empty($data)){
            return false;
        }

            return json_decode(json_encode($data),true);
    }

    /**
     * @name 添加修改房型信息
     * @auth Sam
     * @access public
     * @param  array $room_info 房型信息
     * @return array|string
     */
    public function updateRouteRoomInfo()
    {
        $request = $this->request;
        $roomInfo = $request->param('room_info/a',array());

        if(empty($roomInfo) || !is_array($roomInfo)){
            return '没有房型信息';
        }

        $routeRoomModel = new RouteHotelRoomModel();

        $validateController = new Validate($routeRoomModel->rule);

        if(!$validateController->check($roomInfo)){
            return $validateController->getError();
        }

        $returnRes = $routeRoomModel->save($roomInfo);
        if($returnRes){
            return $routeRoomModel->id;
        }

        return '修改失败';

    }


    /**
     * @name 修改线路活动
     * @auth Sam
     * @access public
     * @param array $activity_info 活动信息
     * @return string
     */
    public function updateRouteActivityInfo()
    {
        $request = $this->request;
        $activityInfo = $request->param('activity_info/a',array());

        if(empty($activityInfo) || !is_array($activityInfo)){
            return '没有活动信息';
        }

        $routeActivityModel = new RouteActivityModel();

        if(!empty($activityInfo['id'])){
            $returnRes = $routeActivityModel->update($activityInfo);
        }else{
            $returnRes = $routeActivityModel->save($activityInfo);
        }

        if($returnRes){
            return $routeActivityModel->id;
        }

        return '修改失败';
    }

    /**
     * @name 获得线路所有信息
     * @auth Sam
     * @access public
     * @param integer $routeId 线路ID
     * @return string
     */
     public function getRouteList()
     {
        $request = $this->request;
        $routeId = $request->param('route_id',0);

        if(empty($routeId)){
            return '线路不存在';
        }

        $routeModel = new RouteModel();

        $routeInfo = $this->formateData($routeModel->field('id as route_id,route_name,destination_place_describe,destination_place_id,country_name,route_type,max_passengers,min_passengers,package_name,route_code')->where('id',$routeId)->select());

        if(empty($routeInfo)){
            return '线路不存在2';
        }

        $routeRoomModel = new RouteHotelRoomModel();

        //房型信息
        $routeRoomInfo = array();
        $routeRoomInfo = $this->formateData($routeRoomModel->where('route_id',$routeId)->select());

        $routeActivityModel = new RouteActivityModel();

        //房型设施信息
        $routeActivityInfo = array();
        $routeActivityInfo = $this->formateData($routeActivityModel->where('route_id',$routeId)->select());

        $vehicleModel = new RouteVehicleModel();

        //交通信息
        $routeVehicleInfo = array();
        $routeVehicleInfo = $this->formateData($vehicleModel->where('route_id',$routeId)->select());

        $returnArray['route_info'] = $routeInfo;
        $returnArray['route_room_info'] = $routeRoomInfo;
        $returnArray['route_activity_info'] = $routeActivityInfo;
        $returnArray['route_vehicle_info'] = $routeVehicleInfo;

        return $returnArray;

     }

    /**
     * @name 通过酒店ID获得交通列表
     * @auth Sam
     * @access public
     * @param mixed $hotelList 酒店ID列表
     * @return string
     */
    public function getVehicleList()
    {
        $request = $this->request;
        $hotelIist = $request->param('hotel_list','');
        $vehicleType = $request->param('vehicle_type','去程');
        $hotelIist = trim($hotelIist,',');

        if(empty($hotelIist)){
            return '酒店不存在';
        }

        //往返交通列表
        $goBackVehicleList = array();
        $goBackVehicleList = $this->goBackVehicleList($hotelIist,$vehicleType);
//        halt($goBackVehicleList);
        $goBackVehicleList = $this->checkVehiclePackage($goBackVehicleList);
//        halt($goBackVehicleList);

        //目的地联程交通列表
        $connectVehicleList = array();
        $connectVehicleList = $this->connectVehicleList($hotelIist,$vehicleType);
//        halt($connectVehicleList);
        $connectVehicleList = $this->checkVehiclePackage($connectVehicleList);
//        halt($connectVehicleList);

        //目的地节点
        $nodeList = array();
        $nodeList = $this->getNodeList($hotelIist,$vehicleType);
        $nodeList = $this->checkVehiclePackage($nodeList);

        $return['go_back_vehicle_list'] = $goBackVehicleList;
        $return['connect_vehicle_list'] = $connectVehicleList;
        $return['node_list'] = $nodeList;

        return $return;


    }

    /**
     * @name 目的地往返交通列表
     * @auth Sam
     * @access public
     * @param string $hotelList 酒店字符串列表
     * @return bool|mixed
     */
    public function goBackVehicleList($hotelList,$vehicleType)
    {
        if(empty($hotelList) || !is_string($hotelList)){
            return array();
        }

        $vehicleCityModel = new VehicleCityModel();

        $cityList = Db::query("SELECT id as vehicle_city_id,hotel_id,hotel_city_id,vehicle_base_id,city_journey_type,city_departure_week,city_passengers_range FROM ims_new.ims_vehicle_city where hotel_id in($hotelList) AND city_journey_type = '$vehicleType'");

        if(empty($cityList)){
            return array();
        }

        $vehicleModel = new VehicleModel();

        foreach($cityList  as $k=>$v){
            $vehicleList = array();

            $vehicleList = $vehicleModel->field('departure_place_name,destination_name')->where("vehicle_base_id in($v[vehicle_base_id])")->select();

            foreach($vehicleList as $m=>$n){

                if($m === 0){
                   $return[] = $n->departure_place_name;
                }
                $return[] = $n->destination_name;

            }

            if(empty($return)){
                $return = [];
            }

                $cityList[$k]['vehicle_path'] = $return;
                $return = array();
        }


        return $cityList;
    }

    /**
     * @name 获得交通节点
     * @auth Sam
     * @access public
     * @param $hotelList
     * @return bool|mixed
     */
    public function getNodeList($hotelList,$vehicleType)
    {
        if(empty($hotelList) || !is_string($hotelList)){
            return false;
        }

        $vehicleList = Db::query("select id as vehicle_id,hotel_id,vehicle_name,vehicle_type,vehicle_category,single_journey_type,departure_place_name,destination_name from ims_new.ims_vehicle where hotel_id in ($hotelList) AND vehicle_category = '单程交通'AND single_journey_type = '$vehicleType'");

        return $vehicleList;

    }

    /**
     * @name 获得目的地联程交通列表
     * @access public
     * @auth Sam
     * @param $hotelList
     * @return bool
     */
    public function connectVehicleList($hotelList,$vehicleType)
    {
        if(empty($hotelList) || !is_string($hotelList)){
            return false;
        }


        $cityList = Db::query("select ims_new.ims_vehicle.id as vehicle_id,vehicle_base_id,ims_new.ims_vehicle.hotel_id,vehicle_name,vehicle_type,ims_new.ims_vehicle.vehicle_category,single_journey_type,departure_place_name,destination_name,connect_journey_type 
from ims_new.ims_vehicle join ims_new.ims_vehicle_base on vehicle_base_id = ims_new.ims_vehicle_base.id where ims_new.ims_vehicle.hotel_id IN ($hotelList) AND ims_vehicle_base.vehicle_category = '联程交通' AND connect_journey_type = '$vehicleType' group by ims_new.ims_vehicle.vehicle_base_id  ");
//halt($cityList);
        if(!empty($cityList)){
            $return = array();
            foreach($cityList as $k=>$v){
                $vehicleList = Db::query("select * from ims_new.ims_vehicle where vehicle_base_id = ".$v['vehicle_base_id']);

                $return[$k]['vehicle_category'] = $v['vehicle_category'];
                $return[$k]['connect_journey_type'] = $v['connect_journey_type'];
                $return[$k]['vehicle_id'] = $v['vehicle_base_id'];
                $return[$k]['hotel_id'] = $v['hotel_id'];
                $return[$k]['vehicle_name'] = $v['vehicle_name'];
                $return[$k]['vehicle_type'] = $v['vehicle_type'];

                foreach($vehicleList as $m=>$n){
                    if($m === 0){
                        $return[$k]['vehicle_path'][] = $n['departure_place_name'];
                    }else{
                        $return[$k]['vehicle_path'][] = $n['departure_place_name'];
                        $return[$k]['vehicle_path'][] = $n['destination_name'];
                    }
                }
            }
            return $return;
        }
//halt($return);

        return $cityList;
    }

    /**
     * @name 判断交通是否在套餐内
     * @auth Sam
     * @param mixed $array 交通数组
     * @return array
     */
    public function checkVehiclePackage($array)
    {
        if(empty($array) || !is_array($array)){
            return array();
        }

        $packageModel = new ContractPackageModel();
        $packageList = array();
        $checkId = '';

        foreach($array as $k=>$v){
            $packageString = $this->chooseGoOrBack($v);
            if(!empty($v['vehicle_base_id'])){
                $checkId = $v['vehicle_base_id'];
            }else{
                $checkId = $v['vehicle_id'];
            }

            $packageList = $this->formateData($packageModel->field('package_name,include_go_vehicle')->where("$packageString like '%$checkId%'")->select());

            $packStatus = $this->getVehiclePackageStatus($packageList);
            $array[$k]['package_status'] = $packStatus;

        }

        return $array;
    }

    /**
     * @name 选择使用去程还是返程
     * @param $array
     * @return string
     */
    public function chooseGoOrBack($array)
    {
        if(!empty($array['single_journey_type']) && $array['single_journey_type'] == '去程'){
            return 'include_go_vehicle';
        }

        if(!empty($array['connect_journey_type']) && $array['connect_journey_type'] == '去程'){
            return 'include_go_vehicle';
        }

        if(!empty($array['city_journey_type']) && $array['city_journey_type'] == '去程'){
            return 'include_go_vehicle';
        }

        if(!empty($array['single_journey_type']) && $array['single_journey_type'] == '返程'){
            return 'include_back_vehicle';
        }

        if(!empty($array['connect_journey_type']) && $array['connect_journey_type'] == '返程'){
            return 'include_back_vehicle';
        }

        if(!empty($array['city_journey_type']) && $array['city_journey_type'] == '返程'){
            return 'include_back_vehicle';
        }

        return 'include_go_vehicle';
    }

    /**
     * @name 通过套餐数据获得交通状态
     * @auth Sam
     * @access public
     * @return integer
     */
    public function getVehiclePackageStatus($array)
    {
        if(empty($array) || is_array($array)){
            return 0;
        }

        $one = 0;
        $two = 0;
        $three = 0;

        foreach($array as $k=>$v){
            if($v['package_name'] !== '基础套餐'){
                $one = 1;
            }

            if($v['package_name'] == '基础套餐'){
                $two = 2;
            }
        }

        return $one+$two;
    }

    /**
     * @name 修改交通信息
     * @auth Sam
     * @access public
     * @param mixed $vehicleInfo 交通信息
     * @return mixed
     */
    public function updateVehicleInfo()
    {
        $request = $this->request;
        $vehicleInfo = $request->param('vehicle_info/a',array());

        if(empty($vehicleInfo) || !is_array($vehicleInfo)){
            return '请输入数据';
        }

        $routeVehicleModel = new RouteVehicleModel();
        $validateController = new Validate($routeVehicleModel->rule);

        if(!$validateController->check($vehicleInfo)){
            return $validateController->getError();
        }

        if($routeVehicleModel->save($vehicleInfo)){
            return $routeVehicleModel->id;
        }

        return '修改失败';
    }

    /**
     * @name 删除线路酒店房型
     * @access public
     * @auth Sam
     * @return string
     */
    public function deleteRouteHotelRoom()
    {
        $request = $this->request;
        $routeHotelRoomId = $request->param('route_hotel_room_id',0);

        if(empty($routeHotelRoomId)){
            return '删除失败';
        }

        $routeRoomInfo = RouteHotelRoomModel::get($routeHotelRoomId);

        if(empty($routeRoomInfo)){
            return '酒店不存在';
        }

        if($routeRoomInfo->delete()){
            return '删除成功';
        }

        return '删除失败2';

    }

    /**
     * @name 删除线路活动
     * @access public
     * @auth Sam
     * @return string
     */
    public function deleteRouteActivity()
    {
        $request = $this->request;
        $routeActivityId = $request->param('route_activity_id',0);

        if(empty($routeActivityId)){
            return '删除失败';
        }

        $activityInfo = RouteActivityModel::get($routeActivityId);

        if(empty($activityInfo)){
            return '活动不存在';
        }

        if($activityInfo->delete()){
            return '删除成功';
        }

        return '删除失败2';
    }






}





?>