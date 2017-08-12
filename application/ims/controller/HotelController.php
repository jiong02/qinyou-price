<?php
namespace app\ims\controller;
use app\ims\model\HotelModel;
use app\ims\model\ImageModel;
use app\ims\model\PlaceModel;
use app\index\controller\Hotel;
use \think\Validate;
use app\ims\model\HotelRoomModel;
use app\ims\model\HotelFacilityModel;
use app\ims\model\ExchangeModel;
use app\index\model\Account;
use app\ims\model\PlaceNoStatusModel;

class HotelController extends BaseController
{

    //联系方式验证规则
    protected $contactWay = [
        'hotel_phone|酒店电话' => 'max:300',
        'hotel_email|酒店电邮' => 'require|email',
        'hotel_instant_messaging|酒店通讯方式' => 'max:300',
        'hotel_website|酒店网址' => 'max:50',
        'hotel_address|酒店地址' => 'max:200',
    ];

    //房间类型
    protected $room = [
        'room_name|房间中文名称' => 'require',
        'room_area|房间面积' => 'number',
        'room_amount|房间类型数量' => 'number',
        'room_equipment|房间内设备' => 'require',
        'standard_adult|允许入住的最大标准成人' => 'number',
        'extra_adult|允许入住的最大额外成人' => 'number',
        'extra_child|允许入住的最大额外儿童' => 'number',
    ];

    //酒店基本信息
    protected $hotelInfo = [
      'hotel_name|酒店房间' => 'require',
      'hotel_star|酒店星级' => 'number',
    ];


    //获得酒店列表
    public function getHotelList()
    {
        $request = $this->request;
        $placeId = $request->param('place_id',0);

        if(empty($placeId)){
            return getErr('');
        }

        $placeInfo = PlaceNoStatusModel::get(['id'=>$placeId]);
        $placeInfo = $this->formateData($placeInfo);

        if(empty($placeInfo)){
            return getErr('');
        }

        $hotelModel = new HotelModel();
        $hotelInfo = '';
        $hotelInfo = $hotelModel
            ->field('id,hotel_name as name,hotel_ename as eng_name,image_uniqid')
            ->where([
            'place_id' => $placeId,
        ])->select();
        $imageModel = new ImageModel();
        foreach ($hotelInfo as $index => $data){
            if(!empty($data['image_uniqid'])){
                $src = $imageModel->field('image_category,image_path')->where('image_uniqid',$data['image_uniqid'])->find();
            }else{
                $src = '';
            }

            $hotelInfo[$index]['src'] = $src;
        }

        $hotelInfo = $this->formateData($hotelInfo);

        if(!empty($hotelInfo[0]['name'])){
            $resultData = [
                'dest_nav'=>[
                    'id'=>$placeInfo['id'],
                    'name'=>$placeInfo['place_name']
                ],
                'hotel' => $hotelInfo,
            ];
        }else{
            $resultData = [
                'dest_nav' => [
                    'id' => $placeInfo['id'],
                    'name' => $placeInfo['place_name'],
                ],
                'hotel' => array(),
            ];
        }

        return getSucc($resultData);
    }

    //添加酒店
    public function addHotel()
    {
        $hotelModel = new HotelModel();
        $hotelModel = $this->hotelInfoData($hotelModel);

        if($hotelModel->save()){
            return getSucc($hotelModel->id);
        }else{
            return getErr('添加失败');
        }

    }

    //修改酒店
    public function updateHotel()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);

        if(empty($hotelId)){
            return getErr(array());
        }

        $hotelModel = HotelModel::get($hotelId);
        $hotelModel = $this->hotelInfoData($hotelModel);
//        $this->dumpExit($this->formateData($hotelModel));
        if($hotelModel->save()){
            return getSucc('数据修改成功');
        }
            return getErr('请查询数据是否没变');
    }


    //添加/修改酒店信息
    public function hotelInfoData($hotelModel)
    {
        $request = $this->request;
        if($request->param('country_id',0)){
            $hotelModel->country_id = $request->param('country_id',0);
        }

        $hotelModel->hotel_name = $request->param('hotel_name','');
        $hotelModel->hotel_ename = $request->param('hotel_e_name','');
        if($request->param('id',0)){
            $hotelModel->place_id = $request->param('id',0);
        }

        $hotelModel->hotel_star = $request->param('hotel_star',0);
        $hotelModel->image_uniqid = $request->param('image_uniqid','');
        $hotelModel->hotel_number = $request->param('hotel_number',0);
        $hotelModel->hotel_description = $request->param('hotel_desc','');

        return $hotelModel;

    }

    //获得海岛下的酒店列表
    public function getPlaceHotelList()
    {
        $request = $this->request;
        $placeId = $request->param('id',0);

        if(empty($placeId)){
            return getErr('');
        }

        $hotelList = array();
        $hotelModel = new HotelModel();
        $hotelList = $hotelModel
            ->field('hotel_name as name,hotel_ename as eng_name')
            ->where('place_id',$placeId)
            ->select();


        if(!empty($hotelList)){
            $hotelList = $this->formateData($hotelList);
            return getSucc($hotelList);
        }

        return getErr('');
    }

    //获得酒店基本信息
    public function getHotelInfo()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);

        if(empty($hotelId)){
            return getErr('');
        }

        $hotelModel = new HotelModel();
        $hotelInfo = $hotelModel
            ->field('id,place_id,country_id,hotel_name,hotel_ename,hotel_star,hotel_number,hotel_description,image_uniqid')
            ->where('id',$hotelId)
            ->find();
        $imageModel = new ImageModel();
        $src = $imageModel->field('id as image_id,image_category,image_path')->where('image_uniqid',$hotelInfo['image_uniqid'])->select();
        if(!empty($hotelInfo['id'])){
            $hotelInfo = $this->formateData($hotelInfo);
            $hotelInfo['img'] = $src;
            return getSucc($hotelInfo);
        }

        return getErr(array());
    }

    //获得酒店的联系方式
    public function getHotelContactWay()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);

        if(empty($hotelId)){
            return getErr('请存入酒店ID');
        }

        $contactInfo = HotelModel::field('hotel_phone,hotel_email,hotel_instant_messaging,hotel_website,hotel_address')->where('id',$hotelId)->find();

        if(!empty($contactInfo)){
            $contactInfo = $this->formateData($contactInfo);

            if(empty($contactInfo['hotel_phone'])){
                $contactInfo['hotel_phone'] = [];
            }else{
                $contactInfo['hotel_phone'] = json_decode($contactInfo['hotel_phone'],true);
            }

            if(empty($contactInfo['hotel_instant_messaging'])){
                $contactInfo['hotel_instant_messaging'] = [];
            }else{
                $contactInfo['hotel_instant_messaging'] = json_decode($contactInfo['hotel_instant_messaging'],true);
            }

            return getSucc($contactInfo);
        }

        return getErr('酒店不存在');
    }

    //酒店联系方式数据
    public function contactWayData($hotelModel)
    {
        $request = $this->request;
        $hotelModel->hotel_phone = $request->param('hotel_phone','');
        $hotelModel->hotel_email = $request->param('hotel_email','');
        $hotelModel->hotel_instant_messaging = $request->param('hotel_instant_messaging','');
        $hotelModel->hotel_website = $request->param('hotel_website','');
        $hotelModel->hotel_address = $request->param('hotel_address','');

        return $hotelModel;
    }

    //修改酒店联系方式
    public function updateHotelContactWay()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);
        if(empty($hotelId)){
            return getErr('请输入酒店ID');
        }
        $hotelModel = HotelModel::get($hotelId);

        $hotelModel = $this->contactWayData($hotelModel);

        if($hotelModel->save()){
            return getSucc('修改成功');
        }else{
            return getErr('修改失败，请查看数据是否没有变化');
        }


    }


    //获得酒店房型列表
    public function getHotelRoomList()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);

        if(empty($hotelId)){
            return getErr('请输入酒店ID');
        }

        $hotelRoomModel = new HotelRoomModel();

        $roomInfo = array();
        $roomInfo = $hotelRoomModel->where('hotel_id',$hotelId)->field('id,room_name')->select();

        if(!empty($roomInfo)){
            return getSucc($roomInfo);
        }

        return getErr('酒店不存在');
    }

    //获得酒店房型信息
    public function getHotelRoomInfo()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);
        $roomId = $request->param('room_id',0);

        if(empty($hotelId) || empty($roomId)){
            return getErr('查询失败');
        }

        $roomModel = new HotelRoomModel();
        $roomInfo = array();
        $roomInfo = $roomModel
            ->where(['hotel_id'=>$hotelId,'id'=>$roomId])
            ->find();
        $imageModel = new ImageModel();
        $src = $imageModel->field('id as image_id,image_category,image_path')->where('image_uniqid',$roomInfo['image_uniqid'])->select();

        if(!empty($roomInfo)){
            $roomInfo = $this->formateData($roomInfo);

            if(!empty($src)){
                $roomInfo['img'] = $src;
            }else{
                $roomInfo['img'] = array();
            }

            if(!empty($roomInfo['room_equipment'])){
                $roomInfo['room_equipment'] = json_decode($roomInfo['room_equipment']);
            }else{
                $roomInfo['room_equipment'] = array();
            }

            if(!empty($roomInfo['room_bed_info'])){
                $roomInfo['room_bed_info'] = json_decode($roomInfo['room_bed_info'],true);
            }else{
                $roomInfo['room_bed_info'] = [['bed_type'=>'','bed_num'=>'']];
            }



            return getSucc($roomInfo);
        }

        return getErr('查询失败，没有房间信息');

    }

    //添加酒店房型图片
    public function addHotelRoomImage()
    {
        $request = $this->request;

        $imageController = new ImageController();

        $uniqid = $request->param('unqid',uniqid());

        if(empty($uniqid)){
            $uniqid = uniqid();
        }

        $roomId = $request->param('room_id',0);

        if(empty($roomId)){
            return getErr('没有房型信息');
        }

        $hotelRoomModel = new HotelRoomModel();

        $hotelRoomInfo = $hotelRoomModel->where('id',$roomId)->find();

        if(empty($hotelRoomInfo)){
            return getErr('没有房型信息');
        }

        $imageInfo = $imageController->fileUpload2($request,$uniqid);

        $returnInfo['id'] = $imageInfo->id;
        $returnInfo['image_uniqid'] = $imageInfo->image_uniqid;
        $returnInfo['image_path'] = $imageInfo->image_path;

        $hotelRoomInfo->image_uniqid = $returnInfo['image_uniqid'];

        if(!empty($imageInfo->save())){
            return getSucc($returnInfo);
        }

        return getErr('修改失败');


    }


    //添加房型信息
    public function addHotelRoom()
    {
        $request = $this->request;
        $roomList = $request->param('room_list','');

        $roomList = json_decode($roomList,true);

        if(empty($roomList) || !is_array($roomList)){
            return getErr('请存入酒店房型列表');
        }

        $roomModel = new HotelRoomModel();
        if($roomModel->saveAll($roomList)){
            return getSucc('添加成功');
        }
            return getErr('添加失败');

    }

    //删除酒店房间
    public function deleteHotelRoom()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);

        if(empty($hotelId)){
            return getErr('没有该酒店房间');
        }

        $roomModel = new HotelRoomModel();

        if($roomModel->where('id',$hotelId)->delete()){
            return getSucc('删除成功');
        }

        return getErr('删除失败');

    }

    //修改房型的信息
    public function updateHotelRoom()
    {
        $request = $this->request;
        $param = $request->param();
        $roomId = $request->param('room_id',0);
        $hotelId = $request->param('hotel_id',0);


        if(empty($roomId) || empty($hotelId)){
            return getErr('修改失败');
        }

        $checkThink = new Validate($this->room);

        $result = $checkThink->check($param);

        if(empty($result)){
            return getErr($checkThink->getError());
        }


        if(!empty($param['room_id'])){
            $roomModel = HotelRoomModel::get($param['room_id']);
        }else{
            $roomModel = new HotelRoomModel();
        }
        unset($param['room_id']);
        unset($param['hotel_id']);

        unset($param['img']);

//        $this->dumpExit($param);
        $mysqlResult = $roomModel->save($param);

        if(!empty($mysqlResult)){
            return getSucc('修改成功');
        }

        return getErr('修改失败,请查询数据是否没有变化');
    }

    //获得酒店设施列表
    public function getFacilityList()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);

        if(empty($hotelId)){
            return getErr('请输入酒店ID');
        }

        $facList = array();
        $facModel = new HotelFacilityModel();
        $facList = $facModel->where(['hotel_id'=>$hotelId,'activity_type'=>'设施'])->field('activity_name as facility_name,id')->select();

        if(!empty($facList)){
            return getSucc($this->formateData($facList));
        }
        return getErr('没有房型设施数据');

    }

    //删除酒店设施
    public function deleteFacility()
    {
        $request = $this->request;
        $facId = $request->param('fac_id',0);

        if(empty($facId)){
            return getErr('没有该设施信息');
        }

        $facModel = new HotelFacilityModel();
        if($facModel->where('id',$facId)->delete()){
            return getSucc('删除成功');
        }

        return getErr('删除失败');

    }

    //获得酒店设施信息
    public function getFacilityInfo()
    {
        $request = $this->request;
        $facId = $request->param('fac_id',0);

        if(empty($facId)){
            return getErr('请输入设施ID');
        }

        $facModel = new HotelFacilityModel();
        $facInfo = array();
        $facInfo = $facModel
            ->field('id,activity_name as name,activity_ename as en_name,is_charge as cost,workday as openDay,work_time as openTime,book_type as book,charge_mode as chargeType,standard_passengers as munPeo,minimum_passengers as minPeo,adult_rate as adultCost,child_rate as child,infant_rate as baby,age_limit')
            ->where('id',$facId)
            ->find();

        $facInfo = $this->formateData($facInfo);

        if(!empty($facInfo)){

            $facInfo = $this->formateData($facInfo);
            if(!empty($facInfo['age_limit'])){
                $ageLimit = array();
                $ageLimit = explode(',',$facInfo['age_limit']);
                $facInfo['age_star'] = $ageLimit[0];
                $facInfo['age_end'] = $ageLimit[1];
            }else{
                $facInfo['age_star'] = '';
                $facInfo['age_end'] = '';
            }

            $facInfo['cost'] = $facInfo['cost'] == '0' ? '免费' : '收费';
            $imageModel = new ImageModel();
            if(!empty($facInfo['image_uniqid'])){
                $src = $imageModel->field('id as image_id,image_category,image_path')->where('image_uniqid',$facInfo['image_uniqid'])->select();
            }else{
                $src = [];
            }

            $facInfo['img'] = $src;

            return getSucc($facInfo);
        }

        return getErr('设施不存在');

    }

    //获得酒店活动列表
    public function getHotelActivity()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);

        if(empty($hotelId)){
            return getErr('该酒店没有活动信息');
        }

        $actModel = new HotelFacilityModel();
        $actInfo = $actModel->field('id,hotel_id,activity_name,activity_ename')->where(['hotel_id'=>$hotelId,'activity_type'=>'活动'])->select();

        if(!empty($actInfo)){
            $actInfo = $this->formateData($actInfo);
            return getSucc($actInfo);
        }

        return getErr('没有该酒店活动信息');

    }

    //删除酒店活动
    public function deleteHotelActivity()
    {
        $request = $this->request;
        $actId = $request->param('act_id',0);

        if(empty($actId)){
            return getErr('没有该酒店活动');
        }

        $actModel = new HotelFacilityModel();

        if($actModel->where('id',$actId)->delete()){
            return getSucc('删除成功');
        }
            return getErr('删除失败');

    }

    //获得活动信息
    public function getActivityInfo()
    {
        $request = $this->request;
        $actId = $request->param('activity_id',0);

        if(empty($actId)){
            return getErr('请输入活动ID');
        }

        $actModel = HotelFacilityModel::get($actId);

        if(empty($actModel)){
            return getErr('没有该活动信息');
        }

        $actModel = $this->formateData($actModel);

        if(!empty($actModel['age_limit'])){
            $ageArray = explode(',',$actModel['age_limit']);
            $actModel['age_start'] = $ageArray[0];
            $actModel['age_end'] = $ageArray[1];
        }

        return getSucc($actModel);

    }

    //添加酒店活动列表
    public function addActivityList()
    {
        $request = $this->request;
        $actList = json_decode($request->param('actList',''),true);


        if(empty($actList)){
            return getErr('没有活动信息');
        }

        $actModel = new HotelFacilityModel();

        $actResult = $actModel->saveAll($actList);

        if(!empty($actResult)){
            return getSucc('添加成功');
        }

        return getErr('添加失败');

    }

    //修改活动信息
    public function updateActivityInfo()
    {
        $request = $this->request;
        $actId = $request->param('activity_id',0);

        if(empty($actId)){
            return getErr('请输入活动ID');
        }

        $actInfo = HotelFacilityModel::get($actId);

        if(empty($actInfo)){
            return getErr('没有该活动信息');
        }

        $actInfo->hotel_id = $request->param('hotel_id',0);
        $actInfo->activity_name = $request->param('activity_name','');
        $actInfo->activity_ename = $request->param('activity_ename','');
        $actInfo->activity_type = $request->param('activity_type','活动');
        $actInfo->image_uniqid = $request->param('image_uniqid','');
        $actInfo->is_charge = $request->param('is_charge',0);
        $actInfo->charge_mode = $request->param('charge_mode','单次');
        $actInfo->pricing_mode = $request->param('pricing_mode','按人');
        $actInfo->book_type = $request->param('book_type','可预定');
        $actInfo->play_time = $request->param('play_time','');
        $actInfo->standard_passengers = $request->param('standard_passengers',0);
        $actInfo->minimum_passengers = $request->param('minimum_passengers',0);
        $actInfo->max_passengers = $request->param('max_passengers',0);
        $actInfo->workday = $request->param('workday','每日开放');
        $actInfo->work_time = $request->param('work_time','');
        $actInfo->age_limit = $request->param('age_start',0).','.$request->param('age_end',0);
        $actInfo->activity_rate = $request->param('activity_rate',0.00);
        $actInfo->adult_rate = $request->param('adult_rate',0.00);
        $actInfo->child_rate = $request->param('child_rate',0.00);
        $actInfo->infant_rate = $request->param('infant_rate',0.00);
        $actInfo->currency_unit = $request->param('currency_unit','');
        $actInfo->activity_cost = $request->param('activity_cost',0.00);

        $actResult = $actInfo->save();

        if(!empty($actResult)){
            return getSucc('修改成功');
        }

        return getErr('修改失败,数据没有变化');


    }

    //获得酒店年龄数据
    public function getHotelAgeLimit()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);

        if(empty($hotelId)){
            return getErr('请输入酒店ID');
        }

        $hotelModel = new HotelModel();

        $ageInfo = $hotelModel
            ->field('adult_age_range as adult,child_age_range as child,infant_age_range as baby,infant_age_unit')
            ->where('id',$hotelId)
            ->find();



        if(!empty($ageInfo)){
            $ageInfo = $this->formateData($ageInfo);
/*
            $ageArray = ['startTime'=>'','endTime'=>''];
            $ageInfo['adult'] = !empty($ageInfo['adult']) ? json_decode($ageInfo['adult'],true) : $ageArray;
            $ageInfo['child'] = !empty($ageInfo['child']) ? json_decode($ageInfo['child'],true) : $ageArray;
            $ageInfo['baby'] = !empty($ageInfo['baby']) ? json_decode($ageInfo['baby'],true) : $ageArray;*/

            return getSucc($ageInfo);
        }

        return getErr('没有年龄数据');

    }

    //修改酒店年龄数据
    public function updateHotelAgeLimit()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);
        $adult_age_range = json_encode($request->param('adult',''));
        $child_age_range = json_encode($request->param('child',''));
        $infant_age_range = json_encode($request->param('baby',''));
        $infant_age_unit = $request->param('infant_age_unit','');

        $hotelModel = HotelModel::get($hotelId);
        $hotelModel->adult_age_range = trim($adult_age_range,'"');
        $hotelModel->child_age_range = trim($child_age_range,'"');
        $hotelModel->infant_age_range = trim($infant_age_range,'"');
        $hotelModel->infant_age_unit = $infant_age_unit;

        if($hotelModel->save()){
            $exp_adult = explode(',',$adult_age_range);
            $exp_child = explode(',',$child_age_range);
            $exp_infant = explode(',',$infant_age_range);

            $returnInfo['min_adult_age'] = $exp_adult[0];
            $returnInfo['max_adult_age'] = $exp_adult[1];
            $returnInfo['min_child_age'] = $exp_child[0];
            $returnInfo['max_child_age'] = $exp_child[1];
            $returnInfo['min_infant_age'] = $exp_infant[0];
            $returnInfo['max_infant_age'] = $exp_infant[1];

            return getSucc($returnInfo);
        }

        return getErr('修改失败,数据没有改变');

    }



    //添加酒店设施
    public function addFacility()
    {
        $request = $this->request;
        $facList = $request->param('fac_list','');

        $facList = json_decode($facList,true);

        $count = count($facList);

        if(empty($facList) || $count <= 0){
            return getErr('请传入酒店设施数据');
        }

        $newFacList = array();
        foreach($facList as $k=>$v){
            foreach($v as $m=>$n){
                $newFacList[$k]['activity_name'] = $facList[$k]['name'];
                $newFacList[$k]['hotel_id'] = $facList[$k]['hotel_id'];
                $newFacList[$k]['activity_type'] = '设施';
            }
        }

        $facModel = new HotelFacilityModel();
        $facResult = $facModel->saveAll($newFacList);

        if(!empty($facResult)){
            return getSucc('添加成功');
        }

        return getErr('添加失败');
    }

    //修改酒店设施
    public function updateFacility()
    {
        $request = $this->request;
        $facId = $request->param('fac_id',0);

        if(empty($facId)){
            return getErr('请输入设施ID');
        }

        $result = array();
        $facModel = HotelFacilityModel::get($facId);
        $facModel = $this->hotelFacData($facModel);
        if(!empty($facModel) && $facModel->save()){
            return getSucc('修改成功');
        }

            return getErr('修改失败，请查看数据是否没有变化');
    }

    //酒店设施数据
    public function hotelFacData($facModel)
    {
        $request = $this->request;
        $facModel->hotel_id = $request->param('hotel_id',0);
        $facModel->activity_name = $request->param('name','');
        $facModel->activity_ename = $request->param('eng_name','');
        $facModel->is_charge = $request->param('cost','免费') == '免费'? 0 : 1;
        $facModel->age_limit = $request->param('age_star',0).','.$request->param('age_end',0);
        $facModel->workday = $request->param('openDay','');
        $facModel->work_time = $request->param('openTime','');
        $facModel->book_type = $request->param('book','');
        $facModel->charge_mode = $request->param('chargeType','');
        $facModel->standard_passengers = $request->param('numPeo',0);
        $facModel->minimum_passengers = $request->param('minPeo',0);
        $facModel->adult_rate = $request->param('adultCost',0);
        $facModel->child_rate = $request->param('child',0);
        $facModel->infant_rate = $request->param('baby',0);
        $facModel->currency_unit = $request->param('currency_unit','');
        $facModel->infant_rate = $request->param('baby',0);

        return $facModel;
    }

    //获得总汇率
    public function getExchangeList()
    {
        $list = [];
        $list = ExchangeModel::all();
        $list = $this->formateData($list);

        if(empty($list)){
            return getErr('没有汇率信息');
        }

        $newReturn = array();

        foreach($list as $k=>$v){
                $newReturn['exc_list'][$k]['id'] = $v['id'];
                $newReturn['exc_list'][$k]['currency_unit'] = $v['currency_unit'];
                $newReturn['exc_list'][$k]['exchange_rate'] = $v['exchange_rate'];
                $newReturn['exc_list'][$k]['exc_one'] = 100;
                $newReturn['exc_list'][$k]['exc_two'] = 1 / ($v['exchange_rate'] * 100);
        }

        $newReturn['is_remind'] = $list[0]['is_remind'];
        $newReturn['remind_cycle'] = $list[0]['remind_cycle'];
        $newReturn['update_employee'] = $list[0]['update_employee'];

        return getSucc($newReturn);

    }

    //汇率信息
    public function exchangeData($excModel)
    {
        $request = $this->request;
        $excOne = $request->param('exc_one',0);
        $excTwo = $request->param('exc_two',0);
        $rate = $excTwo / $excOne;
        $excModel->currency_unit = $request->param('currency_unit','');
        $excModel->exchange_rate = $rate;
        $excModel->is_remind = $request->param('is_remind','否') == '否' ? 0 : 1;
        $excModel->remind_cycle = $request->param('remind_cycle','');
        $excModel->remind_employee_id = $request->param('remind_employee_id','');
        $excModel->update_employee = $request->param('update_employee',0);

        return $excModel;
    }

    //删除汇率
    public function deleteExchange()
    {
        $request = $this->request;
        $excId = $request->param('exc_id',0);

        if(empty($excId)){
            return getErr('没有该汇率信息');
        }

        $excModel = new ExchangeModel();

        if($excModel->where('id',$excId)->delete()){
            return getSucc('删除成功');
        }

        return getErr('删除失败');

    }

    //添加总汇率
    public function addExchange()
    {
        return getErr('添加失败');
        $request = $this->request;
        $excList = json_decode($request->param('exc_list',''),true);
        $isRemind = $request->param('is_remind',0);
        $remindCycle = $request->param('remind_cycle','每周一');
        $remindId = $request->param('remind_employee_id','*');
        $updateEmployee = $request->param('update_employee','');
//$this->dumpExit($updateEmployee);
        /*$a = [
            'exc_list'=> [
                ['exc_one'=>'100','exc_two'=>'200','currency_unit'=>'泰铢'],
                ['exc_one'=>'100','exc_two'=>'200','currency_unit'=>'泰铢']],
            'is_remind'=> 0,
            'remind_cycle'=> '每周一',
            'remind_employee_id' => '800'
        ];*/

        if(empty($excList)){
            return getErr('请输入货币信息');
        }

        foreach($excList as $k=>$v){
            if(!empty($v['id'])){
                $excModel = ExchangeModel::get($v['id']);
            }else{
                $excModel = new ExchangeModel();
            }

            $excOne = $v['exc_one'];
            $excTwo = $v['exc_two'];
//            $rate = $excTwo / $excOne;
            $rate = $excOne / $excTwo;

            $excModel->is_remind = $isRemind;
            $excModel->remind_cycle = $remindCycle;
            $excModel->remind_employee_id = $remindId;
            $excModel->exchange_rate = $rate;
            $excModel->currency_unit = $v['currency_unit'];
            $excModel->update_employee = $updateEmployee;

            if(!$excModel->save()){
                return getErr('添加失败,请查看数据是否完整');
            }
        }


        return getSucc('添加成功');

    }

    //修改总汇率
    public function updateExchange()
    {
        return getErr('不能修改');
        $request = $this->request;
        $excId = $request->param('exc_id',0);
        if(empty($excId)){
            return getErr('请传入汇率ID');
        }
        $excModel = ExchangeModel::get($excId);
        $excModel = $this->exchangeData($excModel);

        if($excModel->save()){
            return getSucc('修改成功');
        }

        return getErr('修改失败，请查看数据是否没有变化');

    }

    //获得酒店默认货币
    public function getHotelExchange()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);

        if(empty($hotelId)){
            return getErr('请输入酒店ID');
        }

        $hotelModel = new HotelModel();
        $hotelInfo = array();
        $hotelInfo = $hotelModel
            ->field('exchange_id,currency_unit,exchange_rate')
            ->where('ims_hotel.id',$hotelId)
            ->join('ims_exchange','exchange_id = ims_exchange.id')
            ->find();


        if(!empty($hotelInfo)){
            return getSucc($hotelInfo);
        }

        return getErr('该酒店没有默认货币信息');
    }

    //获得用户列表
    public function getUserList()
    {
        $accModel = new Account();
        $accList = $accModel->field('emp_sn,acct_name')->select();

        if(!empty($accList)){
            return getSucc($this->formateData($accList));
        }

        return getErr('');

    }

    //获得货币列表(带有默认货币)
    public function getHotelExchangeList()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);

        if(empty($hotelId)){
            return getErr('请输入酒店ID');
        }

        $hotelModel = new HotelModel();
        $hotelInfo = array();
        $hotelInfo = $hotelModel
            ->field('exchange_id,currency_unit,exchange_rate')
            ->where('ims_hotel.id',$hotelId)
            ->join('ims_exchange','exchange_id = ims_exchange.id')
            ->find();
        $hotelInfo = $this->formateData($hotelInfo);


        $excModel = new ExchangeModel();
        $excList = array();
        $excList = $excModel->field('id,currency_unit,exchange_rate')->select();
        $excList = $this->formateData($excList);

        $newExcList = array();

        if(!empty($excList)){
            foreach($excList as $k=>$v){
                if($v['currency_unit'] == $hotelInfo['currency_unit']){
                    $newExcList[$k]['is_default'] = 1;
                    $newExcList[$k]['currency_unit'] = $hotelInfo['currency_unit'];
                    $newExcList[$k]['exchange_rate'] = $hotelInfo['exchange_rate'];
                    $newExcList[$k]['id'] = $hotelInfo['exchange_id'];
                }else{
                    $newExcList[$k]['is_default'] = 0;
                    $newExcList[$k]['currency_unit'] = $v['currency_unit'];
                    $newExcList[$k]['exchange_rate'] = $v['exchange_rate'];
                    $newExcList[$k]['id'] = $v['id'];
                }
            }
        }

        if(!empty($newExcList)){
            return getSucc($newExcList);
        }

        return getErr('该酒店没有默认货币信息');
    }

    //修改酒店默认货币
    public function updateHotelExc()
    {
        $request = $this->request;
        $hotelId = $request->param('hotel_id',0);
        $excId = $request->param('exc_id',0);

        if(empty($hotelId) || empty($excId)){
            return getErr('修改失败');
        }

        $hotelModel = new HotelModel();
        $result = $hotelModel->update(['exchange_id'=>$excId],['id'=>$hotelId]);

        if(!empty($result)){
            return getSucc('修改成功');
        }

        return getErr('修改失败');
    }

    /**
     * 获取酒店年龄定义
     * @Author Jepson
     */

    public function getAgeRangeByHotelId()
    {
        $hotelId = $this->post('hotel_id');
            if($hotelModel = HotelModel::get($hotelId)){
                $ageRange = $hotelModel->format_age_range;
                return getSucc($ageRange);
            }
        return getErr('年龄定义获取失败!');
    }

    /**
     * 获取当前目的地酒店id和名字
     */
    public function getAllName()
    {
        $placeId = $this->post('id');
        $data = HotelModel::where('place_id',$placeId)->field('id,hotel_name as value')->select();
        return getSucc($data);
    }

    /**
     * 获取酒店名称
     * @return \think\response\Json
     */
    public function getName()
    {
        $id = $this->post('hotel_id');
        $data = HotelModel::where('id',$id)->field('id,hotel_name as name')->find();
        return getSucc($data);
    }

    public function getAllExchangeData($return = [])
    {
        $data = ExchangeModel::all();
        foreach ($data as $index => $datum) {
            $return[$index]['value'] = $datum['currency_unit'];
            $return[$index]['label'] = $datum['currency_unit'];
        }
        return getSucc($return);
    }


}




























?>