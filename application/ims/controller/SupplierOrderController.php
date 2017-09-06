<?php
namespace app\ims\controller;

use app\ims\model\BaseModel;
use app\ims\model\ContractModel;
use app\ims\model\ContractPackageModel;
use app\ims\model\ContractRoomModel;
use app\ims\model\ContractSeasonModel;
use app\ims\model\CountryModel;
use app\ims\model\HotelRoomModel;
use app\ims\model\PlaceModel;
use app\ims\model\SupplierAccountModel;
use app\ims\model\SupplierAccountDataModel;
use app\ims\model\SupplierGradeModel;
use app\ims\model\SupplierOrderModel;
use app\ims\model\SupplierOrderTripModel;
use app\ims\model\SupplierOrderPassport;
use app\index\controller\Order;
use app\index\model\Contract;
use app\index\model\Hotel;
use app\index\model\HotelRoom;
use app\ims\model\HotelModel;
use app\ims\model\ExchangeModel;
use app\ims\controller\VehicleBasicController;
use app\ims\controller\SupplierOrderTripController;
use app\ims\model\ImageModel;
use app\ims\model\RoomModel;
use app\ims\model\HotelDefaultVehicleModel;

use app\ims\model\HotelFacilityModel;


class SupplierOrderController extends BaseController
{
    //获得套餐信息
    public function getPackInfo()
    {
        $request = $this->request;
        $packageUniqid = $request->param('package_uniqid','');
        $packType = $request->param('package_type','标准成人');

        if(empty($packageUniqid) || empty($packType)){
            return getErr('没有套餐信息');
        }

        $packModel = new ContractPackageModel();

        $packInfo = $this->formateData($packModel->where(['package_unqid'=>$packageUniqid,'package_type'=>$packType])->find());

        if(!empty($packInfo)){
            $hotelModel = new HotelModel();
            $placeModel = new PlaceModel();

            $hotelInfo = $hotelModel->where('id',$packInfo['hotel_id'])->find();
            $placeInfo = $placeModel->where('id',$hotelInfo['place_id'])->find();

            $defaVehicleModel = new HotelDefaultVehicleModel();

            $defaVehicleInfo = $this->formateData($defaVehicleModel->where('hotel_id',$packInfo['hotel_id'])->find());

            if(!empty($defaVehicleInfo)){
                $packInfo['vehicle_info'] = $defaVehicleInfo;
            }else{
                $packInfo['vehicle_info'] = array();
            }

            $packInfo['hotel_name'] = $hotelInfo['hotel_name'];
            $packInfo['place_name'] = $placeInfo['place_name'];

            return getSucc($packInfo);
        }

        return getErr('没有该套餐信息');
    }

    public function getRoomList()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);

        if(empty($hotelId)){
            return getErr('没有房型信息');
        }

        $hotelRoomModel = new HotelRoomModel();

        $roomList = $this->formateData($hotelRoomModel->field('ims_hotel_room.id as room_id,hotel_id,room_name,room_ename,standard_adult,extra_adult,extra_child,extra_logic,ims_hotel_room.image_uniqid')->where(['hotel_id'=>$hotelId])->select());
//        halt($roomList);

        $newRoomList = array();
        if(empty($roomList)){
            return getErr('没有房型信息');
        }

        //查询酒店下的房型信息
        $imageModel = new ImageModel();
        $roomModel = new HotelRoomModel();
        $packageModel = new ContractPackageModel();
        $hotelModel = new HotelModel();

        foreach($roomList as $k=>$v){
            if(!empty($v['image_uniqid'])){
                $imageInfo = $this->formateData($imageModel->field('image_uniqid,image_path')->where('image_uniqid',$v['image_uniqid'])->find());
            }else{
                $imageInfo['image_uniqid'] = '';
                $imageInfo['image_path'] = '';
            }

            $roomInfo = $this->formateData($roomModel->field('room_amount')->where('id',$v['room_id'])->find());
            $packageInfo = $this->formateData($packageModel->where('hotel_id',$v['hotel_id'])->find());

            if(!empty($imageInfo)){
                $newRoomList[$k] = array_merge($v,$imageInfo);
                $newRoomList[$k]['room_amount'] = !empty($roomInfo['room_amount'])?$roomInfo['room_amount']:0;
            }else{
                $newRoomList[$k] = $v;
                $newRoomList[$k] = 0;
            }

            $newRoomList[$k]['ischoose'] = false;

            if(!empty($packageInfo['package_unqid'])){
                $newRoomList[$k]['package_uniqid'] = $packageInfo['package_unqid'];
            }else{
                $newRoomList[$k]['package_uniqid'] = '';
            }

            $hotelInfo = $hotelModel->field('exchange_id,exchange_rate')->where('ims_hotel.id',$v['hotel_id'])->join('ims_exchange','exchange_id = ims_exchange.id')->find();

            if(!empty($hotelInfo)){
                $newRoomList[$k]['exchange'] = $hotelInfo['exchange_rate'];
            }else{
                $newRoomList[$k]['exchange'] = 1;
            }


            $packageInfo = array();
            $imageInfo = array();
            $roomInfo = array();
        }

        if(empty($roomModel)){
            return getErr('没有房型信息');
        }

        //查询房型下的费用信息
        $strRoomList = '';

        foreach($newRoomList as $k=>$v){
            $strRoomList .= $v['room_id'].',';
        }

        $strRoomList = trim($strRoomList,',');

        //查询日期内可用的套餐
        $seasonModel = new ContractSeasonModel();

        $seasonList = $this->formateData($seasonModel->where("hotel_id = $hotelId ")->find());

        $returnArr = array();
        $nullArr['con_room_id'] = 0;
        $nullArr['contract_id'] = 0;
        $nullArr['season_unqid'] = '';
        $nullArr['pricing_mode'] = '';
        $nullArr['total_price'] = 0;
        $nullArr['adult_fare'] = 0;
        $nullArr['child_fare'] = 0;
        $nullArr['extra_child_fare'] = 0;
        $nullArr['room_price'] = 0;

        if(empty($newRoomList)){
            return getErr('没有酒店列表');
        }

        foreach($newRoomList as $k=>$v){
            $newRoomList[$k] = array_merge($newRoomList[$k],$nullArr);
        }

        return getSucc($newRoomList);


    }


    //创建订单
    public function addOrder()
    {
        $request = $this->request;
        $orderInfo = $request->param('order_info/a',array());

        if(empty($orderInfo) || !is_array($orderInfo)){
            return '请输入完整数据';
        }

        $suppOrderModel = new SupplierOrderModel();

        if(!empty($orderInfo['id'])){
            $result = $suppOrderModel->update($orderInfo);

            if(!empty($result)){
                return getSucc($orderInfo['id']);
            }
        }else{
            $result = $suppOrderModel->save($orderInfo);

            if($result){

                $orderId = $suppOrderModel->id;

                $orderTripModel = new SupplierOrderTripModel();

                $orderTripModel->order_id = $orderId;
                $orderTripModel->stop_to_pay = date('Y-m-d H:i:s',strtotime('+24 hours',time()));

                $orderTripModel->save();

                return getSucc($suppOrderModel->id);
            }
        }



        return getError('修改失败');
    }


    //创建订单(旧版本备份)
    public function addOrder2()
    {
        $request = $this->request;
        $orderId = $request->param('order_id',0);

        $orderName = $request->param('order_name','');
        $order = $request->param('order','');
        $packageUniqd = $request->param('package_unqid','');
        $contractId = $request->param('contract_id',0);
        $hotelId = $request->param('hotel_id',0);
        $roomId = $request->param('room_id',0);
        $startTime = $request->param('start_time','0000-00-00');
        $tripAdult = $request->param('trip_adult','');
        $tripChild = $request->param('trip_child','');
        $accountId = $request->param('account_id','');
        $adultPrice = $request->param('adult_price',0);
        $childPrice = $request->param('child_price',0);
        $endTime = $request->param('end_time','');
        $next = $request->param('next',0);


        if(empty($adultPrice) || $adultPrice <= 0){
            return getErr('成人价格不可用');
        }

        if(empty($childPrice) || $childPrice <= 0){
            return getErr('儿童价格不可用');
        }


        if(!empty($orderId)){
            $orderModel = SupplierOrderModel::get($orderId);
        }else{
            $orderModel = new SupplierOrderModel();
        }

        if(empty($endTime)){
            $day = substr($order,0,1);

            $one = strtotime('+'.$day.'day',strtotime($startTime));
// echo $one;exit;
            //结束日期
            $endTime = date('Y-m-d',$one);
        }


        //总价格
        $totalPrice = ($adultPrice * $tripAdult) + ($tripChild * $childPrice);

        $hotelRoomModel = new HotelRoomModel();
        $hotelInfo = $this->formateData($hotelRoomModel->where('hotel_id',$hotelId)->find());
        $adultRoom = !empty(ceil($tripAdult / $hotelInfo['standard_adult']))?ceil($tripAdult / $hotelInfo['standard_adult']):0;

        $orderModel->order_name = $orderName;
        $orderModel->package_unqid = $packageUniqd;
        $orderModel->contract_id = $contractId;
        $orderModel->hotel_id = $hotelId;
        $orderModel->room_id = $roomId;
        $orderModel->room_number = $adultRoom;
        $orderModel->start_time = $startTime;
        $orderModel->end_time = $endTime;
        $orderModel->trip_adult = $tripAdult;
        $orderModel->trip_child = $tripChild;
        $orderModel->adult_price = $adultPrice;
        $orderModel->child_price = $childPrice;
        $orderModel->account_id = $accountId;
        $orderModel->total_price = $totalPrice;
        $orderModel->use_package = $order;
        $orderModel->next = $next;
        $orderModel->is_delete = 0;


        if(!empty($orderModel->save())){
            if(!empty($orderId)){
                $tripModel = new SupplierOrderTripModel();
                $tripModel = $tripModel->where('order_id',$orderId)->find();
            }else{
                $tripModel = new SupplierOrderTripModel();
            }


            $tripModel->order_id = $orderModel->id;
            $tripModel->stop_to_pay = date('Y-m-d',strtotime('+1 day',time()));

            if($tripModel->save()){
                return getSucc($orderModel->id);
            }

            return getErr('修改失败');
        }else{
            return getErr('修改失败');
        }


    }



    //获得订单详细信息
    public function orderInfo()
    {
        $request = $this->request;
        $orderId = $request->param('order_id',0);

        if(empty($orderId)){
            return getError('订单不存在');
        }

        $suppOrderModel = new SupplierOrderModel();

        $orderInfo = $this->formateData($suppOrderModel->where('id',$orderId)->find());

        if(empty($orderInfo)){
            return getError('订单不存在2');
        }


        $accountId = $request->param('account_id',0);

        if(!empty($accountId)){
            $accountDataModel = new SupplierAccountDataModel();

            $accDataInfo = $this->formateData($accountDataModel->where('account_id',$accountId)->find());

            if(empty($accDataInfo)){
                return getErr('请登录系统');
            }

            $accountGradeModel = new SupplierGradeModel();
            $accountGradeInfo = $this->formateData($accountGradeModel->where('id',$accDataInfo['grade'])->find());

            if(!empty($accountGradeInfo['ratio'])){
                $ratio = $accountGradeInfo['ratio'];
            }else{
                $ratio = 1;
            }

            $orderInfo['ratio'] = $ratio;
        }


        $defaultVechidelModel = new HotelDefaultVehicleModel();

        $defVehicleInfo = $this->formateData($defaultVechidelModel->where('hotel_id',$orderInfo['hotel_id'])->find());
/*        var_dump($orderInfo);
halt($defVehicleInfo);*/
        if(empty($defVehicleInfo)){
            $defVehicleInfo = array();
        }

        if(empty($orderInfo['adult_go_vehicle'])){
            if(!empty($defVehicleInfo)){
                $orderInfo['adult_go_vehicle'] = $defVehicleInfo['default_go_vehicle'];
            }else{
                $orderInfo['adult_go_vehicle'] = '';
            }
        }

        if(empty($orderInfo['adult_back_vehicle'])){
            if(!empty($defVehicleInfo)){
                $orderInfo['adult_back_vehicle'] = $defVehicleInfo['default_back_vehicle'];
            }else{
                $orderInfo['adult_back_vehicle'] = '';
            }
        }

        if(empty($orderInfo['child_go_vehicle'])){
            if(!empty($defVehicleInfo)){
                $orderInfo['child_go_vehicle'] = $defVehicleInfo['default_go_vehicle'];
            }else{
                $orderInfo['child_go_vehicle'] = '';
            }

        }

        if(empty($orderInfo['child_back_vehicle'])){
            if($defVehicleInfo){
                $orderInfo['child_back_vehicle'] = $defVehicleInfo['default_back_vehicle'];
            }else{
                $orderInfo['child_back_vehicle'] = '';
            }

        }

        $activityModel = new HotelFacilityModel();

        $activityInfo = $this->formateData($activityModel->where(['hotel_id'=>$orderInfo['hotel_id'],'activity_type'=>'活动'])->select());

        if(!empty($activityInfo)){
            $orderInfo['activity_info'] = $activityInfo;
        }else{
            $orderInfo['activity_info'] = array();
        }


        return getSucc($orderInfo);
    }


    //获得订单详细信息（旧版本备份）
    public function orderInfo2()
    {
        $request = $this->request;
        $orderId = $request->param('order_id',0);

        if(empty($orderId)){
            return getErr('订单不存在');
        }

        $orderInfo = array();
        $tripController = new SupplierOrderTripController();
        $orderInfo = $tripController->getOrderInfo($orderId);
//$this->dumpExit($orderInfo);
        if(empty($orderInfo)){
            return getErr('订单已删除或订单不存在');
        }

        $packModel = new ContractPackageModel();

        $packInfo = $this->formateData($packModel->where(['package_type'=>'标准成人','package_unqid'=>$orderInfo['package_unqid']])->find());

        if(empty($packInfo)){
            return getErr('套餐信息不存在');
        }

        $hotelModel = new HotelModel();
        $hotelInfo = $hotelModel->field('id,country_id,place_id,hotel_name')->where('id',$orderInfo['hotel_id'])->find();

        $placeModel = new PlaceModel();
        $placeInfo = $placeModel->field('id,place_name,country_id')->where('id',$hotelInfo['place_id'])->find();

        $countryModel = new CountryModel();
        $countryInfo = $countryModel->where('id',$placeInfo['country_id'])->find();

//        $this->dumpExit($packInfo);

        $returnInfo['total_price'] = $orderInfo['total_price'];
        $returnInfo['next'] = $orderInfo['next'];
        $returnInfo['trip_adult'] = $orderInfo['trip_adult'];
        $returnInfo['trip_child'] = $orderInfo['trip_child'];
        $returnInfo['adult_price'] = $orderInfo['adult_price'];
        $returnInfo['child_price'] = $orderInfo['child_price'];
        $returnInfo['start_time'] = $orderInfo['start_time'];
        $returnInfo['include_go_vehicle'] = $packInfo['include_go_vehicle'];
        $returnInfo['include_back_vehicle'] = $packInfo['include_back_vehicle'];
        $returnInfo['include_activity'] = $packInfo['include_activity'];
        $returnInfo['include_facility'] = $packInfo['include_facility'];
        $returnInfo['hotel_id'] = $orderInfo['hotel_id'];
        $returnInfo['place_name'] = $placeInfo['place_name'];
        $returnInfo['hotel_name'] = $hotelInfo['hotel_name'];
        $returnInfo['room_id'] = $orderInfo['room_id'];
        $returnInfo['order_name'] = $orderInfo['order_name'];
        $returnInfo['room_number'] = $orderInfo['room_number'];
        $returnInfo['use_package'] = $orderInfo['use_package'];
        $returnInfo['order_id'] = $orderInfo['id'];
        $returnInfo['trip_id'] = $orderInfo['trip_id'];
        $returnInfo['contract_id'] = $orderInfo['contract_id'];
        $returnInfo['package_unqid'] = $orderInfo['package_unqid'];
        $returnInfo['country_name'] = $countryInfo['country_name'];
        $returnInfo['endTime'] = $orderInfo['end_time'];

        return getSucc($returnInfo);

    }



    //计算套餐费用
    public function countPackCost()
    {
        $request = $this->request;

        $request = $this->request;
        $startTime = $request->param('start_time','');
        $packName = $request->param('package_name','');
        $packUnqid = $request->param('package_unqid','');
        $seasonUnqid = $request->param('season_unqid','');
        $roomId = $request->param('room_id',0);
        $hotelId = $request->param('hotel_id',0);
        $date = $request->param('start_time','');
        //下面可以不填
        $adultNumber = $request->param('adult_number',1);
        $childNumber = $request->param('child_number',1);

        //【【【账号等级优惠开始
        $accountId = $request->param('account_id',0);

        $accountDataModel = new SupplierAccountDataModel();

        $accDataInfo = $accountDataModel->where('account_id',$accountId)->find();
//halt($accDataInfo);
        if(empty($accDataInfo)){
            return getErr('请登录系统');
        }

        $accountGradeModel = new SupplierGradeModel();
        $accountGradeInfo = $accountGradeModel->where('id',$accDataInfo['grade'])->find();

        if(!empty($accountGradeInfo['ratio'])){
            $ratio = $accountGradeInfo['ratio'];
        }else{
            $ratio = 1;
        }

        //】】】账号等级优惠结束

        $night = substr($packName,2,1);
        //byJepson
        $pricingController = new PricingController();

        $accountId = $request->param('account_id',0);

        if(empty($roomId) || empty($date) || empty($night)){
            return getErr('缺少参数');
        }

        $start = date('Y-m-d',strtotime('- 3 day',time()));

        $nowTime = date('Y-m-d',time());

        $forNumber = 15;

        $pricingInfo = array();

        $priceList = array();

        for($i=0;$i<$forNumber;$i++){

            if($i == 0){
                $priceList = $pricingController->getPackageFareByCheckInDate($roomId,$night,$start);
            }else{
                $start = date('Y-m-d',strtotime('+ 1 day',strtotime($start)));
/*                echo $start.'<br>';*/
                $priceList = $pricingController->getPackageFareByCheckInDate($roomId,$night,$start);
//                var_dump($priceList['child_fare']);

            }

            if(empty($priceList['child_fare'])){
                $priceList['child_fare'] = 0;
            }

            if(empty($priceList['adult_fare'])){
                $priceList['adult_fare'] = 0;
            }

            if(!empty($priceList)){
                $pricingInfo['adult_price'][] = $priceList['adult_fare'];
                $pricingInfo['child_price'][] = $priceList['child_fare'];

                if($start == $nowTime){
                    $pricingInfo['today_adult_price'] = $priceList['adult_fare'];
                    $pricingInfo['today_child_price'] = $priceList['child_fare'];
                }
            }else{
                $pricingInfo['adult_price'][] = 0;
                $pricingInfo['today_adult_price'] = 0;
                $pricingInfo['today_child_price'] = 0;
            }
        }

//        $pricingInfo = $pricingController->getPackageFareByCheckInDate($roomId,$night,$date);

        if(empty($pricingInfo)){
            return getError('该日期不可用');
        }

        $pricingInfo['grade_info'] = $ratio;
        $pricingInfo['time'] = time();

        return getSucc($pricingInfo);

    }

    /* 查询固定套餐或基础套餐并检测日期
     * @param $hotelId (酒店ID)
     * @param $packageName (套餐名称) 例 4D3N
     * @param $startTime (出发日期)
     * @param $day (出发日期到结束日期的天数)
    */
    public function selectPackage($hotelId,$packageName,$startTime,$day)
    {
        $packModel = new ContractPackageModel();

        $packageInfo = $this->formateData($packModel->where(['hotel_id'=>$hotelId,'package_type'=>'标准成人','package_name'=>$packageName])->find());

        //如果有套餐
        if(!empty($packageInfo)){
            $packUnqid = $packageInfo['package_unqid'];
            $seasonUnqid = $packageInfo['season_unqid'];

            for($i=0;$i<$day;$i++){
                $strDay = date('Y-m-d',strtotime('+'.$i.' day',strtotime($startTime)));

                $check = $this->checkPackage($strDay,$seasonUnqid,$packUnqid);

                if(empty($check)){
                    return ['status'=>0,'msg'=>'出发日期不可用'];
                    break;
                }

                if($check == false){
                    return ['status'=>0,'msg'=>'出发日期不在价格季内'];
                    break;
                }
            }
        }else{
            //没有套餐信息，则查询其他套餐信息
            return ['status'=>-1,'msg'=>'没有套餐信息'];
        }

        return ['status'=>1,'msg'=>$packageInfo];


    }



    //检测日期是否在合同开始-结束日期 和是否在价格季日期内
    //return false(不在价格季) null(不在合同日期)
    public function checkPackage($date,$seasonUniqid,$packageUniqid)
    {
        if(empty($date)){
            return null;
        }

        $packModel = new ContractPackageModel();
        $packInfo = $this->formateData($packModel->where(['package_unqid'=>$packageUniqid,'season_unqid'=>$seasonUniqid])->find());
//var_dump($packInfo);
        if(empty($packInfo)){
            return null;
        }

        $conModel = new ContractModel();

        //查询是否出发日是否在可用
        $contractUse = $this->formateData($conModel->where("contract_start_date <= '$date'  AND contract_end_date >= '$date' AND date_type = '可用' AND id = ".$packInfo['contract_id'])->select());
//var_dump($contractUse);
        if(empty($contractUse)){
            return null;
        }

        //查询是否出发日是否在可用
        $contractNoUse = $this->formateData($conModel->where("contract_start_date <= '$date'  AND contract_end_date >= '$date' AND date_type = '不可用' AND id = ".$packInfo['contract_id'])->select());

        if(!empty($contractNoUse)){
            return null;
        }

        $seasonModel = new ContractSeasonModel();

        //查询是否在价格季内
        $seasonStart = $this->formateData($seasonModel->where("season_start_date <= '$date' AND season_end_date >= '$date'")->select());

        if(!empty($seasonStart)){
            return true;
        }else{
            return false;
        }


    }


    //检测日期是否在合同期内
    public function checkDate($date,$packUnqid,$seasonUnqid)
    {
        $packModel = new ContractPackageModel();

        $packInfo = $this->formateData($packModel->where(['package_unqid'=>$packUnqid,'season_unqid'=>$seasonUnqid])->find());
//        $this->dumpExit($packInfo);
        if(empty($packInfo)){
            return false;
        }

        $conModel = new ContractModel();

        $use = $conModel->where('id = '.$packInfo['contract_id']." AND date_type = '可用' AND contract_start_date <= '$date' AND contract_end_date >= '$date'")->select();

        if(empty($use)){
            return false;
        }

        $noUse = $conModel->where('id = '.$packInfo['contract_id']." AND date_type = '不可用' AND contract_start_date <= '$date' AND contract_end_date >= '$date'")->select();

        if(!empty($noUse)){
            return false;
        }

        return true;
    }



    //查询基础套餐
    public function selectBasePack($seasonUniqid)
    {
        $packModel = new ContractPackageModel();
        $packInfo = array();
        $packInfo = $this->formateData($packModel->where(['package_name'=>'基础套餐','season_unqid'=>$seasonUniqid,'package_type'=>'标准成人'])->find());

        return $packInfo;
    }



    //使用套餐计算花费
    public function countCost($date,$packInfo,$roomId,$type='标准')
    {
        if(empty($packInfo)){
            return false;
        }

        $roomModel = new ContractRoomModel();

        $roomInfo = $this->formateData($roomModel->where(['season_unqid'=>$packInfo['season_unqid'],'package_unqid'=>$packInfo['package_unqid'],'room_id'=>$roomId])->find());

        if(empty($roomInfo)){
            return false;
        }

        //查询货币汇率
        $roomModel = new HotelRoomModel();
        $hotelRoomInfo = $roomModel->where('id',$roomId)->find();

        $hotelModel = new HotelModel();
        $hotelInfo = $hotelModel->where('id',$hotelRoomInfo['hotel_id'])->find();

        $excModel = new ExchangeModel();
        //exchange_rate(汇率)
        $excInfo = $excModel->where('id',$hotelInfo['exchange_id'])->find();

        $dult = 0;
        $child = 0;
        $baby = 0;
        $extraPrice = 0;
        $roomrice = 0;
        $cPrice = 0;
        $cTraffic = 0;
        $cActivity = 0;
        $cOther = 0;
        $cGoPrice = 0;
        $cBackPrice = 0;
        $adultTotalPrice = 0;
        $childTotalPrice = 0;


        $adultArr = array();
        $childArr = array();
        $babyArr = array();
        $priceArr = array();
        $goPriceArr = array();
        $backPriceArr = array();
        $goPriceArr = json_decode($roomInfo['include_go_vehicle'],true);
        $backPriceArr = json_decode($roomInfo['include_back_vehicle'],true);

        if($roomInfo['total_price'] == '单人总价'){
            $adultArr = json_decode($roomInfo['adult_fare'],true);
            $adult = $adultArr[0]['standard_price'];

            $childArr = json_decode($roomInfo['extra_child_fare'],true);
            $child = $childArr[0]['standard_price'];

            //成人开始计算

            //成人价格/汇率
            $cPrice = trim($adult / $excInfo['exchange_rate'],'-');

            //去程费用
            foreach($goPriceArr as $k=>$v){
                $cGoPrice += trim($this->getTraffic($v['id']),'-');
                if(empty($this->getTraffic($v['id']))){
                    $cGoPrice = 0;
                    break;
                }
            }

            //没有行程费用，查询默认行程
            if(empty($cGoPrice)){

            }

            //返程费用
            foreach($backPriceArr as $k=>$v){
                $cBackPrice += trim($this->getTraffic($v['id']),'-');
                if(empty($this->getTraffic($v['id']))){
                    $cBackPrice = 0;
                    break;
                }
            }

            //没有返程费用，查询默认返程
            if(empty($cBackPrice)){

            }

            //成人价格
            $adultTotalPrice = trim(($cPrice + $cGoPrice + $cBackPrice + 123.75 + 200 + 35) * 0.7042,'-');

            //儿童开始计算

            //加床
            if($roomInfo['child_is_bed'] >= 0){
                $child = $roomInfo['child_is_bed'];

            }else{//不加床
                $child = $childArr[0]['standard_price'] * 1;
            }

            //儿童价格
            $childTotalPrice = trim(($child + $cGoPrice + $cBackPrice + 102) * 0.7042,'-');

            $returnCost['adult'] = $adultTotalPrice;
            $returnCost['child'] = $childTotalPrice;

            return $returnCost;


        }else if($roomInfo['total_price'] == '房型总价'){
            $priceArr = json_decode($roomInfo['room_price']);

            $roomPrice = $priceArr[0]['standard_price'];
            $extrarice = $priceArr[0]['extension_price'];

            $roomPrice = trim($roomPrice / $excInfo['exchange_rate'],'-');

            //去程费用
            foreach($goPriceArr as $k=>$v){
                $cGoPrice += trim($this->getTraffic($v['id']),'-');
                if(empty($this->getTraffic($v['id']))){
                    $cGoPrice = 0;
                    break;
                }
            }

            //没有行程费用，查询默认行程
            if(empty($cGoPrice)){

            }

            //返程费用
            foreach($backPriceArr as $k=>$v){
                $cBackPrice += trim($this->getTraffic($v['id']),'-');
                if(empty($this->getTraffic($v['id']))){
                    $cBackPrice = 0;
                    break;
                }
            }

            //没有返程费用，查询默认返程
            if(empty($cBackPrice)){

            }

            //成人房型价格
            $adultTotalPrice = trim(($roomPrice + $cGoPrice + $cBackPrice + 123.75 + 200 + 35) * 0.7042,'-');


            //儿童开始计算

            //加床
            if($roomInfo['child_is_bed'] >= 0){
                $child = $roomInfo['child_is_bed'];

            }else{//不加床
                $child = $childArr[0]['standard_price'] * 1;
            }

            //儿童价格
            $childTotalPrice = trim(($child + $cGoPrice + $cBackPrice + 102) * 0.7042,'-');

            $returnCost['adult'] = $adultTotalPrice;
            $returnCost['child'] = $childTotalPrice;

            return $returnCost;

        }





    }

    //查询交通费用
    public function getTraffic($traId,$type='成人')
    {
        if(empty($traId)){
            return 0;
        }

        $trafficInfo = VehicleBasicController::queryFareInfo($traId);

        if(empty($trafficInfo)){
            return false;
        }

        if(!is_array($trafficInfo)){
            $trafficInfo = $this->formateData($trafficInfo);
        }

        if($trafficInfo['pricing_method'] == '单人'){
            if($type=='成人'){
                return $trafficInfo['adult_fare'];
            }else if($type == '儿童'){
                return $trafficInfo['child_fare'];
            }


        }else if($trafficInfo['pricing_method'] == '单载体'){
            return $trafficInfo['rental_fare'];
        }




    }



    public function countPackCost2($roomId,$startTime,$packName)
    {
        $request = $this->request;
        $startTime = $request->param('start_time',$startTime);
        $packName = $request->param('package_name',$packName);
        $roomId = $request->param('room_id',$roomId);
        //下面可以不填
        $adultNumber = $request->param('adult_number',1);
        $childNumber = $request->param('child_number',1);

        $day = substr($packName,0,1);

        $pricingController = new PricingController();
        $date = $startTime;
        $accountId = $request->param('account_id',0);

        if(empty($roomId) || empty($date) || empty($day)){
            return getErr('缺少参数');
        }

        $packPrice = $pricingController->testPricing($roomId,$date,$day);
        dump($packPrice);

        //房型信息
        $hotelRoomModel = new HotelRoomModel();
        $roomInfo = $this->formateData($hotelRoomModel->where('id',$roomId)->find());

        //酒店信息
        $hotelModel = new HotelModel();
        $hotelInfo = $this->formateData($hotelModel->where('id',$roomInfo['hotel_id'])->find());
        //汇率信息
        $exchangeModel = new ExchangeModel();
        $excInfo = $this->formateData($exchangeModel->where('id',$hotelInfo['exchange_id'])->find());

        //账号信息
        $accDataModel = new SupplierAccountDataModel();
        $accDataInfo = $this->formateData($accDataModel->where('account_id',$accountId)->find());

        //账号优惠
        $accGradeModel = new SupplierGradeModel();
        $accGradeInfo = $this->formateData($accGradeModel->where('id',$accDataInfo['grade'])->find());

        //最大成人人数
        $maxCheckAdult = $roomInfo['standard_adult'] + $roomInfo['extra_adult'];
        if($maxCheckAdult == 0){
            $adultNumber = 0;
        }else{
            $adultNumber = ceil($adultNumber / $maxCheckAdult);
        }

        //最大儿童人数
        if($roomInfo['extra_child'] == 0){
            $childNumber = 0;
        }else{
            $childNumber = ceil($childNumber / $roomInfo['extra_child']);
        }

        //房间数量
        $totalRoom = $adultNumber + $childNumber;

        if(!empty($packPrice['fixed'])){//固定套餐计费
            //标准成人价格
            $adultPrice = !empty($packPrice['fixed']['adult_fare']['room_fare'])?$packPrice['fixed']['adult_fare']['room_fare']:0;
            //额外成人价格
            $extAdultPrice = !empty($packPrice['fixed']['extra_adult_fare']['room_fare'])?$packPrice['fixed']['extra_adult_fare']['room_fare']:0;
            //额外儿童价格
            $extChildFare = !empty($packPrice['fixed']['extra_child_fare']['room_fare'])?$packPrice['fixed']['extra_child_fare']['room_fare']:0;
            //与 或
//            $extLogic = $packPrice['fixed']['extra_logic'];
            //交通价格
            $traPrice = !empty($packPrice['fixed']['adult_fare']['vehicle_fare'])?$packPrice['fixed']['adult_fare']['vehicle_fare']:0;
//            echo $traPrice;exit;

            //成人价格/汇率
            $adultExcPrice = $adultPrice / $excInfo['exchange_rate'];
                        echo '成人价格/汇率'.'<br>';
                        echo $adultExcPrice;
                        echo '<br>';
                        echo $adultPrice.' / '.$excInfo['exchange_rate'].'<br>';
            //交通费用
            $traExcPrice = $traPrice / $excInfo['exchange_rate'];

            //成人价格 + 交通费用
            $adultExcPrice = $adultExcPrice + $traExcPrice;
            echo '成人价格 + 交通费用  '.$adultExcPrice.'<br/>';

            //(成人保险123.75 + 大礼包 200)*账号对应优惠
            $giftPrice = (123.75 + 200/* + 35*/) * 1;

                        echo '(成人保险123.75 + 大礼包 200)*账号对应优惠'.'<br>';
                        echo $giftPrice;
                        echo '<br>';
            //(((成人/汇率)+礼包价格)/0.7+1000)
            $otherPrice = (($adultExcPrice + $giftPrice) / 0.7) + 1000;
                        echo '(((成人/汇率)+礼包价格)/0.7+1000)'.'<br>';
                        echo $otherPrice;
                        echo '<br>';
            //(*1.006) 成人总价格
            $adultTotalPrice = $otherPrice * 1.04;
                        echo '(*1.04) 成人总价格'.'<br>';
                        echo $adultTotalPrice;
                        echo '<br>';

            echo '儿童';
            //儿童价格 / 汇率
            $childDayPrice = $extChildFare / $excInfo['exchange_rate'];
                        echo '<hr />';
                        echo '儿童价格 / 汇率'.'<br>';
                        echo $childDayPrice;
                        echo '<br>';
            //(儿童价格汇率天数+儿童保险102)*账号对应优惠
            $giftPrice = ($childDayPrice + 102) * 1;
                        echo '(儿童价格汇率天数+儿童保险102)*账号对应优惠'.'<br>';
                        echo $giftPrice;
                        echo '<br>';
            //(/0.7+500)
            $otherPrice = ($giftPrice / 0.7) + 500;
                        echo '(/0.7+500)'.'<br>';
                        echo $otherPrice;
                        echo '<br>';
            //(*1.006) 儿童总价格
            $childTotalPrice = $otherPrice * 1.04;
                        echo '(*1.04) 儿童总价格'.'<br>';
                        echo $childTotalPrice;
                        echo '<br>';
            //合同ID
            $packageModel = new ContractPackageModel();
            $packageInfo = $this->formateData($packageModel->where('package_unqid',$packPrice['fixed']['adult_fare']['package_unqid'])->find());

            $returnInfo['adult'] = $adultTotalPrice;
            $returnInfo['baby'] = $childTotalPrice;
            $returnInfo['package_unqid'] = $packPrice['fixed']['adult_fare']['package_unqid'];
            $returnInfo['contract_id'] = $packageInfo['contract_id'];
            $returnInfo['room_number'] = $totalRoom;

        }else if(!empty($packPrice['base'])){//基本套餐计费
            //标准成人价格
            $adultPrice = !empty($packPrice['base']['adult_fare']['room_fare'])?$packPrice['base']['adult_fare']['room_fare']:0;
            //额外成人价格
            $extAdultPrice = !empty($packPrice['base']['extra_adult_fare']['room_fare'])?$packPrice['base']['extra_adult_fare']['room_fare']:0;
            //额外儿童价格
            $extChildFare = !empty($packPrice['base']['extra_child_fare']['room_fare'])?$packPrice['base']['extra_child_fare']['room_fare']:0;
            //与 或
//            $extLogic = $packPrice['base']['extra_logic'];
            //交通价格
            $traPrice = !empty($packPrice['base']['adult_fare']['vehicle_fare'])?$packPrice['base']['adult_fare']['vehicle_fare']:0;

            //(标准成人*天数) / 汇率
            $adultExcPrice = ($adultPrice) / $excInfo['exchange_rate'];
                        echo '(标准成人*天数) / 汇率'.'日期='.$day.'汇率='.$excInfo['exchange_rate'].'<br>';
                        echo $adultExcPrice;
                        echo '<br>';

            //交通费用
            $traExcPrice = $traPrice / $excInfo['exchange_rate'];
            echo '成人价格 + 交通费用  '.$traExcPrice . '<br/>';

            $adultExcPrice = $adultExcPrice + $traExcPrice;

            //(成人保险123.75 + 大礼包 200)
            $giftPrice = (123.75 + 200/* + 35*/);
                        echo '(成人保险123.75 + 大礼包 200)';
                        echo $giftPrice;
                        echo '<br>';

            //(((成人/汇率)+礼包价格)/0.7+1000)
            $otherPrice = (($adultExcPrice + $giftPrice) / 0.7) + 1000;
                        echo '(((成人/汇率)+礼包价格)/0.7+1000)';
                        echo $otherPrice;
                        echo '<br>';

            //(*1.04) 成人总价格
            $adultTotalPrice = $otherPrice * 1.04;
                        echo '(*1.04) 成人总价格';
                        echo $adultTotalPrice;
                        echo '<br>';

            //(儿童价格) / 汇率
            $childDayPrice = ($extChildFare) / $excInfo['exchange_rate'];
                        echo '(儿童价格 * 天数) / 汇率';
                        echo $childDayPrice;
                        echo '<br>';

            //(儿童价格汇率天数+儿童保险102)
            $giftPrice = ($childDayPrice + 102);
                        echo '(儿童价格汇率天数+儿童保险102)';
                        echo $giftPrice;
                        echo '<br>';

            //((儿童价格汇率天数+儿童保险102)/0.7+500)
            $otherPrice = ($giftPrice / 0.7) + 500;
                        echo '(/0.7+500)';
                        echo $otherPrice;
                        echo '<br>';

            //(*1.04) 儿童总价格
            $childTotalPrice = $otherPrice * 1.04;
                        echo '(*1.04) 儿童总价格';
                        echo $childTotalPrice;
                        echo '<br>';

            //合同ID
            $packageModel = new ContractPackageModel();
            $packageInfo = $this->formateData($packageModel->where('package_unqid',$packPrice['base']['adult_fare']['package_unqid'])->find());

            $returnInfo['adult'] = $adultTotalPrice;
            $returnInfo['baby'] = $childTotalPrice;
            $returnInfo['package_unqid'] = $packPrice['base']['adult_fare']['package_unqid'];
            $returnInfo['contract_id'] = $packageInfo['contract_id'];
            $returnInfo['room_number'] = $totalRoom;

        }
dump($returnInfo);
//        return getSucc($returnInfo);



    }



    //计算套餐费用
    public function countPackCost3()
    {
        $request = $this->request;

        $request = $this->request;
        $startTime = $request->param('start_time','');
        $packName = $request->param('package_name','');
        $packUnqid = $request->param('package_unqid','');
        $seasonUnqid = $request->param('season_unqid','');
        $roomId = $request->param('room_id',0);
        $hotelId = $request->param('hotel_id',0);
        $date = $request->param('start_time','');
        //下面可以不填
        $adultNumber = $request->param('adult_number',1);
        $childNumber = $request->param('child_number',1);

        //【【【账号等级优惠开始
        $accountId = $request->param('account_id',0);

        $accountDataModel = new SupplierAccountDataModel();

        $accDataInfo = $accountDataModel->where('account_id',$accountId)->find();

        if(empty($accDataInfo)){
            return getErr('请登录系统');
        }

        $accountGradeModel = new SupplierGradeModel();
        $accountGradeInfo = $this->formateData($accountDataModel->where('id',$accDataInfo->grade)->find());

        if(!empty($accountGradeInfo['ratio'])){
            $ratio = $accountGradeInfo['ratio'];
        }else{
            $ratio = 1;
        }

        //】】】账号等级优惠结束


        $night = substr($packName,2,1);
        //byJepson
        $pricingController = new PricingController();

        $accountId = $request->param('account_id',0);

        if(empty($roomId) || empty($date) || empty($night)){
            return getErr('缺少参数');
        }
//halt($roomId);
        $pricingInfo = $pricingController->getPackageFareByCheckInDate($roomId,$night,$date);
        halt($pricingInfo);
//        halt($pricingInfo['adult_fare_detail']['room_detail']);
//        //房型信息
//        $hotelRoomModel = new HotelRoomModel();
//        $roomInfo = $this->formateData($hotelRoomModel->where('id',$roomId)->find());
//
//        //酒店信息
//        $hotelModel = new HotelModel();
//        $hotelInfo = $this->formateData($hotelModel->where('id',$roomInfo['hotel_id'])->find());
////var_dump($packPrice);
//        //汇率信息
//        $exchangeModel = new ExchangeModel();
//        $excInfo = $this->formateData($exchangeModel->where('id',$hotelInfo['exchange_id'])->find());

        //账号信息
        $accDataModel = new SupplierAccountDataModel();
        $accDataInfo = $this->formateData($accDataModel->where('account_id',$accountId)->find());

        //账号优惠
        $accGradeModel = new SupplierGradeModel();
        $accGradeInfo = $this->formateData($accGradeModel->where('id',$accDataInfo['grade'])->find());

        //最大成人人数
        $maxCheckAdult = $roomInfo['standard_adult'] + $roomInfo['extra_adult'];
        if($maxCheckAdult == 0){
            $adultNumber = 0;
        }else{
            $adultNumber = ceil($adultNumber / $maxCheckAdult);
        }

        //最大儿童人数
        if($roomInfo['extra_child'] == 0){
            $childNumber = 0;
        }else{
            $childNumber = ceil($childNumber / $roomInfo['extra_child']);
        }

        //房间数量
        $totalRoom = $adultNumber + $childNumber;
//unset($packPrice['fixed']);
//var_dump($packPrice);
//        $this->dumpExit($packPrice);
        if(!empty($packPrice['fixed'])){//固定套餐计费
            //标准成人价格
            $adultPrice = !empty($packPrice['fixed']['adult_fare']['room_fare'])?$packPrice['fixed']['adult_fare']['room_fare']:0;
            //额外成人价格
            $extAdultPrice = !empty($packPrice['fixed']['extra_adult_fare']['room_fare'])?$packPrice['fixed']['extra_adult_fare']['room_fare']:0;
            //额外儿童价格
            $extChildFare = !empty($packPrice['fixed']['extra_child_fare']['room_fare'])?$packPrice['fixed']['extra_child_fare']['room_fare']:0;
            //与 或
//            $extLogic = $packPrice['fixed']['extra_logic'];
            //交通价格
            $traPrice = !empty($packPrice['fixed']['adult_fare']['vehicle_fare'])?$packPrice['fixed']['adult_fare']['vehicle_fare']:0;
//            echo $traPrice;exit;
            /**
             * jepson
             */
            $childVehicleFare = !empty($packPrice['fixed']['extra_child_fare']['vehicle_fare'])?$packPrice['fixed']['extra_child_fare']['vehicle_fare']:0;
            //            //成人价格/汇率
//            $adultExcPrice = $adultPrice / $excInfo['exchange_rate'];
///*            echo '成人价格/汇率'.'<br>';
//            echo $adultExcPrice;
//            echo '<br>';
//            echo $adultPrice.' / '.$excInfo['exchange_rate'].'<br>';*/
//            //交通费用
//            $traExcPrice = $traPrice / $excInfo['exchange_rate'];
//
//            //成人价格 + 交通费用
//            $adultExcPrice = $adultExcPrice + $traExcPrice;
////            echo '成人价格 + 交通费用  '.$adultExcPrice.'<br/>';
//
//            //(成人保险123.75 + 大礼包 200)*账号对应优惠
//            $giftPrice = (123.75 + 200/* + 35*/) * 1;
//
///*            echo '(成人保险123.75 + 大礼包 200)*账号对应优惠'.'<br>';
//            echo $giftPrice;
//            echo '<br>';*/
//            //(((成人/汇率)+礼包价格)/0.7+1000)
//            $otherPrice = (($adultExcPrice + $giftPrice) / 0.7) + 1000;
///*            echo '(((成人/汇率)+礼包价格)/0.7+1000)'.'<br>';
//            echo $otherPrice;
//            echo '<br>';*/
//            //(*1.006) 成人总价格
//            $adultTotalPrice = $otherPrice * 1.04;
///*            echo '(*1.04) 成人总价格'.'<br>';
//            echo $adultTotalPrice;
//            echo '<br>';*/
//            //成人总价格*账号优惠
//            $adultTotalPrice = $adultTotalPrice * $ratio;
//
//
////            echo '儿童';
//            //儿童价格 / 汇率
//            $childDayPrice = $extChildFare / $excInfo['exchange_rate'];
///*            echo '儿童价格 / 汇率'.'<br>';
//            echo $childDayPrice;
//            echo '<br>';*/
//            //(儿童价格汇率天数+儿童保险102)*账号对应优惠
//            $giftPrice = ($childDayPrice + 102) * 1;
///*            echo '(儿童价格汇率天数+儿童保险102)*账号对应优惠'.'<br>';
//            echo $giftPrice;
//            echo '<br>';*/
//            //(/0.7+500)
//            $otherPrice = ($giftPrice / 0.7) + 500;
///*            echo '(/0.7+500)'.'<br>';
//            echo $otherPrice;
//            echo '<br>';*/
//            //(*1.006) 儿童总价格
//            $childTotalPrice = $otherPrice * 1.04;
///*            echo '(*1.04) 儿童总价格'.'<br>';
//            echo $childTotalPrice;
//            echo '<br>';*/
//            //儿童总价格*账号优惠
//            $childTotalPrice = $childTotalPrice * $ratio;

            $pricingController = new PricingController();
            $pricingController->exchangeRate = $excInfo['exchange_rate'];

            $adultTotalPrice = $pricingController->getAdultTotalFare($adultPrice,$traPrice);
            $childTotalPrice = $pricingController->getChildTotalFare($extChildFare,$childVehicleFare);
            //合同ID
            $packageModel = new ContractPackageModel();
            $packageInfo = $this->formateData($packageModel->where('package_unqid',$packPrice['fixed']['adult_fare']['package_unqid'])->find());

            $returnInfo['adult'] = $adultTotalPrice;
            $returnInfo['baby'] = $childTotalPrice;
            $returnInfo['package_unqid'] = $packPrice['fixed']['adult_fare']['package_unqid'];
            $returnInfo['contract_id'] = $packageInfo['contract_id'];
            $returnInfo['room_number'] = $totalRoom;

        }else if(!empty($packPrice['base'])){//基本套餐计费
            //标准成人价格
            $adultPrice = !empty($packPrice['base']['adult_fare']['room_fare'])?$packPrice['base']['adult_fare']['room_fare']:0;
            //额外成人价格
            $extAdultPrice = !empty($packPrice['base']['extra_adult_fare']['room_fare'])?$packPrice['base']['extra_adult_fare']['room_fare']:0;
            //额外儿童价格
            $extChildFare = !empty($packPrice['base']['extra_child_fare']['room_fare'])?$packPrice['base']['extra_child_fare']['room_fare']:0;
            //与 或
//            $extLogic = $packPrice['base']['extra_logic'];
            //交通价格
            $traPrice = !empty($packPrice['base']['adult_fare']['vehicle_fare'])?$packPrice['base']['adult_fare']['vehicle_fare']:0;

            //(标准成人*天数) / 汇率
            $adultExcPrice = ($adultPrice) / $excInfo['exchange_rate'];
            /*            echo '(标准成人*天数) / 汇率'.'日期='.$day.'汇率='.$excInfo['exchange_rate'].'<br>';
                        echo $adultExcPrice;
                        echo '<br>';*/

            //交通费用
            $traExcPrice = $traPrice / $excInfo['exchange_rate'];

            $childVehicleFare = !empty($packPrice['base']['extra_child_fare']['vehicle_fare'])?$packPrice['base']['extra_child_fare']['vehicle_fare']:0;
//            echo '成人价格 + 交通费用  '.$traExcPrice . '<br/>';

//             $adultExcPrice = $adultExcPrice + $traExcPrice;

//             //(成人保险123.75 + 大礼包 200)
//             $giftPrice = (123.75 + 200/* + 35*/);
// /*            echo '(成人保险123.75 + 大礼包 200)';
//             echo $giftPrice;
//             echo '<br>';*/

//             //(((成人/汇率)+礼包价格)/0.7+1000)
//             $otherPrice = (($adultExcPrice + $giftPrice) / 0.7) + 1000;
// /*            echo '(((成人/汇率)+礼包价格)/0.7+1000)';
//             echo $otherPrice;
//             echo '<br>';*/

//             //(*1.006) 成人总价格
//             $adultTotalPrice = $otherPrice * 1.04;
// /*            echo '(*1.04) 成人总价格';
//             echo $adultTotalPrice;
//             echo '<br>';*/
//             //成人总价格*账号等级优惠
//             $adultTotalPrice = $adultTotalPrice * $ratio;

//             //(儿童价格) / 汇率
//             $childDayPrice = $extChildFare / $excInfo['exchange_rate'];
// /*            echo '儿童价格 / 汇率';
//             echo $childDayPrice;
//             echo '<br>';*/

//             //(儿童价格汇率天数+儿童保险102)
//             $giftPrice = ($childDayPrice + 102);
// /*            echo '(儿童价格汇率天数+儿童保险102)';
//             echo $giftPrice;
//             echo '<br>';*/

//             //((儿童价格汇率天数+儿童保险102)/0.7+500)
//             $otherPrice = ($giftPrice / 0.7) + 500;
// /*            echo '(/0.7+500)';
//             echo $otherPrice;
//             echo '<br>';*/

//             //(*1.04) 儿童总价格
//             $childTotalPrice = $otherPrice * 1.04;
// /*            echo '(*1.04) 儿童总价格';
//             echo $childTotalPrice;
//             echo '<br>';*/
//             //儿童总价格*账号等级优惠
//             $childTotalPrice = $childTotalPrice * $ratio;
            $pricingController = new PricingController();
            $pricingController->exchangeRate = $excInfo['exchange_rate'];

            $adultTotalPrice = $pricingController->getAdultTotalFare($adultPrice,$traPrice);
            $childTotalPrice = $pricingController->getChildTotalFare($extChildFare,$childVehicleFare);
            //合同ID
            $packageModel = new ContractPackageModel();
            $packageInfo = $this->formateData($packageModel->where('package_unqid',$packPrice['base']['adult_fare']['package_unqid'])->find());

            $returnInfo['adult'] = $adultTotalPrice;
            $returnInfo['baby'] = $childTotalPrice;
            $returnInfo['package_unqid'] = $packPrice['base']['adult_fare']['package_unqid'];
            $returnInfo['contract_id'] = $packageInfo['contract_id'];
            $returnInfo['room_number'] = $totalRoom;

        }
//var_dump($returnInfo);

        if(empty($returnInfo)){
            return getErr('系统报价失败');
        }

        return getSucc($returnInfo);

        /*        return getErr('日期不可用');

                exit;*/


    }






}

?>